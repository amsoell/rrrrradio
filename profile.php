<?php 
  include("include/libs.php");
  
  if (isset($_REQUEST['key'])) {
    $u = new User($_REQUEST['key']);
?>
<div class="profile <?php print $_REQUEST['view']; ?>">
  <img src="<?php print $u->icon; ?>" width="64" height="64" align="left"/>
  <h1><?php print $u->firstName." ".$u->lastName; ?></h1>
  <h2>aka: <?php print $u->username; ?></h2>
  <h2>Requests available: 
<?php  
  $left = $u->requestsLeft(); 

  print $left;
  
  if ($left<=0) {
    print " (renew in ".intval(($u->requestsRenew()-time())/60)." minutes)";
  }  
  
?></h2>  
  <br style="clear: both;" />
<?php 
    if ($_REQUEST['view']=='full') { 
?>  
  <br />
  <ol>
    <h3>Top Artists</h3>    
<?php
  foreach ($u->getTopArtists() as $artist) {
    print '<li><a href="#!/'.$artist->key.'">'.$artist->name."</a></li>\n";
  }  
?>
    <li class="export">&nbsp;</li>
  </ol>
  <ol>
    <h3>Top Songs</h3>    
<?php
  $rs = $u->getTopTracks();
  
  $trackKeys = Array();
  foreach ($rs as $key=>$track) {
    $trackKeys[] = $track->key;
    print '<li><a href="#!/'.$track->artistKey.'/'.$track->albumKey.'/'.$track->key.'">'.$track->name." - ".$track->artist."</a></li>\n";
  }  
?>
    <li class="export" rel="<?php print implode(',',$trackKeys); ?>" title="Save this as an Rdio playlist">Export these songs to an Rdio playlist</li>
  </ol>
  <ol>
    <h3>Recent Requests</h3>    
<?php
  $keys = $u->getRecentRequests();
  $rs = $rdio->get(array('keys'=>implode(',',$keys)));
  $trackKeys = Array();
  foreach ($keys as $key) {
    $trackKeys[] = $key;
    print '<li><a href="#!/'.$rs->result->$key->artistKey.'/'.$rs->result->$key->albumKey.'/'.$rs->result->$key->key.'">'.$rs->result->$key->name." - ".$rs->result->$key->artist."</a></li>\n";
  }  
?>
    <li class="export" rel="<?php print implode(',',array_reverse($trackKeys)); ?>" title="Save this as an Rdio playlist">Export these songs to an Rdio playlist</li>
  </ol>
  <ol>
    <h3>Favorite Songs</h3>    
<?php
  $rs = $rdio->get(array('keys'=>implode(',',$u->getFavoriteTracks())));
  
  $trackKeys = Array();
  foreach ($rs->result as $key=>$track) {
    $trackKeys[] = $track->key;
    print '<li><a href="#!/'.$track->artistKey.'/'.$track->albumKey.'/'.$track->key.'">'.$track->name." - ".$track->artist."</a></li>\n";
  }  
?>
    <li class="export" rel="<?php print implode(',',$trackKeys); ?>" title="Save this as an Rdio playlist">Export these songs to an Rdio playlist</li>
  </ol>
</div>
<?php
    }
  }
  

?>