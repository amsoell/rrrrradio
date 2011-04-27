<?php 
  include("configuration.php");
  include("classes/Db.class.php");
  include("classes/Rdio.class.php");
  include("classes/User.class.php");
  include("classes/SearchResult.class.php");  
  include("classes/Collection.class.php");
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
    if (array_key_exists('all', $_REQUEST)) {
      $albums = $rdio->getAlbumsForArtist($args);
    } else {    
      $albums = $rdio->getAlbumsForArtistInCollection($args);
    }
    $albums = $albums->result;

    usort($albums, "albumsort");
    
    $randomables = Collection::getRandomables();
    for ($i=0; $i<count($albums); $i++) {
      for ($j=0; $j<count($albums[$i]->tracks); $j++) {
        $albums[$i]->tracks[$j]->randomable = in_array($albums[$i]->tracks[$j]->key, $randomables)?"1":"0";
      }
    }
    print json_encode($albums);
  } elseif (array_key_exists('a', $_REQUEST)) {
    // GET TRACKS FROM A SPECIFIED ALBUM AND RETURN VIA JSON OBJECT
    $args = array("album"=>$_REQUEST['a'], "extras"=>"trackNum", "user"=>$c->rdio_collection_userkey);
    if (array_key_exists('force', $_REQUEST)) $args['force'] = 1;
    if (array_key_exists('all', $_REQUEST)) {
      unset($args['user']);
      $args['keys'] = $_REQUEST['a'];
      $tracks = $rdio->get($args);   
    } else {
      $tracks = $rdio->getTracksForAlbumInCollection($args);   
    }
    $tracks = $tracks->result;

    print json_encode($tracks);
  } elseif (array_key_exists('t', $_REQUEST)) {
    // GET TRACK DETAIL AND RETURN VIA JSON OBJECT
    $key = $_REQUEST['t'];
    $args = array("keys"=>$_REQUEST['t'], "extras"=>"trackNum");
    if (array_key_exists('force', $_REQUEST)) $args['force'] = 1;
    $tracks = $rdio->get($args);    
    $tracks = $tracks->result->$key;

    print json_encode($tracks);
  } elseif (array_key_exists('term', $_REQUEST)) {
    $results = array();
    $results_tracks = array();
    $results_albums = array();
    $results_artists = array();

    // GET MATCHING TRACKS
    $sqlx = "SELECT CONCAT(artistKey,'/',albumKey,'/',trackKey) AS `key`, name, album, artist, icon, 10 AS confidence FROM searchindex WHERE name LIKE '%".addslashes($_REQUEST['term'])."%' UNION ";
    $sqlx .= "SELECT CONCAT(artistKey,'/',albumKey,'/',trackKey) AS `key`, name, album, artist, icon, MATCH(name) AGAINST ('".addslashes($_REQUEST['term'])."') AS confidence FROM searchindex WHERE MATCH(name) AGAINST ('".addslashes($_REQUEST['term'])."') ORDER BY confidence DESC";
    
    $rs = $db->query($sqlx);
    while (($rec = mysql_fetch_array($rs)) && ($rec['confidence']>($maxconfidence*(3/5)))) {
      if (!isset($maxconfidence)) $maxconfidence = $rec['confidence'];
      $r = new SearchResult($rec['key']);
      $r->name = $rec['name'];
      $r->album = $rec['album'];
      $r->artist = $rec['artist'];
      $r->icon = $rec['icon'];
      $r->type = 't';
      $r->confidence = $rec['confidence'];

      if (!array_key_exists($rec['key'], $results_tracks)) $results_tracks[$rec['key']] = $r;
    }

    // GET MATCHING ALBUMS
    $rs = $db->query("SELECT DISTINCT CONCAT(artistKey,'/',albumKey) AS `key`, album, artist, icon FROM searchindex WHERE album LIKE '%".addslashes($_REQUEST['term'])."%'");
    while ($rec = mysql_fetch_array($rs)) {
      $r = new SearchResult($rec['key']);
      $r->album = $rec['album'];
      $r->artist = $rec['artist'];
      $r->icon = $rec['icon'];      
      $r->type = 'a';
      $r->confidence = 10;
      if (!array_key_exists($rec['key'], $results_albums)) $results_albums[$rec['key']] = $r;
    }
    
    // GET MATCHING ARTISTS
    $rs = $db->query("SELECT DISTINCT CONCAT(artistKey) AS `key`, artist FROM searchindex WHERE artist LIKE '%".addslashes($_REQUEST['term'])."%'");    
    while ($rec = mysql_fetch_array($rs)) {
      $r = new SearchResult($rec['key']);
      $r->artist = $rec['artist'];
      $r->type = 'r';
      $r->confidence = 10;
      if (!array_key_exists($rec['key'], $results_artists)) $results_artists[$rec['key']] = $r;
    }
    
    // ORDER RESULT GROUPS WITH HIGHEST MATCH AT TOP
    $results = array_merge($results_artists, $results_tracks, $results_albums);
    
    // GET ARTISTS/ALBUMS FROM RDIO API
    $res = $rdio->search(array('query'=>$_REQUEST['term'], 'types'=>'Album', 'never_or'=>true, 'count'=>10));
    $res = $res->result->results;
    
    
    foreach ($res as $item) {
      if ($item->canStream) {
        unset($r);
        switch ($item->type) {
          case 'r':
            $r = new SearchResult($item->key);
            $r->artist = $item->name;
            $r->type = '_r';
            break;
          case 'a':
            if (!array_key_exists($item->artistKey.'/'.$item->key, $results)) {        
              $r = new SearchResult($item->artistKey.'/'.$item->key);
              $r->artist = $item->artist;
              $r->album = $item->name;          
              $r->icon = $item->icon;      
              $r->type = '_a';          
            }
            break;
        }
        
        if (isset($r)) $results[] = $r;
      }
    }
    
    usort($results, "searchresultsort");
    
    print json_encode($results);
  } elseif ($_REQUEST['v']=='requests') {
    $rs = $db->query("SELECT GROUP_CONCAT(albumKey) as albumKeys FROM request WHERE approved IS NULL");
    
    if ($rec = mysql_fetch_array($rs)) {
      $requests[] = $rdio->get(array("keys"=>$rec['albumKeys']))->result;
    }
    
    print json_encode($requests);
  }

  function albumsort($a, $b) { 
    if ($a->releaseDate==$b->releaseDate) return 0; 
    return (($a->releaseDate < $b->releaseDate) ? 1 : -1); 
  }
  
  function searchresultsort($a, $b) {
    $sortOrder = array("r"=>1, "t"=>2, "a"=>3, "_a"=>4);
      
    if ($sortOrder[$a->type]==$sortOrder[$b->type]) {
      if ($a->confidence==$b->confidence) return 0;
      
      return (($a->confidence < $b->confidence) ? 1 : -1);
    }
    

    return (($sortOrder[$a->type] > $sortOrder[$b->type]) ? 1 : -1);
  }