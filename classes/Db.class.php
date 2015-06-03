<?php
  class Db {
    var $username;
    var $password;
    var $database;
    var $dblink;
    var $rslink;
    
    function __construct() {
      $c = new Config();
      $this->host     = $c->db_host;
      $this->username = $c->db_username;
      $this->password = $c->db_password;
      $this->database = $c->db_database;
      
      $this->dblink = mysql_connect($this->host, $this->username, $this->password);
      mysql_select_db($this->database, $this->dblink);
      
    }
    
    function query($sql) {
      $this->rslink = mysql_query($sql);
      return $this->rslink;
    }
    
    function fetch_array() {
      return mysql_fetch_array($this->rslink);
    }
    
    function num_rows() {
      return mysql_num_rows($this->rslink);
    }
  }
?>
