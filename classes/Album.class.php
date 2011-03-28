<?php
  class Album {
    public $key;
    public $name;
    public $icon;
    public $artist;
    
    function __construct($key) {
      return $this->load($key);
    }
    
    function load($key) {
      $db = new Db();
      
      $rs = $db->query("SELECT `name`, icon, artistKey FROM album WHERE `key`='$key'");  
      if ($rec = mysql_fetch_array($rs)) {
        $this->key = $key;
        $this->name = $rec['name'];
        $this->icon = $rec['icon'];
        $this->artist = new Artist($rec['artistKey']);

        return true;
      } else {
        return false;
      }
    }
  }
?>