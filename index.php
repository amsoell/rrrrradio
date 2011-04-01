<?php 
  session_start();  

  include("configuration.php");
  include("classes/Db.class.php");
  include("classes/Rdio.class.php");
  include("classes/User.class.php");
  include("classes/Track.class.php");
  include("classes/Queue.class.php");  
  include("classes/Collection.class.php");    
  include("include/functions.php");
  
  $c = new Config();
  $db = new Db();
  $rdio = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
  authenticate();
  
  $token = $rdio->getPlaybackToken(array("domain"=>$c->app_domain));
?>
<html>
  <head>
    <title>Crumppbo</title>
    <script src="https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <script type="text/javascript">
    
      var loggedin = <?php print $rdio->loggedIn()?'true':'false'; ?>;
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


<?php if ($rdio->loggedIn()): ?>
    <div id="nowplaying">
      <div id="song"><span id="song_title"></span> - <span id="song_artist"></span></div>
      <div id="album">From the album <span id="song_album"></span></div>
      <div id="progress"><div id="slider"><div id="time"><span id="time_current"></span> / <span id="time_total"></span></div></div></div>
    </div>
<?php else: ?>
    <div id="intro">
      <h1>Welcome to Crumppbo!</h1>
      <p>
        Crumppbo is the best place to listen to your favorite music along with your friends across the Internet. 
        Crumppbo is powered by Rdio Internet Radio, so you'll need a web subscription to enjoy the music. If you have 
        an account, <a href="<?php print $_SERVER['PHP_SELF']; ?>?op=login">log in</a> and join in!</p>
      <p>Don't have an Rdio account? Sign up for a <a href="<?php print $_SERVER['PHP_SELF']; ?>?op=login">free 7-day trial</a> and see what you think.</p>
    </div>
<?php endif; ?>      
    </div>
    <div id="browser">
      <div id="queue"></div>
      <div id="collection">
        <div id="fadetop"></div>
        <div id="fadebottom"></div>   
        <div id="mask"></div>   
        <ul id="music">
<?php
  foreach (Collection::getArtists() as $artist) {
    $key = explode("|", $artist->key);
    $key = str_replace("rl", "r", $key[0]);
    print "<li class=\"artist closed\" id=\"".$key."\">".$artist->name."</li>\n";
  }
?>  
        </ul>    
      </div>
    </div>
    <div id="time"></div>
    
    <div id="api_swf"></div>
  </body>
</html>