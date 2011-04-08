<?php 
  include("include/libs.php");
  
  if (isset($_REQUEST['key'])) {
    $u = new User($_REQUEST['key']);
?>
<div class="profile">
  <img src="<?php print $u->icon; ?>" width="64" height="64" align="left"/>
  <h1><?php print $u->firstName." ".$u->lastName; ?>
  <h2>aka: <?php print $u->username; ?></h2>
  <br style="clear: both;" />
  <br />
  <h3>Top Artists</h3>
  <ol>
<?php
  foreach ($u->getTopArtists() as $artist) {
    print "<li>".$artist->name."</li>\n";
  }  
?>
  </ol>
  <br /><br />
  <h3>Top Songs</h3>
  <ol>
<?php
  foreach ($u->getTopTracks() as $track) {
    print "<li>".$track->name." - ".$track->artist."</li>\n";
  }  
?>
  </ol>
</div>
<?php
  }
?>