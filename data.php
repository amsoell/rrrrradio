<?php 
  include("configuration.php");
  include("classes/Db.class.php");
  include("classes/Rdio.class.php");
  include("classes/User.class.php");
  include("include/functions.php");
  
  $c = new Config();
  $db = new Db();
  $rdio = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
  session_start();  
  authenticate();  
  
  if (array_key_exists('r', $_REQUEST)) {
    // GET ALBUMS FROM A SPECIFIED ARTIST AND RETURN VIA JSON OBJECT  
    $args = array("artist"=>$_REQUEST['r'], "user"=>$c->rdio_collection_userkey);
    if (array_key_exists('force', $_REQUEST)) $args['force'] = 1;
    $albums = $rdio->getAlbumsForArtistInCollection($args);
    $albums = $albums->result;

    usort($albums, "albumsort");
    print json_encode($albums);
  } elseif (array_key_exists('a', $_REQUEST)) {
    // GET TRACKS FROM A SPECIFIED ALBUM AND RETURN VIA JSON OBJECT
    $args = array("album"=>$_REQUEST['a'], "extras"=>"trackNum", "user"=>$c->rdio_collection_userkey);
    if (array_key_exists('force', $_REQUEST)) $args['force'] = 1;
    $tracks = $rdio->getTracksForAlbumInCollection($args);    
    $tracks = $tracks->result;

    print json_encode($tracks);
  }

  function albumsort($a, $b) { 
    if ($a->releaseDate==$b->releaseDate) return 0; 
    return (($a->releaseDate < $b->releaseDate) ? 1 : -1); 
  }
