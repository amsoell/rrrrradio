<?php
  include("libs.php");
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
      var autoplay = <?php print $c->autoplay?'true':'false'; ?>;
      var api_swf = "http://www.rdio.com/api/swf/";
      var playbackToken = "<?php print $token->result; ?>";
      var domain = "<?php print $c->app_domain; ?>";
      var refreshInterval = <?php print $c->refresh_interval; ?>;
<?php if ($c->debug): ?>
      var __debugMode = true;
<?php endif; ?>      
    </script>
    <script src="js/RdioStream.class.js"></script>     
    <script src="js/RdioPreview.class.js"></script>         
    <script src="js/musicqueue.class.js"></script>  
    <script src="js/ajax.js"></script>       
<?php  if (strpos($_SERVER['HTTP_USER_AGENT'],"iPhone")): ?>
    <link type="text/css" rel="stylesheet" href="/css/jqtouch.min.css" />     
    <link type="text/css" rel="stylesheet" href="/css/jqtouch.themes/apple/theme.min.css" />         
    <link type="text/css" rel="stylesheet" href="/css/style.touch.css" /> 
    <script src="/js/jqtouch.min.js" type="application/x-javascript" charset="utf-8"></script> 
    <script type="text/javascript" charset="utf-8"> 
        var jQT = new $.jQTouch({
            icon: 'jqtouch.png',
            addGlossToIcon: false,
            startupScreen: 'jqt_startup.png',
            statusBar: 'black',
            preloadImages: [
                '../../themes/jqt/img/back_button.png',
                '../../themes/jqt/img/back_button_clicked.png',
                '../../themes/jqt/img/button_clicked.png',
                '../../themes/jqt/img/grayButton.png',
                '../../themes/jqt/img/whiteButton.png',
                '../../themes/jqt/img/loading.gif'
                ]
        });
    </script> 
    <script src="/js/controller.touch.js"></script>       
<?php  else: ?> 
    <script src="/js/jquery.fancybox-1.3.4.pack.js"></script>       
    <script src="/js/jquery-ui-1.8.11.custom.min.js"></script>  
    <script src="/js/jquery.qtip.pack.js"></script>          
    <script src="/js/jquery.scrollTo-1.4.2-min.js"></script>                  
    <script src="/js/jquery.hotkeys.js"></script>       
    <script src="/js/controller.js"></script>      
    <script type="text/javascript" src="http://use.typekit.com/goy0iya.js"></script>
    <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.8.2r1/build/reset/reset-min.css">    
    <link type="text/css" rel="stylesheet" href="/theme/<?php print $c->theme; ?>/css/style.css" /> 
    <link type="text/css" rel="stylesheet" href="/css/jquery.fancybox-1.3.4.css" />     
    <link type="text/css" rel="stylesheet" href="/css/ui-lightness/jquery-ui-1.8.11.custom.css" />          
    <link type="text/css" rel="stylesheet" href="/css/jquery.qtip.min.css" />         
<?php endif; ?>    
    <link id="page_favicon" href="/favicon.ico" rel="icon" type="image/x-icon" />
  </head>
  <body onload="$('.autoclick').trigger('click')">
