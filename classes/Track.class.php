<?php
  class Track {
    public $key;
    public $name;
    public $duration;
    
    function __construct($key) {
      return $this->load($key);
    }
    
    function load($key) {
      $db = new Db();
      
      $rs = $db->query("SELECT `name`, duration FROM track WHERE `key`='$key'");  
      if ($rec = mysql_fetch_array($rs)) {
        $this->key = $key;
        $this->name = $rec['name'];
        $this->duration = $rec['duration'];

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