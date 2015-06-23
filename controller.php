<?php
  include("configuration.php");
  include("classes/Db.class.php");
  include("classes/RdioLib.class.php");
  include("classes/Track.class.php");
  include("classes/Collection.class.php");
  include("classes/Queue.class.php");
  include("include/functions.php");

  $c = new Config();
  $db = new Db();
  $rdio = new RdioLib(RDIO_CLIENT_ID, RDIO_CLIENT_SECRET, RDIO_REDIRECT_URI);
  $q = new Queue();
  session_start();

  switch (strtolower($_REQUEST['r'])) {
    case "mark":
      //! mark
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
      //! queue
      // add a requested track to the queue
      if (isset($_REQUEST['key'])) {
        $track = new Track($_REQUEST['key']);
        if ($q->isComingUp($_REQUEST['key'])) {
          $response = "Track is already in upcoming queue";
        } elseif ((!$rdio->is_authenticated()) && (!isset($_REQUEST['oauth_token']))) {
          $response = "You are not logged in to Rdio";
        } else {
          $e = $q->isRequestable($track);
          if ($e instanceof QueueError) {
            $response = $e->errorMessage;
          } else {
            $q->push($track, true, (isset($_REQUEST['userKey'])?$_REQUEST['userKey']:$_SESSION['user']->key), $_REQUEST['client'], $_REQUEST['dedicationName'], $_REQUEST['dedicationRecipient'], $_REQUEST['dedicationMessage']);
          }
        }
      }

      // break intentionally omitted

    case 'getqueue':
      //! getqueue
      if (isset($_REQUEST['userKey'])) {
        $db->query("UPDATE user SET lastseen=UNIX_TIMESTAMP(NOW()), lastclient='".addslashes($_REQUEST['client'])."' WHERE `key`='".addslashes($_REQUEST['userKey'])."'");
      }

      $tracks = $q->getQueue();

      if (count($tracks)>0) {
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
      } else {
        $track = new stdClass();
        $track->startplay = 0;
        $track->endplay = 9999999999;
        $track->user = null;
        $track->mark = null;
        $track->key = "";
        $track->name = "Stream unavailable";
        $track->album = "";
        $track->artist = "Rdio servers are unavailable, check back soon!";
        $track->albumKey = "";
        $track->artistKey = "";
        $track->icon = "http://".$c->app_domain."/images/failwhale200.jpg";
        $track->duration = 9999999999;
        $track->trackNum = null;
        $track->canStream = 1;
        $track->likes = 0;
        $track->requests = 0;
        $track->bigIcon = "http://".$c->app_domain."/images/failwhale600.jpg";
        $tracks = Array($track);

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
      //! save
      if ($rdio->is_authenticated()) {
        $rdio->createPlaylist(array('name'=>$_REQUEST['name'], 'description'=>'Exported from '.$c->sitename.' on '.date('F j, Y'), 'tracks'=>$_REQUEST['tracks']));
      } else {
        print "Not logged in";
      }
      break;
    case 'request':
      //! request
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
      //! deny
      $db->query("DELETE FROM request WHERE albumKey='".$_REQUEST['a']."' LIMIT 1");

      break;
    case "removefromcollection":
      //! removefromcollection
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
      //! approve
      $rs = $rdio->get(array('keys'=>$_REQUEST['a'], 'extras'=>'trackKeys'));

      $keys = array();
      foreach ($rs->result->$_REQUEST['a']->trackKeys as $trackKey) {
        $keys[] = $trackKey;
      }

      $rdio->addToCollection(array('keys'=>implode(',',$keys)));

      $db->query("UPDATE request SET approved=UNIX_TIMESTAMP(NOW()) WHERE albumKey='".$_REQUEST['a']."'");

      // break intentionally omitted.
    case "refresh":
      //! refresh
      // clear API cache for artist/album requests
      $db->query("DELETE FROM api_usage WHERE params LIKE '%getArtistsInCollection%'");
      $db->query("DELETE FROM api_usage where params LIKE '%getAlbumsForArtist%'");

      break;
    case 'finishedTrack':
      //! finishedtrack
      break;
    case 'getrandomtrack':
      //! getrandomtrack
      $c = new Collection();
      $t = $c->getRandomTrack();
  }
