  // UI Function: Loads up the contents of the JS:Queue object into the user interface
  function refreshQueueDisplay() {
    $('#queue .track').remove();
    $.each(_QUEUE.q.slice(_QUEUE.ptr), function(i, track) {
      if (i==_QUEUE.ptr) {
        $('.song_title').html(track.name);
        $('.song_artist').html(track.artist);
        $('.song_album').html(track.album);
      }    
    
      $t = $('<div></div>').attr('id', track.key).addClass('track').css('background-image', 'url('+track.icon+')');
      $title = $('<div></div>').addClass('title');
      $track = $('<div></div>').addClass('trackname').html(track.name);
      $artist = $('<div></div>').addClass('artist').html(track.artist);
      $title.append($track).append($artist);
      
      if (track.user != null) {
        $userpic = $('<img>').addClass('userpic').attr('src', track.user.icon).attr('width', '14').attr('height', '14');
        $username = $('<div></div>').addClass('username').html('Requested by '+track.user.username);
        $user = $('<div></div>').addClass('user').append($userpic).append($username);

        $t.addClass('request').attr('rel',track.user.username).append($('<div></div>').addClass('indicator')).append($user);
      }
      
      $details = $('<div></div>').addClass('detail').append(
        $('<div></div>').addClass('like').html('Love it!').prepend($('<img>').attr('src','/theme/cramppbo/images/heart.png')).click(function() { likeit($(this).parent().parent().attr('id')); })
      ).append(
        $('<div></div>').addClass('dislike').html('Hate it!').prepend($('<img>').attr('src','/theme/cramppbo/images/cancel.png')).click(function() { dislikeit($(this).parent().parent().attr('id')); })
      );        
      
      $t.append($details).append($title).hover(function() {
        $('.request[rel='+$(this).attr('rel')+']').find('.user').fadeIn();
        $(this).children('.detail').fadeIn();        
      }, function() {
        $(this).children('.detail').fadeOut();      
        $('.request[rel='+$(this).attr('rel')+']').find('.user').fadeOut();
      });
  
      if (i==0) {
        $('#queue').prepend($t);
      } else {
        $('#queue').append($t);
      }
    });
  }
  
  function likeit(key) {
    alert(key);
  }
  
  function dislikeit(key) {
    alert(key);
  }
  
  function refreshListeners(listeners) {
    $('#toolbar .listeners').empty();
    $.each(listeners, function(i, listener) {
      $l = $('<img>').attr('src', listener.icon).attr('alt', listener.username).attr('title', listener.username).qtip({
        content: {
          text: 'Loading...',
          ajax: {
            url: 'profile.php',
            type: 'GET',
            data: { key: listener.key },
            once: false
          }
        },
        position: {
          my: 'top right',
          adjust: {
            x: -16,
            y: 5
          }          
        },
        style: {
          classes: 'ui-tooltip-dark ui-tooltip-shadow ui-tooltip-rounded'
        }
      });
      $('#toolbar .listeners').append($l);
    })
  }

  function bind() {
    $('div.album').unbind();
    $('div.album.closed').click(function() {
      node = $(this).parent();
      albumTitle = $(this).find('p').html();
      $(this).find('p').html('').addClass('ajax-loader')
      $(this).removeClass('closed');
      $(this).siblings().fadeOut();
        
      $.ajax({
        url: '/data.php',
        dataType: 'json',
        data: 'a='+$(this).attr('id'),
        success: function(d) {
          node.append($('<h1></h1>').html(albumTitle));
          trackNode = $('<ol>');
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
    
    $('#toolbar').bind('mouseenter', function() {
      $(this).find('#tools #nowplaying').animate({ top: '-30px' }, 150)
      $(this).find('#tools #ops').animate({ top: 0 }, 150)      
    }).bind('mouseleave blur focusout', function () {
      $(this).find('#tools #nowplaying').animate({ top: 0 }, 150)    
      $(this).find('#tools #ops').animate({ top: '30' }, 150)          
    });
    
    $(window).bind('blur', function() {
      $('#tools #nowplaying').animate({ top: 0 }, 150)    
      $('#tools #ops').animate({ top: '30' }, 150)          
      $('div.qtip:visible').qtip('hide');  
      $('.track .user:visible').fadeOut();    
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
          $('#collection #browser #album').empty();
          for (i=0; i<d.length; i++) {
            $a = $('<div></div>').addClass('album').attr('id', d[i].key);
            $a.append($('<img>').attr('src',d[i].icon).attr('width','125').attr('height','125'));
            
            $d = $('<div></div>').addClass('detail');
            $d.append($('<h1></h1>').html(d[i].name));
            
            $tracks = $('<ol></ol>');
            $prevtrack = 0;
            for (j=0; j<d[i].tracks.length; j++) {
              if (d[i].tracks[j].trackNum!=$prevtrack) {
                $t = $('<li></li>').addClass('track').attr('id', d[i].tracks[j].key).attr('value', d[i].tracks[j].trackNum).html(d[i].tracks[j].name);
                $tracks.append($t);
              }
              $prevtrack = d[i].tracks[j].trackNum;
            }
            $d.append($tracks);
            
            $('#collection #browser #album').append($a.append($d));
          }
          bind();
          $('.ajax-loader').remove();
        }      
      });
    }).dblclick(function() {
      node = $(this);
  
      $.ajax({
        url: '/data.php',
        dataType: 'json',
        data: 'r='+$(this).attr('id')+'&force=1',

        beforeSend: function() {
          node.append($('<div class="ajax-loader"></div>'));        
        },
        success: function(d) {
          $('#collection #browser #album').empty();
          for (i=0; i<d.length; i++) {
            $a = $('<div></div>').addClass('album closed').attr('id', d[i].key);
            $a.append($('<img>').attr('src',d[i].icon).attr('width','125').attr('height','125'));
            $a.append($('<p></p>').html(d[i].name));
            $('#collection #browser #album').append($a);
          }
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
    })   
  }
  
  
  if (window.fluid) {
    window.resizeTo(660, 770);
  }