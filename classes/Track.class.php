<?php
  class Track {
    public $key;
    public $name;
    public $album;    
    public $duration;
    public $canStream;
    
    function __construct($key) {
      $this->canStream = false;
      return $this->load($key);
    }
    
    function load($key) {
      $db = new Db();
      
      $rs = $db->query("SELECT `name`, albumKey, duration, canStream FROM track WHERE `key`='$key'");  
      if ($rec = mysql_fetch_array($rs)) {
        $this->key = $key;
        $this->name = $rec['name'];
        $this->duration = $rec['duration'];
        $this->album = new Album($rec['albumKey']);
        if ($rec['canStream']==1) $this->canStream = true;

        return true;
      } else {
        return false;
      }
    }
  }
  
  class QueueTrack extends Track {
    public $startplay;
    public $endplay;
  }
?>