<?php
  define('RDIO_CONSKEY', 'vmu7x6u4rktv468vae8dn28h');
  define('RDIO_CONSSEC', 'GrP5WnY7gF');

  class Config {
    var $sitename = "rrrrradio stage";
    var $app_domain = "domainname.com";
    var $app_dir = "/";  
    var $rdio_callback_url = "";
    var $rdio_oembed_url = "http://www.rdio.com/api/oembed/";
    var $rdio_collection_userkey = "userKey";
    
    var $lastfm_conskey = "";
    var $lastfm_conssec = "";
    var $lastfm_api_url = "http://ws.audioscrobbler.com/2.0/";    
    
    var $db_username = "";
    var $db_password = "";
    var $db_database = "";
    
    var $song_buffer = 3;
    var $theme = "cramppbo";
    
    var $free_if_queue_less_than = 7;
    var $requests_per_hour = 5;
    var $requests_per_artist_per_hour = 3;
    var $requests_per_album_per_hour = 2;
    
    function __construct() {
      $this->rdio_callback_url = "http://".$this->app_domain.$this->app_dir;
    }
  }
?>
