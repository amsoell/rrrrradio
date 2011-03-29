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
      
      $rs = $db->query("SELECT `key`, state, token, secret FROM user WHERE `key`='$key' LIMIT 1");
      if ($rec = mysql_fetch_array($rs)) {
        $this->key = $rec['key'];
        $this->state = $rec['state'];
        $this->token = $rec['token'];
        $this->secret = $rec['secret'];
        
        $key = $rec['key'];
        $user = rdioGet(array("method"=>"get", "keys"=>$key, "extras"=>"username"));
        $this->username = $user->result->$key->username;
        $this->firstName = $user->result->$key->firstName;        
        $this->lastName = $user->result->$key->lastName;        
        $this->icon = $user->result->$key->icon;        
        $this->gender = $user->result->$key->gender;        
      }
    }
  
  }
?>