<?php 
  include("configuration.php");
  include("classes/Db.class.php");
  include("classes/Artist.class.php");
  include("classes/Album.class.php");
  include("classes/Track.class.php");
  include("classes/Queue.class.php");  
  include("classes/Collection.class.php");    
  include("include/functions.php");
  
  $c = new Config();
  $db = new Db();
  session_start();  
  
  authenticate();
  
  $token = rdioGet(array("method"=>"getPlaybackToken","domain"=>$c->app_domain));
?>
<html>
  <head>
    <title>Crumppbo -- radioasoell:mark IV</title>
    <script src="https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <script type="text/javascript">
      var api_swf = "http://www.rdio.com/api/swf/";
      var playbackToken = "<?php print $token->result; ?>";
      var domain = "<?php print $c->app_domain; ?>";
    </script>
    <script src="js/musicqueue.class.js"></script>  
    <script src="js/controller.js"></script>   
  </head>
  <body>
    <h2>Queue</h2>
    <ul id="queue"></ul>
    <h2>Collection</h2>
    <ul id="music">
<?php
  $cx = new Collection();
  foreach ($cx->getArtists() as $artist) {
    print "<li class=\"artist closed\" id=\"".$artist->key."\">".$artist->name."</li>\n";
  }
?>  
    </ul>
    <div id="api_swf"></div>
  </body>
</html>