<?php
  class Collection {
    function getArtists() {
      $db = new Db();
      
      $db->query("SELECT `key`, `name` FROM artist ORDER BY `name`");    
      $artists = Array();
      
      while ($rec = $db->fetch_array()) {
        $artists[] = new Artist($rec['key']);
      }
      
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
      
      $rs = $db->query("SELECT `key` FROM track WHERE rnd>RAND() AND ".$requestedBit."`key` NOT IN ('".implode(",''", $queuetracks)."') AND IFNULL(lastqueue, 0)<=UNIX_TIMESTAMP(NOW())-".$lastplaythreshold." ORDER BY rnd LIMIT 1");
      if ($rec = mysql_fetch_array($rs)) {
        $t = new Track($rec['key']);
        return $t;
      } else {
        return false;
      }
    }
    
    function addTrack($track) {
      $db = new Db();
      
      if (!$this->trackExists($track->key)) {
        $db->query("REPLACE INTO track (`key`, albumKey, artistKey, `name`, trackNum, shortUrl, duration, isExplicit, isClean, canStream, requested, rnd) VALUES ('".$track->key."', '".$track->albumKey."', '".$track->artistKey."', '".addslashes($track->name)."', ".$track->trackNum.", '".$track->shortUrl."', ".$track->duration.", ".intval($track->isExplicit).", ".intval($track->isClean).", ".intval($track->canStream).", 1, rand())");
        print "<br />";
        $albumKey = $track->albumKey;
        $album = rdioGet(array("method"=>"get", "keys"=>$albumKey));
        
        $this->addAlbum($album->result->$albumKey);
      }
    }
    
    function addAlbum($album) {
      $db = new Db();
      
      if (!$this->albumExists($album->key)) {
        $db->query("REPLACE INTO album (`key`, artistKey, `name`, icon, url, isExplicit, isClean, canStream, shortUrl, embedUrl, duration) VALUES ('".$album->key."', '".$album->artistKey."', '".addslashes($album->name)."', '".$album->icon."', '".$album->url."', ".intval($album->isExplicit).", ".intval($album->isClean).", ".intval($album->canStream).", '".$album->shortUrl."', '".$album->embedUrl."', ".$album->duration.")");
        print "<br />";
        $artistKey = $album->artistKey;
        $artist = rdioGet(array("method"=>"get", "keys"=>$artistKey));
        
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