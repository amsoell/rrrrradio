<?php
  class Artist {
    public $key;
    public $name;
    
    function __construct($key) {
      return $this->load($key);
    }
    
    function load($key) {
      $db = new Db();
      
      $rs = $db->query("SELECT `name` FROM artist WHERE `key`='$key'");  
      if ($rec = mysql_fetch_array($rs)) {
        $this->key = $key;
        $this->name = $rec['name'];

        return true;
      } else {
        return false;
      }
    }
  }
?>