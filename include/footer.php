      </div>
    </div>
    <div id="footer">
      <div class="details">
        &copy; 2011 rrrrradio | 
        <a href="faq.php">FAQ</a> | 
        Five Rs, each one louder than the last.
      </div>
      <div class="rdio">
        rrrrradio is powered by <a href="<?php print $c->affiliate_link_subscribe; ?>" target="_blank"><img src="/theme/cramppbo/images/RdioWhiteLogo.png" border="0" /></a>
      </div>
    </div>
    <script type="text/javascript">
    
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', '<?php print $c->ga_tag; ?>']);
      _gaq.push(['_trackPageview']);
    
      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
    
    </script>    
  </body>
</html>