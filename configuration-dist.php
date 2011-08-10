<?php
  define('RDIO_CONSKEY', '');
  define('RDIO_CONSSEC', '');

  class Config {
    var $sitename = "rrrrradio";
    var $app_domain = "rrrrradio.com";
    var $app_dir = "/";  
    var $rdio_callback_url = "";
    var $rdio_oembed_url = "http://www.rdio.com/api/oembed/";
    var $rdio_activeplaylist_url = "http://rd.io/x/QVvMyzNeAas";
    var $rdio_collection_userkey = "<rdiouserkey>";
    
    var $admin_email = "your@email.com";
    
    var $lastfm_conskey = "";
    var $lastfm_conssec = "";
    var $lastfm_api_url = "http://ws.audioscrobbler.com/2.0/";    
    
    var $db_username = "";
    var $db_password = "";
    var $db_database = "";
    
    var $song_buffer = 3;
    var $theme = "cramppbo";
    
    var $affiliate_link_subscribe = "http://click.linksynergy.com/fs-bin/click?id=Y5hfCBRENkU&offerid=221756&type=3&subid=0";
    var $affiliate_link_prefix = "http://click.linksynergy.com/fs-bin/click?id=Y5hfCBRENkU&subid=&offerid=221756.1&type=10&tmpid=7950&RD_PARM1=";
    var $ga_tag = "";
    
    var $free_if_queue_less_than = 7;
    var $requests_per_hour = 5;
    var $requests_per_artist_per_hour = 3;
    var $requests_per_album_per_hour = 2;
    
    // HOURS BEFORE REPEATING RANDOMLY QUEUED TRACKS
    var $random_rotation = 5;
    // MAXIMUM LENGTH (IN SECONDS) FOR RANDOMLY QUEUED TRACKS
    var $random_max_length = 300;
    // NUMBER OF REQUESTS REQUIRED TO BOOST A LONGER TRACK INTO RANDOM ROTATION
    var $random_requests_required = 4;
    
    var $autoplay = true;
    var $previews = false;
    var $refresh_interval = 15;
    var $debug = true;
    
    var $smtp_host = "";
    var $smtp_port = 465;
    var $smtp_username = "";
    var $smtp_password = "";
    
    
    function __construct() {
      $this->app_domain = $_SERVER['HTTP_HOST'];
      $this->rdio_callback_url = "http://".$this->app_domain.$this->app_dir;
    }
  }
?>
