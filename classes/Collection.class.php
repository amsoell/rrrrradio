<?php
  class Collection {
    function getArtists() {
      $c = new Config();
      $rdio = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
      
      $artists = $rdio->getArtistsInCollection(array("user"=>$c->rdio_collection_userkey));    
      $artists = $artists->result;

      return $artists;
    }
    
    function getRandomables() {
      $db = new Db();
      
      $db->query("SET SESSION GROUP_CONCAT_MAX_LEN = 30000");
      $rs = $db->query("SELECT GROUP_CONCAT(DISTINCT trackKey) AS randomables FROM queue");
      
      if ($rec = mysql_fetch_array($rs)) {
        $tracks = explode(',',$rec['randomables']);
      } else {
        $tracks = array();
      }
      
      return $tracks;
    }

    function getRandomTrack($includeQueued=false, $excludeBlocked=array()) {
      $db = new Db();
      $q = new Queue();
      
      $randomables = Collection::getRandomables();

      if (!$includeQueued) {
        // get currently queued tracks to exclude from selection
        foreach ($q->getQueue() as $track) unset($randomables[array_search($track->key, $randomables)]);
      }
      
      if (is_array($excludeBlocked)) {
        // remove tracks blocked by selected users
        foreach ($excludeBlocked as $user) {
          if ($user instanceof User) {
            $u = $user;
          } else {
            $u = new User($userKey);
          }
          
          foreach ($u->getBlockedTracks() as $trackKey) unset($randomables[array_search($trackKey, $randomables)]);
        }
      }
      
      $t = new Track($randomables[rand(0, count($randomables)-1)]);

      // Make sure track is streamable
      if ($t->canStream!=1) $t = Collection::getRandomTrack($includeQueued, $includeAll, $lastplaythreshold);

      return $t;
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
    
    function getPendingRequests() {
      $db = new Db();
      
      $requests = array();
      $rs = $db->query("SELECT albumKey, userKey FROM request WHERE approved IS NULL ORDER BY REQUESTED DESC");
      while ($rec = mysql_fetch_array($rs)) {
        $requests[] = $rec['albumKey'];
      }
      
      return $requests;
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