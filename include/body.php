    <div id="wrapper">
      <div id="toolbar">
        <div id="tools">      
          <div id="nowplaying">
            <span class="song_title"></span> : <span class="song_artist"></span>
  
          </div>
          <div id="ops">
            <img class="player_mute" src="/theme/cramppbo/images/tools/sound_high.png" />
            <span id="volume">
              <img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="1" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="2" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="3" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="4" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="5" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="6" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="7" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="8" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="9" /><img src="/theme/cramppbo/images/volnotch.gif" alt="" rel="10" />
            </span>
<?php if (isset($_SESSION['user'])) : ?>            
            <span class="advanced">
              <img class="export" src="/theme/cramppbo/images/tools/doc_export.png" rel="livequeue" title="Export the current queue to your Rdio account"/>
            </span>
<?php endif; ?>
          </div>
          <span class="nowlistening"><span class="indicators"></span><span class="listeners"></span></span>                      
        </div>
        <div class="progress"><div class="slider"></div></div>                  
      </div>
      <div id="page">
        <div id="collection">
          <div class="header">  
            Request a song
            <form action="<?php print $_SERVER['PHP_SELF']; ?>" method="GET">
              <input name="q" id="search" title="search" value="search" class="empty" />
            </form>
          </div>
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
          <div id="now_playing">
            <div id="song"><span class="song_title"></span> - <span class="song_artist"></span></div>
            <div id="album">From the album <span class="song_album"></span></div>
            <br />
            <div id="detail">
              <div class="song_likes"></div>
              <div class="song_requests"></div>
              <div class="song_requester"></div>              
            </div>
          </div>
<?php else: ?>
          <div id="intro">
            <h2>Welcome to <?php print $c->sitename; ?>!</h2>
            <p>
              <?php print $c->sitename; ?> is the best place to listen to your favorite music along with your friends across the Internet. 
              <?php print $c->sitename; ?> is powered by Rdio Internet Radio, so you'll need a web subscription to enjoy the music. If you have 
              an account, <a href="<?php print $_SERVER['PHP_SELF']; ?>?op=login">log in</a> and join in!</p>
            <p>For a quick intro into how <?php print $c->sitename; ?> works, check out our <a href="howitworks.php" class="lightbox iframe">introduction</a></p>
            <p>Don't have an Rdio account? Sign up for a <a href="<?php print $c->affiliate_link_subscribe; ?>">free 7-day trial</a> and see what you think.</p>
          </div>
<?php endif; ?>      
        </div>
      </div>
      <div id="RdioStream"></div>
      <div id="RdioPreview"></div>      
      <div class="hidden">
        <div id="popup">
          <div id="message"></div>
        </div>
        <a href="#popup" id="popuplink"></a>
<?php
  if (!$rdio->loggedIn()) { 
?>    
        <div id="howitworks1">
          <img src="/images/howitworks/step1.png" align="right" vspace="5" />
          <p>When you first get to <?php print $c->sitename; ?> and log in to your Rdio account, you'll start listening to the play queue. If you like what you see coming up, you don't have to do anything else! Just enjoy!</p>
          <p>If you would like to add some specific songs to the upcoming queue, though, you can start by clicking on the "Request a song" bar at the top of the page.</p>
        </div>
        
      </div>
<?php 
  }
?>
    </div>
  </div>
