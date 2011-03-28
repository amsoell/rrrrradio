<?php 
  include("configuration.php");
  include("classes/Db.class.php");
  include("classes/User.class.php");
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
    <title>Crumppbo</title>
    <script src="https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <script type="text/javascript">
      var api_swf = "http://www.rdio.com/api/swf/";
      var playbackToken = "<?php print $token->result; ?>";
      var domain = "<?php print $c->app_domain; ?>";
    </script>
    <script src="js/musicqueue.class.js"></script>  
    <script src="js/controller.js"></script>   
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.8.2r1/build/reset/reset-min.css">    
    <link type="text/css" rel="stylesheet" href="/css/theme/crumppbo/style.css" /> 
  </head>
  <body>
    <div id="queue"></div>
    <div id="collection">
      <div id="fadetop"></div>
      <div id="fadebottom"></div>   
      <div id="mask"></div>   
      <ul id="music">
<?php
  foreach (Collection::getArtists() as $artist) {
    print "<li class=\"artist closed\" id=\"".$artist->key."\">".$artist->name."</li>\n";
  }
?>  
      </ul>    
    </div>
    
    <div id="api_swf"></div>
  </body>
</html>
