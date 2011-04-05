<?php 
  include("configuration.php");
  include("classes/Db.class.php");
  include("classes/Rdio.class.php");
  include("classes/Track.class.php");
  include("classes/Collection.class.php");
  include("classes/Queue.class.php");
  include("include/functions.php");
  
  $c = new Config();
  $db = new Db();
  $rdio = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
  $q = new Queue();
  session_start();  
  authenticate();
  
  switch (strtolower($_REQUEST['r'])) {

    case "queue":
      // add a requested track to the queue
      if ($q->isComingUp($_REQUEST['key'])) { 
        $response = "Track is already in upcoming queue";
      } elseif (!$rdio->loggedIn()) {
        $response = "You are not logged in to Rdio";
      } else {
        $key = $_REQUEST['key'];
        $track = $rdio->get(array("keys"=>$key));
        if (property_exists($track->result, $key)) {  
          $q->push($track->result->$key, true, $_SESSION['user']->key);
        }
      }
    case 'getqueue':

      $tracks = $q->getQueue();      
      for ($i=0; $i<count($tracks); $i++) {
        $key = $tracks[$i]->key;
        $detail = $rdio->get(array("keys"=>$key));
        $tracks[$i]->name = $detail->result->$key->name;
        $tracks[$i]->icon = $detail->result->$key->icon;  
        $tracks[$i]->artist = $detail->result->$key->artist;
        $tracks[$i]->album = $detail->result->$key->album;
        $tracks[$i]->canStream = intval($detail->result->$key->canStream);
      }
      
      $return  = '{ ';
      if (strlen($response)>0) $return .= '"response": '.json_encode($response).', ';
      $return .='"timestamp" : '.time().', "queue" : '.json_encode($tracks).' }';

      print $return;
      break;
    case 'finishedTrack':
      break;
    case 'getrandomtrack':
      $c = new Collection();
      $t = $c->getRandomTrack();
  }
