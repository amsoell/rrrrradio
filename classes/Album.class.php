<?php
  class Album {
    public $key;
    public $name;
    public $artist;
    
    function __construct($key) {
      return $this->load($key);
    }
    
    function load($key) {
      $db = new Db();
      
      $rs = $db->query("SELECT `name`, artistKey FROM album WHERE `key`='$key'");  
      if ($rec = mysql_fetch_array($rs)) {
        $this->key = $key;
        $this->name = $rec['name'];
        $this->artist = new Artist($rec['artistKey']);

        return true;
      } else {
        return false;
      }
    }
  }
?>