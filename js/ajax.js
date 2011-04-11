var cb = {};
var _QUEUE = new musicQueue();
var skip;
var playerstate = 2;
var muting = 0;

// Gets the latest queue from the server and passes it on to the JS:Queue object
// May be redundant with the existance of the updateQueue function.
// Consider combining in the future.
function getQueue() {
  $.ajax({
    url: '/controller.php',
    dataType: 'json',
    data: 'r=getQueue',
    async: false,
    success: function(d) {
      _QUEUE.init(d.queue);
      skip = d.timestamp - d.queue[0].startplay;      
      if (loggedIn && autoplay) {
        player().rdio_play(_QUEUE.getNext().key);
      } else {
        _QUEUE.updateQueue(d.queue);
        if (window.fluid) { 
          window.fluid.dockBadge = _QUEUE.length();
        }
        refreshQueueDisplay();  
        refreshListeners(d.listeners);         
      }

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
        } else {
          display(d.response);
        }
      } else {
        display('Track successfully added to queue');
      }
      updateQueue();
    }      
  });
}

function setmark(key, val) {
  $.ajax({
    url: '/controller.php',
    dataType: 'json',
    data: 'r=mark&key='+key+'&val='+val,
    success: function(d) {
    }
  })
  
  $('.track#'+key+' .detail').html((val==0?getMarkButtons(key):getMarkStatus(key, val)));
}
