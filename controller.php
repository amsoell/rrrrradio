<?php 
  include("configuration.php");
  include("classes/Db.class.php");
  include("classes/Artist.class.php");
  include("classes/Album.class.php");
  include("classes/Track.class.php");
  include("classes/Collection.class.php");
  include("classes/Queue.class.php");
  include("include/functions.php");
  
  $c = new Config();
  $db = new Db();
  $q = new Queue();
  session_start();  
  
  switch (strtolower($_REQUEST['r'])) {

    case "queue":
      // add a requested track to the queue
      if (!$q->isComingUp($_REQUEST['key'])) {      
        $q->push($_REQUEST['key'], true, $_SESSION['userKey']);
      }
    case 'getqueue':

      $tracks = $q->getQueue();

      $tracks = '{ "timestamp" : '.time().', "queue" : '.json_encode($tracks).' }';

      print $tracks;
      break;
    case 'finishedTrack':
      break;
    case 'getrandomtrack':
      $c = new Collection();
      $t = $c->getRandomTrack();
      print "<pre>".print_r($t, true)."</pre>";
  }
