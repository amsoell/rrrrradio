<?php
  class User {
    public $key;
    public $username;
    public $firstName;
    public $lastName;
    public $icon;
    public $gender;
    public $lastclient;
    private $state;
    private $token;
    private $secret;

    public $isCurator;

    function __construct($key) {
      $db = new Db();
      $rdio = new RdioLib(RDIO_CLIENT_ID, RDIO_CLIENT_SECRET, RDIO_CLIENT_REDIRECT_URI);

      $rs = $db->query("SELECT `key`, state, token, secret, lastclient FROM user WHERE `key`='$key' LIMIT 1");
      if ($rec = mysql_fetch_array($rs)) {
        $this->key = $rec['key'];
        $this->state = $rec['state'];
        $this->token = $rec['token'];
        $this->secret = $rec['secret'];
        $this->lastclient = $rec['lastclient'];

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

    function getCurrentListeners($minutes = 4) {
      $db = new Db();

      $l = array();
      $rs = $db->query("SELECT `key` FROM user WHERE lastseen>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$minutes." MINUTE)) ORDER BY `key` DESC");
      while ($rec = mysql_fetch_array($rs)) {
        $l[] = new User($rec['key']);
      }

      return $l;
    }

    function getFavoriteTracks() {
      $db = new Db();

      $t = array();
      $rs = $db->query("SELECT GROUP_CONCAT(trackKey) AS favoriteTracks FROM mark WHERE trackKey LIKE 't%' AND userKey='".$this->key."' AND mark=1");

      if ($rec = mysql_fetch_array($rs)) {
        $t = explode(",", $rec['favoriteTracks']);
      }

      return $t;
    }

    function getRecentRequests($num=15) {
      $db = new Db();

      $rs = $db->query("SELECT trackKey FROM queue WHERE userKey='".$this->key."' ORDER BY startPlay DESC LIMIT ".$num);
      $t = Array();
      while ($rec = mysql_fetch_array($rs)) {
        $t[] = $rec['trackKey'];
      }

      return $t;
    }

    function getBlockedTracks() {
      $db = new Db();

      $t = array();
      $rs = $db->query("SELECT GROUP_CONCAT(trackKey) AS blockedTracks FROM mark WHERE userKey='".$this->key."' AND mark=-1");

      if ($rec = mysql_fetch_array($rs)) {
        $t = explode(",", $rec['blockedTracks']);
      }

      return $t;
    }

    function ping() {
      $db = new Db();

      $db->query("UPDATE user SET lastseen=UNIX_TIMESTAMP(NOW()) WHERE `key`='".$this->key."' LIMIT 1");
    }

    function getTopArtists($count=10) {
      $db = new Db();
      $rdio = new RdioLib(RDIO_CLIENT_ID, RDIO_CLIENT_SECRET, RDIO_CLIENT_REDIRECT_URI);

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
      $rdio = new RdioLib(RDIO_CLIENT_ID, RDIO_CLIENT_SECRET, RDIO_CLIENT_REDIRECT_URI);

      $rs = $db->query("SELECT trackKey, count(id) AS requestCount FROM queue WHERE userKey='".$this->key."' GROUP BY trackKey ORDER BY requestCount DESC LIMIT $count");
      $a = array();
      while ($rec = mysql_fetch_array($rs)) {
        $key = $rec['trackKey'];
        $tmp = $rdio->get(array("keys"=>$rec['trackKey']));
        $a[] = $tmp->result->$key;
      }

      return $a;
    }

    function requestsLeft() {
      $db = new Db();
      $c = new Config();

      $rs = $db->query("SELECT COUNT(userKey) AS requests, DATE_ADD(FROM_UNIXTIME(MIN(added)), INTERVAL 1 HOUR) AS newRequests FROM queue WHERE userKey='".$this->key."' AND free=0 AND added>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 HOUR))");
      if ($rec = mysql_fetch_array($rs)) {
        return ($c->requests_per_hour - $rec['requests']);
      } else {
        return $c->requests_per_hour;
      }
    }

    function requestsRenew() {
      $db = new Db();
      $c = new Config();

      $rs = $db->query("SELECT COUNT(userKey) AS requests, DATE_ADD(FROM_UNIXTIME(MIN(added)), INTERVAL 1 HOUR) AS newRequests FROM queue WHERE userKey='".$this->key."' AND free=0 AND added>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 HOUR))");
      if ($rec = mysql_fetch_array($rs)) {
        return strtotime($rec['newRequests']);
      } else {
        return time();
      }
    }

  }
?>
