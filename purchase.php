<?php
  include("include/libs.php");
  
  if (isset($_REQUEST['key'])) {
    $info = $rdio->get(array("keys"=>$_REQUEST['key']));
    
    if (count($info->result->$_REQUEST['key'])>0) {
      $url = "http://www.rdio.com".$info->result->$_REQUEST['key']->url;
    }
  } elseif (isset($_REQUEST['trackKey'])) {
    $info = $rdio->get(array("keys"=>$_REQUEST['trackKey']));
    
    if (count($info->result->$_REQUEST['trackKey'])>0) {
      $album = $rdio->getObjectFromUrl(array("url"=>$info->result->$_REQUEST['trackKey']->albumUrl));
      if (isset($album->result)>0) {
        $url = $album->result->shortUrl;
      }
    }
  }
  
  if (isset($url)) header("Location: ".$c->affiliate_link_prefix.urlencode($url));
?>