<?php include("include/header.php"); ?>
<div style="text-align: left;">
    <h1>How it works</h1>
<?php
    switch ($_REQUEST['page']) {
        case 2:
?>
    <img src="/images/howitworks/step2.png" align="right" vspace="5" />
    <p>&nbsp;</p>
    <p>After clicking on the "Request a song" bar, the request panel will slide down. Browse through the list of available artists on the left hand side of the page until you find the one you want. Then just give it a click to see the albums and songs available from that artist on the right.</p>
    <p>Once you've found your song, click it to see more about that song...</p>
    <p align="right"><a href="howitworks.php?page=3">Next &raquo;</a></p>
<?php
            break;
        case 3:
?>
    <img src="/images/howitworks/step3.png" align="right" vspace="5" />
    <p>&nbsp;</p>
    <p>From the song popup screen, you can see details about the song and are given several options:</p>
    <br />
    <ul>
<?php if ($c->previews): ?>    
        <li><b>Preview this song</b> --  Listen to a bit of the song to make sure it's the one you want to hear</li>
<?php endif; ?>        
        <li><b>Add to queue</b> -- Request the song to be added to the end of the upcoming play queue</li>
        <li><b>Dedicate</b> -- Request the song and attach a dedication. Dedications can be sent out via email.</li>
        <li><b>Mark as favorite</b> -- Indicate that you like this song. It will be added to an ongoing list of your favorite tracks for easy access</li>
    </ul>
    <p>If you decide you don't want to do anything with the song, you can just hit ESC or click the X in the upper left corner.</p>
    <p align="right"><a href="howitworks.php?page=4">Next &raquo;</a></p>
<?php
            break;            
        case 4:
?>
    <img src="/images/howitworks/step4.png" align="right" vspace="5" />
    <p>&nbsp;</p>
    <p>If you know the song or the album you're interested in, but forget the artist, you can always use the search feature.</p>
    <p>From the request panel, click in the text box on the right and just start typing. As results are found, they will be diplayed below.</p>
    <p>If your song or album comes up under "More songs" or "More artists," it can be added to our collection but isn't ready quite yet. Just click it and let us know you would like it added and we'll take it from there!</p>
    <p>Once you do find a song you want to hear in your search results, just click it and the request pane will scroll down to the appropriate location.</p>
    <p align="right"><a href="howitworks.php?page=5">Next &raquo;</a></p>
<?php
            break;            
        case 5:
?>
    <p>There are many more features and tricks to find, but this will get you started. If you have any questions, please feel free to <a href="inquiries@rrrrradio.com">contact us</a>. Enjoy!</p>
    <p align="center"><a href="howitworks.php">&laquo;Start Over&raquo;</a></p>
<?php
            break;            
        default:
?>
    <img src="/images/howitworks/step1.png" align="right" vspace="5" />
    <p>&nbsp;</p>
    <p>When you first get to <?php print $c->sitename; ?> and log in to your Rdio account, you'll start listening to the play queue. If you like what you see coming up, you don't have to do anything else! Just enjoy!</p>
    <p>If you would like to add some specific songs to the upcoming queue, though, you can start by clicking on the "Request a song" bar at the top of the page.</p>
    <p align="right"><a href="howitworks.php?page=2">Next &raquo;</a></p>
<?php
    }
?>
    <br style="clear: both; " />
</div>
</body>
</html>