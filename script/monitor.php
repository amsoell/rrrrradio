#!/usr/bin/php5-latest -c/home/170824/etc
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
  $db = new Db();
  $q = new Queue();
  $rdio = new RdioLib(RDIO_CLIENT_ID, RDIO_CLIENT_SECRET, RDIO_CLIENT_REDIRECT_URI);

  $length = $q->length();

  while ($q->length()<=3) {
    $track = Collection::getRandomTrack(false, User::getCurrentListeners());

    // force Rdio request so data is cached for XHR calls
    $rdio->get(array("keys"=>$track->key, "extras"=>"trackNum"));
    //if ($q->isRandomable($track)) {
    //  print "Track not randomable";
    //} else
    if ($q->push($track)) {
      print "Added: ". $track->name ."\n";
    } else {
      print "Could not add to queue\n";
    }
  }
?>
