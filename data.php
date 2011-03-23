<?php 
  include("configuration.php");
  include("classes/Db.class.php");
  include("include/functions.php");
  
  $c = new Config();
  $db = new Db();
  session_start();  
  
  if (array_key_exists('r', $_REQUEST)) {
    // GET ALBUMS FROM A SPECIFIED ARTIST AND RETURN VIA JSON OBJECT
    $db->query("SELECT `key`, `name` FROM album WHERE artistKey='".$_REQUEST['r']."' ORDER BY releaseDate DESC");
    
    $albums = "";
    while ($rec = $db->fetch_array()) {
      $key = explode("|", $rec['key']);
      $key = $key[0];
      $key = str_replace("al", "a", $key);
      $albums .= '{ "key": "'.$key.'", "name": '.json_encode($rec['name']).' }, ';
    }
    if (strlen($albums)>0) $albums = substr($albums, 0, strlen($albums)-2);
    
    $albums = "[ ".$albums. " ]";
    print $albums;
  } elseif (array_key_exists('a', $_REQUEST)) {
    // GET TRACKS FROM A SPECIFIED ALBUM AND RETURN VIA JSON OBJECT
    $db->query("SELECT `key`, `name`, trackNum FROM track WHERE albumKey='".$_REQUEST['a']."' ORDER BY trackNum");
    
    $tracks = "";
    while ($rec = $db->fetch_array()) {
      $tracks .= '{ "key": "'.$rec['key'].'", "name": '.json_encode($rec['name']).', "trackNum": '.$rec['trackNum'].' }, ';
    }
    if (strlen($tracks)>0) $tracks = substr($tracks, 0, strlen($tracks)-2);
    
    $tracks = "[ ".$tracks. " ]";
    print $tracks;
  }
