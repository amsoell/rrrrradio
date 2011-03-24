<?php
  class Queue {
    function isComingUp($key) {
      $db = new Db();
      
      $rs = $db->query("SELECT id FROM queue WHERE trackKey='$key' AND endplay>UNIX_TIMESTAMP(NOW())");
      return (mysql_num_rows($rs)>0);
    }
    
    function getQueue() {
      $db = new Db();
      
      $rs = $db->query("SELECT trackKey, startplay, endplay FROM queue WHERE endplay>=UNIX_TIMESTAMP(NOW()) ORDER BY startplay");    
      $tracks = Array();
      while ($rec = mysql_fetch_array($rs)) {
        $t = new QueueTrack($rec['trackKey']);
        $t->startplay = $rec['startplay'];
        $t->endplay = $rec['endplay'];
        $tracks[] = $t;
      }
      
      return $tracks;
    }
    
    function push($obj, $requested=false) {
      $db = new Db();
      $endplay = $this->endOfQueue();
      
      if ($obj instanceof Track) {
        $key = $obj->key;
      } else {
        $key = $obj;
      }
      $track = new Track($key);

      if (strlen($track->key)>0) {
        $db->query("INSERT INTO queue (trackKey, added, startplay, endplay) VALUES ('$key', UNIX_TIMESTAMP(NOW()), ".($endplay+1).", ".($endplay+$track->duration+1).")");
      }
      
      if ($requested) $db->query("UPDATE track SET requested=1 WHERE `key`='$key' AND requested=0");
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