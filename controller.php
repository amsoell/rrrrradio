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
    case "mark":
      $track = new Track($_REQUEST['key']);
      $track->mark($_REQUEST['val'], isset($_REQUEST['userKey'])?$_REQUEST['userKey']:null);
      
      break;
    case "ignore":
      if (isset($_REQUEST['off'])) {
        $q->removeMuter($_SESSION['user']->key);
      } else {
        $q->addMuter($_SESSION['user']->key);
      }
      
      // break intentionally omitted

    case "queue":
      // add a requested track to the queue
      if (isset($_REQUEST['key'])) {
        $track = new Track($_REQUEST['key']);             
        if ($q->isComingUp($_REQUEST['key'])) { 
          $response = "Track is already in upcoming queue";
        } elseif ((!$rdio->loggedIn()) && (!isset($_REQUEST['oauth_token']))) {
          $response = "You are not logged in to Rdio";
        } else {
          $e = $q->isRequestable($track);
          if ($e instanceof QueueError) {
            $response = $e->errorMessage;
          } else {
            $q->push($track, true, (isset($_REQUEST['userKey'])?$_REQUEST['userKey']:$_SESSION['user']->key));
          }
        }
      }
      
      // break intentionally omitted
      
    case 'getqueue':
      if (isset($_REQUEST['userKey'])) {
        $db->query("UPDATE user SET lastseen=UNIX_TIMESTAMP(NOW()), lastclient='".addslashes($_REQUEST['client'])."' WHERE `key`='".addslashes($_REQUEST['userKey'])."'");          
      }
    
      $tracks = $q->getQueue();  
      for ($i=0; $i<count($tracks); $i++) {
        $key = $tracks[$i]->key;
        $detail = $rdio->get(array("keys"=>$key, "extras"=>"trackNum,bigIcon"));
        $tracks[$i]->name = $detail->result->$key->name;
        $tracks[$i]->icon = $detail->result->$key->icon;  
        $tracks[$i]->artist = $detail->result->$key->artist;
        $tracks[$i]->album = $detail->result->$key->album;
        $tracks[$i]->bigIcon = $detail->result->$key->bigIcon;
        $tracks[$i]->canStream = intval($detail->result->$key->canStream);
      }
      
      $listeners = User::getCurrentListeners(); 
      
      $return  = '{ ';
      if (strlen($response)>0) $return .= '"response": '.json_encode($response).', ';
      $return .='"timestamp" : '.time().', "queue" : '.json_encode($tracks).', "listeners" : '.json_encode($listeners);
      if ($_SESSION['user']->isCurator) $return .= ', "pendingRequests": '.count(Collection::getPendingRequests()); 
      $return .= ' }';

      print $return;

      break;
    case 'save':
      if ($rdio->loggedIn()) {
        $rdio->createPlaylist(array('name'=>$_REQUEST['name'], 'description'=>'Exported from '.$c->sitename.' on '.date('F j, Y'), 'tracks'=>$_REQUEST['tracks']));
      } else {
        print "Not logged in";
      }
      break;
    case 'request':
      $item = $rdio->get(array('keys'=>$_REQUEST['item']));
      
      $db->query("INSERT INTO request (albumKey, userKey, requested) VALUES ('".$_REQUEST['item']."', '".$_SESSION['user']->key."', UNIX_TIMESTAMP(NOW()))");
      
      $headers  = "Organization: rrrrradio\r\n";
      $headers .= "MIME-Version: 1.0\r\n";
      $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
      $headers .= "X-Priority: 3\r\n";
      $headers .= "X-Mailer: PHP". phpversion() ."\r\n";
      $headers .= "From: ".$c->sitename." <admin@".$c->app_domain.">\r\n";
      $headers .= "Reply-to: ".$c->admin_email;
      
      mail($c->admin_email, $c->sitename." request", "The following has been requested for addition to the ".$c->sitename." station:\n\n".
        "Artist: ".$item->result->$_REQUEST['item']->artist."\n".
        "Album: ".$item->result->$_REQUEST['item']->name."\n".
        "URL: ".$item->result->$_REQUEST['item']->shortUrl."\n".
        "iPhone: ".str_replace("http://","rdio://",$item->result->$_REQUEST['item']->shortUrl)."\n".
        "Requested By: ".$_SESSION['user']->firstName." ".$_SESSION['user']->lastName."\n\n".
        "Once the item has been added to the collection, flush the API cache with this link:\n".
        "http://".$c->app_domain."/controller.php?r=refresh", $headers);
        
      break;
    case "deny":
      $db->query("DELETE FROM request WHERE albumKey='".$_REQUEST['a']."' LIMIT 1");
      
      break;
    case "removefromcollection":
      $thekeys = explode(',',$_REQUEST['keys']);
      
      $a = array();
      foreach ($thekeys as $key) {
        switch (substr($key, 0, 1)) {
          case 't':
            $t = $rdio->get(array("keys"=>$key));
            $a[] = $t->result->$key->albumKey;
            break;
          case 'a':
            $a[] = $key;
            break;
        }
      }
        
      if (isset($a)) {
        $t = $rdio->get(array("keys"=>implode(',',$a), "extras"=>"trackKeys"));
        
        $keys = array();
        foreach ($t->result as $a) {
          foreach ($a->trackKeys as $trackKey) {
            $keys[] = $trackKey;
          }
        }
        
        $rdio->removeFromCollection(array("keys"=>implode(',',$keys)));        
        $db->query("DELETE FROM queue WHERE trackKey IN ('".implode("','",$keys)."')");
      }

      break;
    case "approve":
      $rs = $rdio->get(array('keys'=>$_REQUEST['a'], 'extras'=>'trackKeys'));
      
      $keys = array();
      foreach ($rs->result->$_REQUEST['a']->trackKeys as $trackKey) {
        $keys[] = $trackKey;
      }
      
      $rdio->addToCollection(array('keys'=>implode(',',$keys)));
      
      $db->query("UPDATE request SET approved=UNIX_TIMESTAMP(NOW()) WHERE albumKey='".$_REQUEST['a']."'");
      
      // break intentionally omitted.
    case "refresh":
      // clear API cache for artist/album requests
      $db->query("DELETE FROM api_usage WHERE params LIKE '%getArtistsInCollection%'");
      $db->query("DELETE FROM api_usage where params LIKE '%getAlbumsForArtist%'");
      
      break;
    case 'finishedTrack':
      break;
    case 'getrandomtrack':
      $c = new Collection();
      $t = $c->getRandomTrack();
  }
