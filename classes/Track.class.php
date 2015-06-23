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
      $r = new RdioLib(RDIO_CLIENT_ID, RDIO_CLIENT_SECRET, RDIO_CLIENT_REDIRECT_URI);
      $t = $r->get(array("keys"=> $key, "extras"=>"trackNum,isOnCompilation"));

      $this->key = $key;
      $this->name = $t->result->$key->name;
      $this->album = $t->result->$key->albumKey;
      $this->artist = ($t->result->$key->isOnCompilation?'r62':$t->result->$key->artistKey);
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

    function mark($val, $userKey=null) {
      $db = new Db();

      if (is_null($userKey) && property_exists($_SESSION['user'], 'key')) {
        $userKey = $_SESSION['user']->key;
      }

      if (!is_null($userKey)) {
        if ($val==0) {
          $db->query("DELETE FROM mark  WHERE userKey='".$userKey."' AND trackKey='".$this->key."' LIMIT 1");
        } else {
          $db->query("REPLACE INTO mark (userKey, trackKey, mark) VALUES ('".$userKey."', '".$this->key."', $val)");
        }
      }
    }
  }

  class QueueTrack extends Track {
    public $startplay;
    public $endplay;
    public $muted;
    public $user;
    public $mark;
  }
?>
