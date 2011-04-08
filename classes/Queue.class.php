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

      $rs = $db->query("SELECT trackKey, userKey, startplay, endplay FROM queue WHERE endplay>=UNIX_TIMESTAMP(NOW()) ORDER BY startplay");    
      $tracks = Array();

      while ($rec = mysql_fetch_array($rs)) {
        $t = new QueueTrack();

        $t->key = $rec['trackKey'];
        $t->startplay = $rec['startplay'];
        $t->endplay = $rec['endplay'];
        $t->duration = $rec['endplay']-$rec['startplay'];
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
    
    function isRequestable($track) {
      $db = new Db();
      $c = new Config();
      
      // CANNOT GET USER INFO. NO USER INFO, NO REQUEST
      if (strlen($_SESSION['user']->key)<=0) return false; 
      
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
      
      // IF USER IS THE ONLY LISTENER, LET THEM REQUEST
      if (count(User::getCurrentListeners())<=1) return true;
      
      // QUEUE IS GREATER THAN LIMIT & USER IS OUT OF REQUESTS
      $rs = $db->query("SELECT COUNT(userKey) AS fromUser FROM queue WHERE userKey='".$_SESSION['user']->key."' AND free=0 AND added>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 HOUR))");
      if (($rec = mysql_fetch_array($rs)) && ($rec['fromUser']>=$c->requests_per_hour)) return new QueueError('You are out of requests');
      
      
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