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
    $tracks = rdioGet(array("method"=>"get", "keys"=>$_REQUEST['ids'], "extras"=>"trackNum"));
    
    print "<!-- pre>".print_r($tracks, true)."</pre -->";
    
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
<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="POST">
<textarea name="ids" rows="8" cols="50"></textarea> <input type="submit" value="Add" />
</form>
</body>
</html>