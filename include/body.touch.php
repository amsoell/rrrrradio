<div id="home" class="current">
  <div class="toolbar">
    <h1>rrrrradio</h1>
    <a href="#" class="refresh button leftButton refresh">refresh</a>
<?php if ($rdio->loggedIn()) : ?>
    <a href="#collection" class="button add flip">+</a>
<?php else: ?>
    <a href="<?php print $_SERVER['PHP_SELF']; ?>?op=login" rel="external" class="button flip">login</a>
<?php endif; ?>
  </div>
  <h2>Now Playing</h2>
  <ul id="nowplaying" class="rounded">
  </ul>
  <h2>Coming Up</h2>
  <ul id="queue" class="rounded">
  </ul>
</div>
<div class="form" id="collection">
  <div class="toolbar">
    <h1>Add request</h1>
    <a href="#home" class="back button leftButton">Hide</a>
  </div>
  <ul class="rounded">
<?php
  foreach (Collection::getArtists() as $artist) {
    $key = explode("|", $artist->key);
    $key = str_replace("rl", "r", $key[0]);
    print "<li class=\"arrow artist closed\" id=\"".$key."\"><a href=\"#\">".$artist->name."</a></li>\n";
  }
?>  
  </ul>    
</div>

<div class="form" id="albums">
  <div class="toolbar">
    <h1></h1>
    <a href="#" class="back">Artists</a>
  </div>
  <ul class="rounded">
  </ul>
</div>

<div class="form" id="tracks">
  <div class="toolbar">
    <h1></h1>
    <a href="#" class="back">Albums</a>
  </div>
  <ul class="rounded">
  </ul>
</div>

<div class="form" id="trackdetail">
  <div class="toolbar">
    <h1></h1>
    <a href="#" class="back">Tracks</a>
  </div>
  <ul class="individual rounded">
    <li><a href="#" id="request">Request</a></li>
  </ul>
</div>