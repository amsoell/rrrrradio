<?php
  class Track {
    public $key;
    public $name;
    public $album;    
    public $duration;
    public $trackNum;
    public $canStream;
    
    function __construct($key) {
      $this->canStream = false;
      return $this->load($key);
    }
    
    function load($key) {
      $db = new Db();
      
      $rs = $db->query("SELECT `name`, albumKey, duration, trackNum, canStream FROM track WHERE `key`='$key'");  
      if ($rec = mysql_fetch_array($rs)) {
        $this->key = $key;
        $this->name = $rec['name'];
        $this->duration = $rec['duration'];
        $this->trackNum = $rec['trackNum'];
        if ($rec['canStream']==1) $this->canStream = true;

        return true;
      } else {
        return false;
      }
    }
    
    function exists($key) { 
      $db = new Db();
      
      $key = trim($key);
      
      $rs = $db->query("SELECT `key` FROM track WHERE `key`='$key'");
      return (mysql_num_rows($rs)>0);
    }
  }
  
  class QueueTrack extends Track {
    public $startplay;
    public $endplay;
    public $user;
  }
?>