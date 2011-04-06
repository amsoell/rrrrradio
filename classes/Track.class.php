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
      if (!is_null($key)) {
        return $this->load($key);
      } else {
        return true;
      }
    }
    
    function load($key) {
      $r = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
      $t = $r->get(array("keys"=> $key, "extras"=>"trackNum"));

      $this->key = $key;
      $this->name = $t->result->$key->name;
      $this->album = $t->result->$key->albumKey;
      $this->artist = $t->result->$key->artistKey;
      $this->icon = $t->result->$key->icon;
      $this->duration = $t->result->$key->duration;
      $this->trackNum = $t->result->$key->trackNum;
      $this->canStream = $t->result->$key->canStream;
      
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