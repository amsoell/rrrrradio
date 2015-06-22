<?php
  include_once("User.class.php");
  date_default_timezone_set('America/Toronto');
  require_once('class.phpmailer.php');

  class Queue {
    function isComingUp($key) {
      $db = new Db();

      $rs = $db->query("SELECT id FROM queue WHERE trackKey='$key' AND endplay>UNIX_TIMESTAMP(NOW())");
      return (mysql_num_rows($rs)>0);
    }

    function getQueue() {
      $db = new Db();

      $sqlx = "SELECT queue.trackKey, queue.userKey, queue.albumKey, queue.artistKey, queue.dedicationName, queue.dedicationMessage, queue.startplay, queue.endplay, queue.muted, GROUP_CONCAT(f.userKey) AS likes, COUNT(q.trackKey) AS requests ";
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
        $t->likes = is_null($rec['likes'])?0:count(array_unique(explode(',',$rec['likes'])));
        $t->requests = $rec['requests'];
        $t->muted = explode(',',$rec['muted']);
        if (!is_null($rec['userKey'])) {
          $t->user = new User($rec['userKey']);
        }
        if (!(is_null($rec['dedicationName']) || ($rec['dedicationName']==""))) {
          $t->dedicationName = $rec['dedicationName'];
          $t->dedicationMessage = $rec['dedicationMessage'];
        }
        $tracks[] = $t;

      }

      return $tracks;
    }

    function freeQueue() {
      $c = new Config();
      return ($this->length()<$c->free_if_queue_less_than);
    }

    function addMuter($userKey) {
      $db = new Db();
      $db->query("UPDATE queue SET muted=CONCAT('$userKey',',',IFNULL(muted, '')) WHERE startPlay<=".time()." AND endPlay>=".time()." AND (muted NOT LIKE '%$userKey%' OR muted IS NULL)");
    }

    function removeMuter($userKey) {
      $db = new Db();
      $rs = $db->query("SELECT id, muted FROM queue WHERE startPlay<=".time()." AND endPlay>=".time()." AND muted LIKE '%$userKey%'");
      if ($rec = mysql_fetch_array($rs)) {
        $muted = $rec['muted'];
        $muted = str_replace($userKey, "", $muted);
        $muted = str_replace(",,",",",$muted);
        $db->query("UPDATE queue SET muted='".addslashes($muted)."' WHERE id=".$rec['id']);
      }
    }

    function push($obj, $requested=false, $requestedBy=null, $client='web', $dedicationName=null, $dedicationRecipient=null, $dedicationMessage=null) {
      $db = new Db();
      $c = new Config();
      $buffer = $c->song_buffer; // seconds between tracks
      $endplay = $this->endOfQueue()+$buffer;

      if (is_object($obj)) {
        $key = $obj->key;
        $track = $obj;
        $duration = $obj->duration;
      } else {
        $key = $obj;
        $track = new Track($key);
      }

      if (is_object($obj) && property_exists($obj, "canStream") && $obj->canStream==0) {
        echo "Not streamable\n";
        return false;
      } else {
        $db->query("INSERT INTO queue (trackKey, albumKey, artistKey, userKey, free, dedicationName, dedicationRecipient, dedicationMessage, client, added, startplay, endplay) VALUES ('$key', '".$obj->album."', '".$obj->artist."', ".(is_null($requestedBy)?"NULL":"'$requestedBy'").", ".($this->length()<$c->free_if_queue_less_than?'1':'0').", '".addslashes($dedicationName)."', '".addslashes($dedicationRecipient)."', '".addslashes($dedicationMessage)."', '".addslashes($client)."', UNIX_TIMESTAMP(NOW()), ".($endplay).", ".($endplay+$obj->duration).")");

        if (trim(strlen($dedicationRecipient))>0) {
          $r = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
          $t = $r->get(array("keys"=> $key, "extras"=>"trackNum,isOnCompilation"));

          $dedicationMessage = "Oh, hello! I thought you would like to know that the following dedication has been made for you at ".$c->sitename.":\r\n\r\n".
                               "Artist: " . $t->result->$key->artist . "\r\n" .
                               "Song: " . $t->result->$key->name . "\r\n" .
                               "Dedicated to: " . $dedicationName . "\r\n" .
                               "Dedicated by: " . $_SESSION['user']->firstName." ".$_SESSION['user']->lastName . "\r\n\r\n".
                               "Message:\r\n".$dedicationMessage."\r\n\r\n".
                               "To listen to this dedication, log on to ".$c->app_domain;

          $this->sendNotification($dedicationRecipient, $dedicationName, $dedicationMessage);
        }
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

    function getPlaylists() {
      $db = new Db();
      $c = new Config();

      $playlists = Array();
      $rs = $db->query("SELECT id, trackKey, userKey, startplay, endplay FROM queue WHERE userKey IS NOT NULL ORDER BY startplay");

      $prev = 0;
      $currentPlaylist = 0;
      while ($rec = mysql_fetch_array($rs)) {
        $t = new QueueTrack($rec['trackKey']);
        $t->startplay = $rec['startplay'];
        $t->endplay = $rec['endplay'];
        $t->user = new User($rec['userKey']);
        if ($rec['id']!=($prev+1)) {
          // new set of tracks
          // if current set is less than 10 tracks, delete it
          if (count($playlists[$currentPlaylist])<10) {
            unset($playlists[$currentPlaylist]);
          } else {
            $currentPlaylist++;
          }
        }

        $playlists[$currentPlaylist][] = $t;
        $prev = $rec['id'];
      }

      return $playlists;
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
      $sqlx .= "startPlay>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$c->random_rotation." HOUR)) OR (";
      // Nothing longer than y number of seconds...
      $sqlx .= "endPlay-startPlay>".$c->random_max_length;
      if ($c->random_requests_required>0) {
          // ...unless it's been requested a certain number of times
          $sqlx .= " AND trackKey NOT IN (SELECT trackKey FROM (SELECT COUNT(trackKey) as theCount, trackKey FROM queue WHERE userKey IS NOT NULL GROUP BY trackKey) r WHERE theCount>=".$c->random_requests_required."))";
      } else {
          $sqlx .= ")";
      }

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

    function sendNotification($address, $name, $message) {
      $c = new Config();

      $recipients = explode(",", $address);

      foreach ($recipients as $recipient) {
        $recipient = trim($recipient);

        if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $recipient)) {

          $mail = new PHPMailer();
          $mail->IsSMTP();
  //        $mail->SMTPDebug = 2;
          $mail->SMTPAuth = true;
          $mail->SMTPSecure = "ssl";
          $mail->Host = $c->smtp_host;
          $mail->Port = $c->smtp_port;
          $mail->Username = $c->smtp_username;
          $mail->Password = $c->smtp_password;
          $mail->SetFrom("noreply@rrrrradio.com", "rrrrradio");
          $mail->Subject = $c->sitename." Dedication";
          $mail->AltBody = $message;
          $mail->MsgHTML(nl2br($message));
          $mail->AddAddress($recipient, $name);

          $mail->Send();
        }
      }
    }



  }

  class QueueError {
    var $errorMessage;

    function __construct($msg) {
      $this->errorMessage = $msg;
    }
  }
?>