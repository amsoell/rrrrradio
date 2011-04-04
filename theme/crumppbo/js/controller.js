// UI Function: Loads up the contents of the JS:Queue object into the user interface
function refreshQueueDisplay() {
  $('#queue').empty();
  $.each(_QUEUE.q.slice(_QUEUE.ptr), function(i, track) {
    $t = $('<div></div>').addClass('track').css('background-image', 'url('+track.icon+')');
    $title = $('<div></div>').addClass('title');
    $track = $('<div></div>').addClass('trackname').html(track.name);
    $artist = $('<div></div>').addClass('artist').html(track.artist);
    $title.append($track).append($artist);
    
    if (track.user != null) {
      $userpic = $('<img>').addClass('userpic').attr('src', track.user.icon).attr('width', '14').attr('height', '14');
      $username = $('<div></div>').addClass('username').html('Requested by '+track.user.username);
      $user = $('<div></div>').addClass('user').append($userpic).append($username);
      $t.append($user);
    }
    $t.append($title);
    $('#queue').append($t);
  })
}

$(document).ready(function() {
  function bind() {
    $('li.album').unbind();
    $('li.album.closed').click(function() {
      node = $(this);
  
      $.ajax({
        url: '/data.php',
        dataType: 'json',
        data: 'a='+$(this).attr('id'),
        beforeSend: function() {
          node.append($('<div class="ajax-loader"></div>'));
        },
        success: function(d) {
  
          trackNode = $('<ul>');
          for (i=0; i<d.length; i++) {
            track = $('<li class="track" id="'+d[i].key+'" value="'+d[i].trackNum+'">'+d[i].name+'</li>');
            if (!d[i].canStream) track.addClass('unstreamable');
            trackNode.append(track);
          }
          node.append(trackNode).removeClass('closed');
          bind();
          $('.ajax-loader').remove();
        }      
      });
    });
  
  
    $('li.artist').unbind();
    $('li.artist.closed').unbind().click(function() {
      node = $(this);
  
      $.ajax({
        url: '/data.php',
        dataType: 'json',
        data: 'r='+$(this).attr('id'),

        beforeSend: function() {
          node.append($('<div class="ajax-loader"></div>'));        
        },
        success: function(d) {
  
          albumNode = $('<ul>');
          for (i=0; i<d.length; i++) {
            albumNode.append($('<li class="album closed" id="'+d[i].key+'">'+d[i].name+'</li>'));
          }
          node.append(albumNode).removeClass('closed');
          bind();
          $('.ajax-loader').remove();
        }      
      });
    });
    
    
    $('li.track').unbind().click(function() {
      node = $(this);
  
      $.ajax({
        url: '/controller.php',
        dataType: 'json',
        data: 'r=queue&key='+$(this).attr('id'),
        beforeSend: function() {
          $('#queue').append($('<div></div>').addClass('track placeholder'));
        },
        success: function(d) {  
          if ("response" in d) alert(d.response);
          updateQueue();
        }      
      });
    });    
    
  }
})