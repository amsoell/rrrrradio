<?php 
  include("include/libs.php");

  $playlists = Queue::getPlaylists();
  
  print "<ul>";
  foreach ($playlists as $playlist) {
    print "<li>Found playlist: ".count($playlist)." tracks started by ".$playlist[0]->user->username." on ".date("Y-m-d", $playlist[0]->startplay)."</li>";
  }
  print "</ul>";
?>