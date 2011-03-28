<?php 
  include("configuration.php");
  include("classes/Db.class.php");
  include("include/functions.php");
  
  $c = new Config();
  $db = new Db();
  session_start();  
  
  if (array_key_exists('r', $_REQUEST)) {
    // GET ALBUMS FROM A SPECIFIED ARTIST AND RETURN VIA JSON OBJECT  
    $albums = rdioGet(array("method"=>"getAlbumsForArtistInCollection", "artist"=>$_REQUEST['r']));
    $albums = $albums->result;

    usort($albums, "albumSort");
    print json_encode($albums);
  } elseif (array_key_exists('a', $_REQUEST)) {
    // GET TRACKS FROM A SPECIFIED ALBUM AND RETURN VIA JSON OBJECT
    $tracks = rdioGet(array("method"=>"getTracksForAlbumInCollection", "album"=>$_REQUEST['a'], "extras"=>"trackNum"));    
    $tracks = $tracks->result;

    print json_encode($tracks);
  }

  function albumsort($a, $b) { 
    if ($a->releaseDate==$b->releaseDate) return 0; 
    return (($a->releaseDate < $b->releaseDate) ? 1 : -1); 
  }