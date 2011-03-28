<?php
  include("../configuration.php");
  include("../classes/Db.class.php");
  include("../classes/Artist.class.php");
  include("../classes/Album.class.php");
  include("../classes/Track.class.php");
  include("../classes/Collection.class.php");
  include("../classes/Queue.class.php");
  include("../include/functions.php");
  
  $c = new Config();
  $db = new Db();
  $cx = new Collection();
  session_start();  
  
  authenticate();  
?>
<html>
  <head>
    <style>
      .incollection { color: #0F0; }
      .new { color: #F00; }
    </style>
  </head>
  <body>
<?php
  if (strlen($_REQUEST['ids'])>0) {
    switch ($_REQUEST['type']) {
      case "search":
        $result = rdioGet(array("method"=>"search", "query"=>$_REQUEST['ids'], "types"=>"Album", "extras"=>"trackKeys"));
        
        foreach ($result->result->results as $album) {
          if (!Collection::albumExists($album->key))print '<a href="'.$_SERVER['PHP_SELF'].'?ids='.$album->key.'">';
          print $album->name."</a><br />";
        }
        break;
      case 'tracks':
        processTracks($_REQUEST['ids']);
        break;
      case "albums":
        $albums = explode(",", $_REQUEST['ids']);
        
        foreach ($albums as $album) {
          $albuminfo = rdioGet(array("method"=>"get", "keys"=>trim($album), "extras"=>"trackKeys"));
          $ids = implode(",", $albuminfo->result->$album->trackKeys);
          
          processTracks($ids);
        }
        break;
    }
  }
  
  function processTracks($ids) {
        global $cx; 
        
        $tracks = rdioGet(array("method"=>"get", "keys"=>$ids, "extras"=>"trackNum"));
        
        foreach ($tracks->result as $track) {
          if (Collection::trackExists($track->key)) {
            print '<span class="incollection">Already in collection:</span> '.$track->name.' <i>by</i> '.$track->artist.' (from <i>'.$track->album.'</i>)<br />';
            
            $albumKey = $track->albumKey;
            $album = rdioGet(array("method"=>"get", "keys"=>$albumKey));
            $cx->addAlbum($album->result->$albumKey);
          } else {
            print '<span class="new">Adding:</span> '.$track->name.' <i>by</i> '.$track->artist.' (from <i>'.$track->album.'</i>)<br />';  
            $cx->addTrack($track);    
          }
        }
  
  }
?>
<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="GET">
<textarea name="ids" rows="8" cols="50"></textarea><br />
<select name="type"><option value="albums"<?php if ($_REQUEST['type']=='albums') print " selected" ?>>Albums</option><option value="tracks"<?php if ($_REQUEST['type']=='tracks') print " selected" ?>>Tracks</option><option value="search"<?php if ($_REQUEST['type']=='search') print " selected" ?>>General Search</option></option></select><input type="submit" value="Add" /> 
</form>
</body>
</html>