<?php
  class SearchResult {
    var $key;
    var $name;
    var $album;
    var $artist;
    var $icon;
    var $type;
    
    function __construct($key) {
      $this->key = $key;
    }
  }
?>