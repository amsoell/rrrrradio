#!/usr/bin/php5-latest
<?php
  $dir = __FILE__;
  $dir = substr($dir, 0, strrpos($dir, "/"))."/";

  include("$dir../configuration.php");
  include("$dir../classes/Db.class.php");
  include("$dir../classes/RdioLib.class.php");
  include("$dir../classes/Track.class.php");
  include("$dir../classes/Queue.class.php");
  include("$dir../classes/Collection.class.php");
  include("$dir../include/functions.php");

  $c = new Config();
  $rdio = new RdioLib(RDIO_CLIENT_ID, RDIO_CLIENT_SECRET, RDIO_CLIENT_REDIRECT_URI);
  $db = new Db();

  // FIRST, CLEAN UP THE API_USAGE TABLE
  $db->query("DELETE FROM api_usage WHERE executed<UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY))");

  // QUERY RDIO FOR ALL TRACKS/ALBUMS/ARTISTS IN THE COLLECTION FOR FASTER XHR SEARCHING
  $start = 0;
  $db->query("SET AUTOCOMMIT = 0");
  $db->query("START TRANSACTION");
  $db->query("DELETE FROM searchindex");
  do {
    $result = $rdio->getTracksInCollection(array("user"=>$c->rdio_collection_userkey, "sort"=>"dateAdded", "start"=>$start, "count"=>100));

    foreach ($result->result as $track) {
      $sqlx = "REPLACE INTO searchindex (trackKey, albumKey, artistKey, name, album, artist, icon) VALUES ".
        "('".$track->key."', '".$track->albumKey."', '".$track->artistKey."', '".addslashes($track->name)."', '".addslashes($track->album)."', '".addslashes($track->artist)."', '".addslashes($track->icon)."')";
      $db->query($sqlx);
    }

    $start += 100;
  } while (count($result->result)>0);
  $db->query("COMMIT");
?>
