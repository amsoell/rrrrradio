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
    
    function push($obj, $requested=false, $requestedBy=null) {
      $db = new Db();
      $endplay = $this->endOfQueue();
      
      if (is_object($obj)) {
        $key = $obj->key;
        $duration = $obj->duration;
      } else {
        $key = $obj;
      }

      if (is_object($obj) && property_exists($obj, "canStream") && $obj->canStream==0) {
        return false;
      } else {
        $db->query("INSERT INTO queue (trackKey, userKey, added, startplay, endplay) VALUES ('$key', ".(is_null($requestedBy)?"NULL":"'$requestedBy'").", UNIX_TIMESTAMP(NOW()), ".($endplay).", ".($endplay+$obj->duration).")");
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
  }
?>