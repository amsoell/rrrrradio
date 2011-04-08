<?php
  class User {
    public $key;
    public $username;
    public $firstName;
    public $lastName;
    public $icon;
    public $gender;
    private $state;
    private $token;
    private $secret;
    
    function __construct($key) {
      $db = new Db();
      $rdio = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
      
      $rs = $db->query("SELECT `key`, state, token, secret FROM user WHERE `key`='$key' LIMIT 1");
      if ($rec = mysql_fetch_array($rs)) {
        $this->key = $rec['key'];
        $this->state = $rec['state'];
        $this->token = $rec['token'];
        $this->secret = $rec['secret'];
        
        $key = $rec['key'];
        if (class_exists("OAuth")) {
          $user = $rdio->get(array("keys"=>$key, "extras"=>"username"));
          $this->username = $user->result->$key->username;
          $this->firstName = $user->result->$key->firstName;        
          $this->lastName = $user->result->$key->lastName;        
          $this->icon = $user->result->$key->icon;        
          $this->gender = $user->result->$key->gender;        
        }
      }
    }
    
    function getCurrentListeners($minutes = 10) {
      $db = new Db();
      
      $l = array();
      $rs = $db->query("SELECT `key` FROM user WHERE lastseen>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$minutes." MINUTE)) ORDER BY lastseen DESC");      
      while ($rec = mysql_fetch_array($rs)) {
        $l[] = new User($rec['key']);
      }
      
      return $l;
    }
    
    function ping() {
      $db = new Db();

      $db->query("UPDATE user SET lastseen=UNIX_TIMESTAMP(NOW()) WHERE `key`='".$this->key."' LIMIT 1");
    }
    
    function getTopArtists($count=10) {
      $db = new Db();
      $rdio = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
      
      $rs = $db->query("SELECT artistKey, count(id) AS requestCount FROM queue WHERE userKey='".$this->key."' GROUP BY artistKey ORDER BY requestCount DESC LIMIT $count");
      $a = array();
      while ($rec = mysql_fetch_array($rs)) {
        $key = $rec['artistKey'];
        $tmp = $rdio->get(array("keys"=>$rec['artistKey']));
        $a[] = $tmp->result->$key;
      }
      
      return $a;
    }
    
    function getTopTracks($count=10) {
      $db = new Db();
      $rdio = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
      
      $rs = $db->query("SELECT trackKey, count(id) AS requestCount FROM queue WHERE userKey='".$this->key."' GROUP BY trackKey ORDER BY requestCount DESC LIMIT $count");
      $a = array();
      while ($rec = mysql_fetch_array($rs)) {
        $key = $rec['trackKey'];
        $tmp = $rdio->get(array("keys"=>$rec['trackKey']));
        $a[] = $tmp->result->$key;
      }
      
      return $a;
    }    
  
  }
?>