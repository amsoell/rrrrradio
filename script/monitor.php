#!/usr/bin/php
<?php 
  $dir = __FILE__;
  $dir = substr($dir, 0, strrpos($dir, "/"))."/";

  include("$dir../configuration.php");
  include("$dir../classes/Db.class.php");
  include("$dir../classes/Rdio.class.php");
  include("$dir../classes/Track.class.php");
  include("$dir../classes/Queue.class.php");
  include("$dir../classes/Collection.class.php");
  include("$dir../include/functions.php");
  
  $c = new Config();
  $db = new Db();
  $q = new Queue();
  $rdio = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);    
 
  $length = $q->length();
  while ($q->length()<=3) {
    $track = Collection::getRandomTrack(false, User::getCurrentListeners());
    
    // force Rdio request so data is cached for XHR calls
    $rdio->get(array("keys"=>$track->key, "extras"=>"trackNum"));
    if ($q->isRandomable($track)) $q->push($track);
  }
?>
