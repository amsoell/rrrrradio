<?php 
  include("configuration.php");
  include("classes/Db.class.php");
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
        $key = $_REQUEST['key'];
        $track = rdioGet(array("method"=>"get", "keys"=>$key));
        if (property_exists($track->result, $key)) {  
        $q->push($track->result->$key, true, $_SESSION['user']->key);
        }
      }
    case 'getqueue':

      $tracks = $q->getQueue();      
      for ($i=0; $i<count($tracks); $i++) {
        $key = $tracks[$i]->key;
        $detail = rdioGet(array("method"=>"get", "keys"=>$key));
        $tracks[$i]->name = $detail->result->$key->name;
        $tracks[$i]->icon = $detail->result->$key->icon;  
        $tracks[$i]->artist = $detail->result->$key->artist;
        $tracks[$i]->album = $detail->result->$key->album;
        $tracks[$i]->canStream = intval($detail->result->$key->canStream);
      }

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
