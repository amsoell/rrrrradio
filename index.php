<?php 
  include("include/header.php");
?>
      <div id="collection">
        <div class="header">Request a song</div>
        <div id="browser">
          <ul id="music">
<?php
  foreach (Collection::getArtists() as $artist) {
    $key = explode("|", $artist->key);
    $key = str_replace("rl", "r", $key[0]);
    print "<li class=\"artist closed\" id=\"".$key."\">".$artist->name."</li>\n";
  }
?>  
          </ul>    
          <div id="album">
          </div>    
        </div>
      </div>
      <div id="queue">
<?php if ($rdio->loggedIn()): ?>
        <div id="nowplaying">
          <div id="song"><span class="song_title"></span> - <span class="song_artist"></span></div>
          <div id="album">From the album <span class="song_album"></span></div>
        </div>
<?php else: ?>
        <div id="intro">
          <h1>Welcome to <?php print $c->sitename; ?>!</h1>
          <p>
            <?php print $c->sitename; ?> is the best place to listen to your favorite music along with your friends across the Internet. 
            <?php print $c->sitename; ?> is powered by Rdio Internet Radio, so you'll need a web subscription to enjoy the music. If you have 
            an account, <a href="<?php print $_SERVER['PHP_SELF']; ?>?op=login">log in</a> and join in!</p>
          <p>Don't have an Rdio account? Sign up for a <a href="<?php print $_SERVER['PHP_SELF']; ?>?op=login">free 7-day trial</a> and see what you think.</p>
        </div>
<?php endif; ?>      
      </div>
    </div>
    <div id="api_swf"></div>
    <div class="hidden">
<?php
  if (!$rdio->loggedIn()) : ?>    
      <div id="welcome">
        <h1><?php print $c->sitename; ?>: Social Listening</h1>
        <p>We're putting the social back into radio. Listen to music in a truly social way with your friends; You hear what they hear. You listen to what they request, and they hear your requests along with you. Make requests, make dedications, and maybe make some friends.
        <p>Starting is simple</p>
        <ol>
          <li>Log in to your Rdio account</li>
          <p><?php print $c->sitename; ?> is powered by Rdio's massive online collection of music, so you'll need a subscription to join in. Plans start at $4.99/month, but you can always give it a try with a free 7-day trial to see if you like it first.</p>
          <p><a href="<?php print $_SERVER['PHP_SELF']; ?>?op=login">Log in now</a>, or <a href="http://rdio.com/accounts/login">sign up for a free 7-day trial</a>.</p>
          <li>Authorize <?php print $c->sitename; ?>to connect to your Rdio account</li>
          <p><a href="<?php print $_SERVER['PHP_SELF']; ?>?op=login">Click here</a> to hook it up.</p>
          <li>Start listening</li>
          <p>That's the easy part. Just come back to this site, click "play," and you're done!</p>
        </ol>    
      </div>
      <a href="#welcome" id="welcomelink" class="autoclick">Introduction</a>
    </div>
<?php 
  endif;
  include("include/footer.php");
?>
    
