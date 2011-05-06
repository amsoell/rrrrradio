var _QUEUE = new musicQueue();
var skip;
var playerstate = 2;
var muting = 0;
var ignoring = 0;
var currentPosition = 0;

// Gets the latest queue from the server and passes it on to the JS:Queue object
// May be redundant with the existance of the updateQueue function.
// Consider combining in the future.
function getQueue($play, $seek) {
  console.log("running getqueue")
  $.ajax({
    url: '/controller.php',
    dataType: 'json',
    data: 'r=getQueue',
    async: false,
    success: function(d) {
      _QUEUE.init(d.queue);
      skip = d.timestamp - d.queue[0].startplay;      

      _QUEUE.updateQueue(d.queue);

      if (window.fluid) window.fluid.dockBadge = _QUEUE.length();
      refreshQueueDisplay();  
      refreshListeners(d.listeners);  
      refreshRequestBadge(d.pendingRequests);
      
      _QUEUE.ptr = -1;

      if ($play && loggedIn) {
        RdioPlayer().rdio_play(_QUEUE.getNext().key);      
      }
    }
  });
}

function exportToPlaylist(playlistName, tracks) {
  $.ajax({
    url: '/controller.php',
    dataType: 'json',
    data: 'r=save&name='+playlistName+'&tracks='+tracks,
    async: false,
    success: function(d) {
      display("Playlist saved as '"+$('#rdio_playlist').val() + "'", {
        ok: function() {
          $.fancybox.close();
        }
      });            
    }
  });
}

// Get the latest queue from the server and pass it on to the JS:Queue object for processing.
function updateQueue() {
  $.ajax({
    url: '/controller.php',
    dataType: 'json',
    data: 'r=getQueue',
    success: function(d) {
      _QUEUE.updateQueue(d.queue);
      if (window.fluid) { 
        window.fluid.dockBadge = _QUEUE.length();
      }
      refreshQueueDisplay();  
      refreshListeners(d.listeners);    
      refreshRequestBadge(d.pendingRequests);      
    }
  });
  
  return true;
}

function queueTrack(trackKey) {
  $.ajax({
    url: '/controller.php',
    dataType: 'json',
    data: 'r=queue&key='+trackKey,
    beforeSend: function() {
      $('#queue').append($('<div></div>').addClass('track placeholder'));
    },
    success: function(d) {  
      if ("response" in d) {
        if (window.fluid) {
          fluid.showGrowlNotification({
            title: "Oops...",
            description: d.response
          })
          $.fancybox.close();
        } else {
          display(d.response);
        }
      } else {
        $('#album #'+trackKey).addClass('randomable');
        display('Track successfully added to queue');
      }
      updateQueue();
    }      
  });
}

function displayTrack(trackKey) {
  $.ajax({
    url: '/data.php',
    dataType: 'json',
    data: 't='+trackKey,
    success: function(d) {
      $detail = $('<div></div>').addClass('trackPreview')
        .append($('<img>').addClass('coverart').attr('src',d.icon))
        .append($('<div></div>').addClass('detail')
          .append($('<h2></h2>').html(d.name))
          .append($('<h3></h3>').html(d.artist+' - '+d.album))
          .append($('<div></div>').html(parseInt(d.duration / 60)+':'+(String('0'+d.duration %60, -2).substr(-2,2))))
        )

        
      if (d.canStream) {
        $detail.append($('<div></div>').addClass('_tip footnote').html('Tip: Double click tracks to skip this popup and add songs to the queue immediately'))
          .find('.detail').append($('<div><div>').addClass('preview').attr('rel', trackKey).html('Preview this song').prepend($('<img>').attr('src','/theme/cramppbo/images/preview.play.png')))
                          .append($('<div><div>').addClass('request').attr('rel', trackKey).html('Add to queue').prepend($('<img>').attr('src','/theme/cramppbo/images/preview.add.png')))
                          .append($('<div><div>').addClass('like').attr('rel', trackKey).html('Mark as favorite').prepend($('<img>').attr('src','/theme/cramppbo/images/tools/heart.png')));                          
                               
      } else {
        $detail.find('.detail').append($('<br /><div><div>').addClass('like').attr('rel', trackKey).html('Mark as favorite').prepend($('<img>').attr('src','/theme/cramppbo/images/tools/heart.png')))
                               .append($('<div></div>').html('This track is not available for playback at this time.'));
      }
  
      display($detail);
    }
  }); 
}

function setmark(key, val) {
  $('.qtip').qtip('hide');
  $.ajax({
    url: '/controller.php',
    dataType: 'json',
    data: 'r=mark&key='+key+'&val='+val,
    success: function(d) {
    }
  })
  
  $('.track#'+key+' .detail').html((val==0?getMarkButtons(key):getMarkStatus(key, val)));
}
