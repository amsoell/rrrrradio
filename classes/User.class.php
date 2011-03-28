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
      
      $rs = $db->query("SELECT `key`, username, firstName, lastName, icon, gender, state, token, secret FROM user WHERE `key`='$key' LIMIT 1");
      if ($rec = mysql_fetch_array($rs)) {
        $this->key = $rec['key'];
        $this->username = $rec['username'];
        $this->firstName = $rec['firstName'];
        $this->lastName = $rec['lastName'];
        $this->icon = $rec['icon'];
        $this->gender = $rec['gender'];
        $this->state = $rec['state'];
        $this->token = $rec['token'];
        $this->secret = $rec['secret'];
      }
    }
  
  }
?>