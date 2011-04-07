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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title><?php print $c->sitename; ?></title>
    <script src="https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <script type="text/javascript">
    
      var loggedIn = <?php print $rdio->loggedIn()?'true':'false'; ?>;
      var api_swf = "http://www.rdio.com/api/swf/";
      var playbackToken = "<?php print $token->result; ?>";
      var domain = "<?php print $c->app_domain; ?>";
    </script>
    <script src="js/musicqueue.class.js"></script>  
    <script src="js/controller.js"></script>   
    <script src="/theme/<?php print $c->theme; ?>/js/controller.js"></script>    
    <script src="/js/jquery.fancybox-1.3.4.pack.js"></script>       
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.8.2r1/build/reset/reset-min.css">    
    <link type="text/css" rel="stylesheet" href="/theme/<?php print $c->theme; ?>/css/style.css" /> 
    <link type="text/css" rel="stylesheet" href="/css/jquery.fancybox-1.3.4.css" />     
    <link id="page_favicon" href="/favicon.ico" rel="icon" type="image/x-icon" />
  </head>
  <body onload="$('.autoclick').trigger('click')">
    <div id="wrapper">
      <div id="toolbar">
        <div id="tools">      
          <div id="nowplaying">
            <span class="song_title"></span> : <span class="song_artist"></span>
  
          </div>
          <div id="ops">
            <img class="player_mute" src="/theme/cramppbo/images/tools/sound_high.png" />
            <span id="volume">
              <img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="1" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="2" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="3" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="4" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="5" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="6" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="7" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="8" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="9" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="10" />
            </span>
<!--
            <img src="/theme/cramppbo/images/tools/heart.png" />
            <img src="/theme/cramppbo/images/tools/cancel.png" />
-->
          </div>
          <span class="nowlistening"><span class="listeners"></span></span>                      
        </div>
        <div class="progress"><div class="slider"></div></div>                  
      </div>
      <div id="page">
