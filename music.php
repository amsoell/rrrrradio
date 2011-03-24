<?php 
  include("configuration.php");
  include("classes/Db.class.php");
  include("include/functions.php");
  
  $c = new Config();
  $db = new Db();
  session_start();  
  
  authenticate();
  
  $token = rdioGet(array("method"=>"getPlaybackToken","domain"=>$c->app_domain));
?>
<html>
  <head>
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
    <ul id="music">
<?php
  $artists = $db->query("SELECT `key`, `name` FROM artist ORDER BY `name`");
  while ($rec = mysql_fetch_array($artists)) {
    print "<li class=\"artist closed\" id=\"".$rec['key']."\">".$rec['name']."</li>\n";
  }
?>  
    </ul>
    <div id="api_swf"></div>
    <a id="addTrack">click to add a track</a>
    <a id="next">click to go to the next track</a>
    <a id="play">click to go to play</a>    
  </body>
</html>