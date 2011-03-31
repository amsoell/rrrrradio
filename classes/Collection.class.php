<?php
  class Collection {
    function getArtists() {
      $c = new Config();
      $rdio = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
      
      $artists = $rdio->getArtistsInCollection(array("user"=>$c->rdio_collection_userkey));    
      $artists = $artists->result;

      return $artists;
    }

    function getRandomTrack($includeQueued=false, $includeAll=false, $lastplaythreshold=10800) {
      $db = new Db();
      $q = new Queue();

      if (!$includeQueued) {
        // get currently queued tracks to exclude from selection
        $queuetracks = array();
        foreach ($q->getQueue() as $track) {
          $queuetracks[] = $track->key;
        }
      } else {
        $queuetracks = array("");
      }
      
      if (!$includeAll) {
        $requestedBit = "requested=1 AND ";
      }
      
      $rs = $db->query("SELECT FLOOR(RAND()*COUNT(DISTINCT trackKey)) AS offset FROM queue");
      if ($rec = mysql_fetch_array($rs)) {
        $offset = $rec['offset'];

        $rs = $db->query("SELECT DISTINCT trackKey, endplay-startplay AS duration FROM queue LIMIT $offset, 1");
        if ($rec = mysql_fetch_array($rs)) {
          $t = new Track();
          $t->key = $rec['trackKey'];
          $t->duration = $rec['duration'];
          return $t;
        } else {
          return false;
        }
      } else {
        return false;
      }
    }
    
    function addTrack($track) {
      $db = new Db();
      $rdio = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
      
      if (!$this->trackExists($track->key)) {
        $db->query("REPLACE INTO track (`key`, albumKey, artistKey, `name`, trackNum, shortUrl, duration, isExplicit, isClean, canStream, requested, rnd) VALUES ('".$track->key."', '".$track->albumKey."', '".$track->artistKey."', '".addslashes($track->name)."', ".$track->trackNum.", '".$track->shortUrl."', ".$track->duration.", ".intval($track->isExplicit).", ".intval($track->isClean).", ".intval($track->canStream).", 1, rand())");
        $albumKey = $track->albumKey;
        $album = $rdio->get(array("keys"=>$albumKey));
        print $albumKey;
        $this->addAlbum($album->result->$albumKey);
      }
    }
    
    function addAlbum($album) {
      $db = new Db();
      $rdio = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
      
      if (!$this->albumExists($album->key)) {
        $db->query("REPLACE INTO album (`key`, artistKey, `name`, icon, url, isExplicit, isClean, canStream, shortUrl, embedUrl, duration) VALUES ('".$album->key."', '".$album->artistKey."', '".addslashes($album->name)."', '".$album->icon."', '".$album->url."', ".intval($album->isExplicit).", ".intval($album->isClean).", ".intval($album->canStream).", '".$album->shortUrl."', '".$album->embedUrl."', ".$album->duration.")");
        print "<br />";
        $artistKey = $album->artistKey;
        $artist = $rdio->get(array("keys"=>$artistKey));
        
        $this->addArtist($artist->result->$artistKey);
      }
      
    }
    
    function addArtist($artist) {
      $db = new Db();
      
      if (!$this->artistExists($artist->key)) {
        $db->query("REPLACE INTO artist (`key`, `name`, url) VALUES ('".$artist->key."', '".addslashes($artist->name)."', '".addslashes($artist->url)."')");
        print "<br />";
      }
      
    }
    
    function trackExists($key) {
      $db = new Db();
      
      $key = trim($key);
      
      $rs = $db->query("SELECT `key` FROM track WHERE `key`='$key'");
      return (mysql_num_rows($rs)>0);    
    }
    
    function albumExists($key) {
      $db = new Db();
      
      $key = trim($key);
      
      $rs = $db->query("SELECT `key` FROM album WHERE `key`='$key'");
      return (mysql_num_rows($rs)>0);
    }
    
    function artistExists($key) {
      $db = new Db();
      
      $key = trim($key);
      
      $rs = $db->query("SELECT `key` FROM artist WHERE `key`='$key'");
      return (mysql_num_rows($rs)>0);
    }    
  }
?>