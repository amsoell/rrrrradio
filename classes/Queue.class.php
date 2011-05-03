<?php
  include_once("User.class.php");

  class Queue {
    function isComingUp($key) {
      $db = new Db();
      
      $rs = $db->query("SELECT id FROM queue WHERE trackKey='$key' AND endplay>UNIX_TIMESTAMP(NOW())");
      return (mysql_num_rows($rs)>0);
    }
    
    function getQueue() {
      $db = new Db();

      $sqlx = "SELECT queue.trackKey, queue.userKey, queue.albumKey, queue.artistKey, queue.startplay, queue.endplay, GROUP_CONCAT(f.userKey) AS likes, COUNT(q.trackKey) AS requests ";
      if (isset($_SESSION['user']) && property_exists($_SESSION['user'], "key")) {
        $sqlx .= ", m.mark FROM queue LEFT JOIN mark AS m ON queue.trackKey=m.trackKey AND m.userKey='".$_SESSION['user']->key."' ";
      } else {
        $sqlx .= "FROM queue ";
      }
      $sqlx .= "LEFT JOIN (SELECT * FROM mark WHERE mark=1) AS f ON queue.trackKey=f.trackKey LEFT JOIN (SELECT * FROM queue WHERE userKey IS NOT NULL) AS q ON queue.trackKey=q.trackKey WHERE queue.endplay>=UNIX_TIMESTAMP(NOW()) GROUP BY queue.trackKey ORDER BY startplay";

      $rs = $db->query($sqlx);
      $tracks = Array();

      while ($rec = mysql_fetch_array($rs)) {
        $t = new QueueTrack();

        $t->key = $rec['trackKey'];
        $t->albumKey = $rec['albumKey'];
        $t->artistKey = $rec['artistKey'];
        $t->startplay = $rec['startplay'];
        $t->endplay = $rec['endplay'];
        $t->duration = $rec['endplay']-$rec['startplay'];
        $t->mark = $rec['mark'];
        $t->likes = is_null($rec['likes'])?0:count(explode(',',$rec['likes']));
        $t->requests = $rec['requests'];
        if (!is_null($rec['userKey'])) {
          $t->user = new User($rec['userKey']);
        }
        $tracks[] = $t;

      }
      
      return $tracks;
    }
    
    function freeQueue() {
      $c = new Config();
      return ($this->length()<$c->free_if_queue_less_than);
    }
    
    function push($obj, $requested=false, $requestedBy=null) {
      $db = new Db();
      $c = new Config();
      $buffer = $c->song_buffer; // seconds between tracks
      $endplay = $this->endOfQueue()+$buffer;
      
      if (is_object($obj)) {
        $key = $obj->key;
        $duration = $obj->duration;
      } else {
        $key = $obj;
      }

      if (is_object($obj) && property_exists($obj, "canStream") && $obj->canStream==0) {
        return false;
      } else {
        $db->query("INSERT INTO queue (trackKey, albumKey, artistKey, userKey, free, added, startplay, endplay) VALUES ('$key', '".$obj->album."', '".$obj->artist."', ".(is_null($requestedBy)?"NULL":"'$requestedBy'").", ".($this->length()<$c->free_if_queue_less_than?'1':'0').", UNIX_TIMESTAMP(NOW()), ".($endplay).", ".($endplay+$obj->duration).")");
      }
    }
    
    function endOfQueue() {
      $db = new Db();
      
      $rs = $db->query("SELECT MAX(endplay) AS endplay FROM queue");
      if ($rec = mysql_fetch_array($rs)) {
        $endplay = $rec['endplay'];
        if (is_null($endplay) || ($endplay<time())) $endplay = time();
        return $endplay;
      } else {
        return time();
      }
    }
    
    function length() {
      $db = new Db();
      
      $rs = $db->query("SELECT COUNT(id) AS length FROM queue WHERE endplay>UNIX_TIMESTAMP(NOW())");
      if ($rec = mysql_fetch_array($rs)) {
        return $rec['length'];
      } else {
        return 0;
      }
    }
    
    function isRandomable($track) {
      // RESTRICTIONS ON RANDOMLY QUEUED TRACKS
      $db = new Db();
      $c = new Config();
      
      //IF A USER CAN'T REQUEST IT, IT CANT COE UP RANDOMLY EITHER
      if (!$this->isRequestable($track, false)) return false;
      
      $db->query("SET SESSION GROUP_CONCAT_MAX_LEN = 30000");      
      $sqlx  = "SELECT GROUP_CONCAT(DISTINCT trackKey) AS trackKeys FROM queue WHERE ";
      // Nothing that's played in the past x number of hours
      $sqlx .= "startPlay>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$c->random_rotation." HOUR)) OR ";
      // Nothing longer than y number of seconds
      $sqlx .= "endPlay-startPlay>".$c->random_max_length;
      
      $rs = $db->query($sqlx);
      if ($rec = mysql_fetch_array($rs)) {
        $tracks = explode(',', $rec['trackKeys']);
        if (in_array($track->key, $tracks)) return false;
      }
      
      return true;
    }
    
    function isRequestable($track, $requireAuthentication=true) {
      // RESTRICTIONS ON USER QUEUED TRACKS
      
      $db = new Db();
      $c = new Config();
      
      // CANNOT GET USER INFO. NO USER INFO, NO REQUEST
      if ($requireAuthentication && (strlen($_SESSION['user']->key)<=0)) return false; 
      
      // TRACK IS ALREADY IN QUEUE
      if ($this->isComingUp($track->key)) return false;
            
      // ONLY TWO REQUESTS FROM A SPECIFIC ALBUM WITHIN AN HOUR
      $rs = $db->query("SELECT COUNT(albumKey) AS fromAlbum FROM queue WHERE albumKey='".$track->album."' AND added>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 HOUR))");
      if (($rec = mysql_fetch_array($rs)) && ($rec['fromAlbum']>=$c->requests_per_album_per_hour)) return new QueueError('This album has already been played from '.$c->requests_per_album_per_hour.' times in the last hour');

      // ONLY THREE REQUESTS FROM A SPECIFIC ARTIST WITHIN TWO HOURS      
      $rs = $db->query("SELECT COUNT(artistKey) AS fromArtist FROM queue WHERE artistKey='".$track->artist."' AND added>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 HOUR))");
      if (($rec = mysql_fetch_array($rs)) && ($rec['fromArtist']>=$c->requests_per_artist_per_hour)) return new QueueError('This artist has already been played '.$c->requests_per_artist_per_hour.' times in the last hour');;

      // IF QUEUE LENGTH IS SHORT ENOUGH, EVALUATE TO TRUE AT THIS POINT
      if ($this->freeQueue()) return true;
      
      if ($requireAuthentication) {
        // IF USER IS THE ONLY LISTENER, LET THEM REQUEST
        if (count(User::getCurrentListeners())<=1) return true;
        
        // QUEUE IS GREATER THAN LIMIT & USER IS OUT OF REQUESTS
        $rs = $db->query("SELECT COUNT(userKey) AS fromUser FROM queue WHERE userKey='".$_SESSION['user']->key."' AND free=0 AND added>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 HOUR))");
        if (($rec = mysql_fetch_array($rs)) && ($rec['fromUser']>=$c->requests_per_hour)) return new QueueError('You are out of requests');
      }
      
      
      return true;
    }
  }
  
  class QueueError {
    var $errorMessage;
    
    function __construct($msg) {
      $this->errorMessage = $msg;
    }
  }
?>