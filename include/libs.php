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
?>