<?php
  class Track {
    public $key;
    public $name;
    public $album;    
    public $artist;
    public $albumKey;
    public $artistKey;
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
    
    function mark($val) {
      $db = new Db();
      
      if (isset($_SESSION['user']) && property_exists($_SESSION['user'], 'key')) {
        if ($val==0) {
          $db->query("DELETE FROM mark  WHERE userKey='".$_SESSION['user']->key."' AND trackKey='".$this->key."' LIMIT 1");
        } else {
          $db->query("REPLACE INTO mark (userKey, trackKey, mark) VALUES ('".$_SESSION['user']->key."', '".$this->key."', $val)");
        }
      }
    }
  }
  
  class QueueTrack extends Track {
    public $startplay;
    public $endplay;
    public $user;
    public $mark;
  }
?>