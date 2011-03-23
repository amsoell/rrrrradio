<?php 
  include("configuration.php");
  include("classes/Db.class.php");
  include("include/functions.php");
  
  $c = new Config();
  $db = new Db();
  session_start();  
  
  switch (strtolower($_REQUEST['r'])) {
    case "queue":
      $rec = $db->query("SELECT id FROM queue WHERE trackKey='".$_REQUEST['key']."' AND completed IS NULL");
      if (mysql_num_rows($rec)<=0) {
        $db->query("INSERT INTO queue (trackKey, added) VALUES ('".$_REQUEST['key']."', NOW())");
      }
    case 'getqueue':

      $db->query("SELECT trackKey FROM queue WHERE completed IS NULL ORDER BY added");

      while ($rec = $db->fetch_array()) {
        $tracks .= $rec['trackKey'].'", "';
      }
      if (strlen($tracks)>0) $tracks = substr($tracks, 0, strlen($tracks)-3);
      $tracks = '{ "queue" : ["'.$tracks.'] }';

      print $tracks;
      break;
    case 'finishedTrack':
      break;
  }
