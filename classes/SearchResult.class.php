<?php
  class SearchResult {
    var $key;
    var $name;
    var $album;
    var $artist;
    var $icon;
    var $type;
    var $confidence;
    
    function __construct($key) {
      $this->key = $key;
    }
  }
?>