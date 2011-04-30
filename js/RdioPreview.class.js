  ///////////////////////////////////////////
  // Rdio SWF callback function assignments
  ///////////////////////////////////////////
  
var RdioPreview = {
  ready: function() {
  },

  playingTrackChanged: function(newTrack) {
  },
  
  playStateChanged: function(state) {
    if (state==1) {
      $('.preview img').attr('src', '/theme/cramppbo/images/preview.playing.gif');
      RdioPreviewer().rdio_seek(30);      
      setTimeout("RdioPreviewer().rdio_setMute(0)", 1000);
    } else if (state==2) {
      $('.preview img').attr('src', '/theme/cramppbo/images/preview.play.png');
    } else {
      RdioPreviewer().rdio_setMute(1);
      $('.preview img').attr('src', '/theme/cramppbo/images/ajax-loader.gif');
    }
  },
  
  playingSomewhereElse: function() {
    display("Sorry, you're streaming Rdio somewhere else");
  },
  
  positionChanged: function(pos) {
  },
  
  volumeChanged: function(level) {
  }
}
