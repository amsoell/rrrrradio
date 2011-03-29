<?php
  class Track {
    public $key;
    public $name;
    public $album;    
    public $artist;
    public $icon;    
    public $duration;
    public $trackNum;
    public $canStream;
    
    function __construct($key=null) {
      $this->canStream = 1;
      if (!is_null($key)) {
        return $this->load($key);
      } else {
        return true;
      }
    }
    
    function load($key) {
      $ths->key = $key;
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