
  function playerMute() {
    RdioPlayer().rdio_setMute(1);
  }
  
  function playerUnmute() {
    if (ignoring==1) unignoreCurrent();
    RdioPlayer().rdio_setMute(0);
  }
  
  function toggleMute(mute) {
    if ((mute == 1) || (mute == 0)) muting = ((mute+1)%2);
    
    if (muting==0) {
      playerMute();
      muting = 1;
      
      $('.player_mute').attr('src','/theme/cramppbo/images/tools/sound_mute.png');
    } else {
      playerUnmute();
      muting = 0;
      ignoring = 0;
            
      $('.player_mute').attr('src','/theme/cramppbo/images/tools/sound_high.png');      
    }
  }
  
  function radioplay() {
    $('#queue').addClass('playing');
    getQueue(true);
  }
   
  
  function setVolume(level, indicatorOnly) { // level = integer from 1 to 10
    if ((level>=0) & (level<=10)) {
      $('#volume').attr('rel',level).children().each(function(i, e) {
        if (indicatorOnly!=true) RdioPlayer().rdio_setVolume(parseInt(level)/10);    
        if (i<level) {
          $(e).attr('src','/theme/cramppbo/images/volnotch.gif');
        } else {
          $(e).attr('src','/theme/cramppbo/images/volnotchoff.gif');
        }
      });
    }
  }
  
  function unignoreCurrent() {
    $.ajax({
      url: '/controller.php',
      data: 'r=ignore&off=true',
      dataType: 'json',
      success: function(d) {      
        _QUEUE.updateQueue(d.queue);      
        refreshListeners(d.listeners);        
      }
    });
    
    ignoring = 0;
    toggleMute(0);  
  }
  
  function ignoreCurrent(unmute) {
    $.ajax({
      url: '/controller.php',
      data: 'r=ignore',
      dataType: 'json',
      success: function(d) {      
        _QUEUE.updateQueue(d.queue);      
        refreshListeners(d.listeners);        
      }
    });
    
    ignoring = 1;
    toggleMute(1);  
  }
  
  
  
  function RdioPlayer() {
    return $('#RdioStream').get(0);
  }
  
  function RdioPreviewer() {
    return $('#RdioPreview').get(0);
  }
  
  function playPreview(trackKey) {
    if (playerstate==1) {
      RdioPlayer().rdio_setMute(1);
    }
    setTimeout("RdioPreviewer().rdio_play('"+trackKey+"')", 1000);
  }
  
  function stopPreview() {
    RdioPreviewer().rdio_setMute(1);
    setTimeout("RdioPreviewer().rdio_stop();RdioPreviewer().rdio_setMute(0);if ((playerstate==1) && (skip==-1)) RdioPlayer().rdio_setMute(0);", 1000);  
  }
  
  function display(msg, buttons) {
    $('.qtip').qtip('hide');

    if (typeof msg == 'object') {
      if (typeof msg.url != 'undefined') {
        $('#popup #message').html('<img src="/theme/cramppbo/images/ajax-loader-bar.gif" />').css({
          width: '500px',
          height: '500px'
        });
        $('#popuplink').trigger('click').css('width','auto');
        
        $.ajax({
          url: msg.url+'&view=full',
          success: function(d) {
            $('#popup #message').html(d);
            $.fancybox.resize();
          }
        })
      } else if (typeof msg.iframe != 'undefined') {
        $('#popup #message').css({ width: '500px', height: '500px' }).append($('<iframe></iframe>').attr('width',500).attr('height', 500).attr('frameborder', 0).attr('src', msg.iframe));
        $('#popuplink').trigger('click').css('width','auto');        
      } else {
        // must be a jquery object. Let's display the contents
        $('#popup #message').html($('<div>').append(msg.clone()).remove().html());
        $('#popuplink').trigger('click');
      }
    } else {
      if ((arguments.length==1) && window.fluid) {
        window.fluid.showGrowlNotification({
            title: 'rrrrradio', 
            description: msg
        });
      } else {
        $('#popup #message').css({
          width: 'auto',
          height: 'auto'
        }).html(msg);    
        if (arguments.length>1) {
          $('#popup #message').append($('<div></div>').addClass('buttons'));      
          for (var key in buttons) {
            $('<a href="javascript:;"></a>').addClass('button').html(key).bind('click', buttons[key]).appendTo('#popup #message .buttons');
          }
  
        } else {
          $close = $('<br /><br /><a href="javascript:;" onClick="$.fancybox.close();">ok</a>');      
          $('#popup #message').append($close);  
        }
  
        $('#popuplink').trigger('click')
      }
    }
  }

  function displayArtistWorks(d) {
    $('#collection #browser #album').empty();
    for (i=0; i<d.length; i++) {
      if (d[i]!=undefined) {
        $a = $('<div></div>').addClass('album').attr('id', d[i].key);
        if (!d[i].canStream) $a.addClass('unstreamable');
        $a.append($('<img>').attr('src',d[i].icon).attr('width','125').attr('height','125'));
        
        $d = $('<div></div>').addClass('detail');
        $d.append($('<h1></h1>').html(d[i].name));
        if (!d[i].canStream) {
          if ((new Date(d[i].releaseDate.replace(/-/gi,"/"))).getTime() > (new Date()).getTime()) {
            $d.append($('<h2></h2>').html('Available: '+(new Date(d[i].releaseDate.replace(/-/gi,"/"))).toLocaleDateString()));
          } else {
            $d.append($('<h2></h2>').html('Unavailable for streaming'));        
          }
        }
        
        $tracks = $('<ol></ol>');
        $prevtrack = 0;
        for (j=0; j<d[i].tracks.length; j++) {
          if (d[i].tracks[j].trackNum!=$prevtrack) {
            $t = $('<li></li>').addClass('track').attr('id', d[i].tracks[j].key).attr('value', d[i].tracks[j].trackNum).html(d[i].tracks[j].name);
            if (d[i].tracks[j].randomable==1) $t.addClass('randomable');
            if (!d[i].canStream) $t.addClass('unstreamable');
            $tracks.append($t);
          }
          $prevtrack = d[i].tracks[j].trackNum;
        }
        $d.append($tracks);
        
        $('#collection #browser #album').append($a.append($d));
      }
    }  
  }

  function getMarkButtons(key) {
    $d = $('<div></div>');
    $d.append(
      $('<div></div>').attr('rel', key).addClass('buytrack').html('Buy track').prepend($('<img>').attr('src','/theme/cramppbo/images/tools/cur_dollar.png')).qtip({
        content: {
          text: "Buy this track from Rdio now."
        },
        position: {
          target: 'mouse',
          my: 'bottom center',
          adjust: {
            y: -15
          }          
        },
        show: {
          delay: 1000
        },
        style: {
          classes: 'ui-tooltip-light ui-tooltip-shadow ui-tooltip-rounded'
        }
        
      }).click(function() { 
        $('.qtip').qtip('hide');
        window.open('/purchase.php?key='+$(this).attr('rel'), '_newtab');
      })    
    ).append(
      $('<div></div>').attr('rel', key).addClass('buyalbum').html('Buy album').prepend($('<img>').attr('src','/theme/cramppbo/images/tools/cur_dollar.png')).qtip({
        content: {
          text: "Buy this album from Rdio now."
        },
        position: {
          target: 'mouse',
          my: 'bottom center',
          adjust: {
            y: -15
          }          
        },
        show: {
          delay: 1000
        },
        style: {
          classes: 'ui-tooltip-light ui-tooltip-shadow ui-tooltip-rounded'
        }
        
      }).click(function() { 
        $('.qtip').qtip('hide');
        window.open('/purchase.php?trackKey='+$(this).attr('rel'), '_newtab');
      })    
    ).append(
      $('<div></div>').attr('rel', key).addClass('like').html('Love it!').prepend($('<img>').attr('src','/theme/cramppbo/images/heart.png')).qtip({
        content: {
          text: "Mark this song as a favorite."
        },
        position: {
          target: 'mouse',
          my: 'bottom center',
          adjust: {
            y: -15
          }          
        },
        show: {
          delay: 1000
        },
        style: {
          classes: 'ui-tooltip-light ui-tooltip-shadow ui-tooltip-rounded'
        }
        
      })
    ).append(
      $('<div></div>').attr('rel', key).addClass('dislike').html('Hate it!').prepend($('<img>').attr('src','/theme/cramppbo/images/cancel.png')).qtip({
        content: {
          text: "Mark this song as 'disliked.' Disliked songs will not come up randomly while you are listening."
        },
        position: {
          target: 'mouse',
          my: 'bottom center',
          adjust: {
            y: -15
          }          
        },
        show: {
          delay: 1000
        },        
        style: {
          classes: 'ui-tooltip-light ui-tooltip-shadow ui-tooltip-rounded'
        }
        
      })
    ).click(function() { setmark($(this).attr('rel'), -1); });
    
    return $d;
  }
  
  function getMarkStatus(key, mark) {
    $d = $('<div></div>');  

    switch (parseInt(mark)) {
      case 1:
        $d.append(
          $('<div></div>').addClass('markstatus').html('You love it!').prepend($('<img>').attr('src','/theme/cramppbo/images/heart.png'))
        );
        break;
      case -1:
        $d.append(
          $('<div></div>').addClass('markstatus').html('You hate it!').prepend($('<img>').attr('src','/theme/cramppbo/images/cancel.png'))
        );
        break;
    }
    
    $d.append(
      $('<div></div>').attr('rel', key).addClass('unmark').html('(unmark)').click(function() { setmark($(this).attr('rel'), 0); })        
    );
    
    return $d;
  
  }

  // UI Function: Loads up the contents of the JS:Queue object into the user interface
  function refreshQueueDisplay() {
    $('#queue .track').remove();
    $.each(_QUEUE.q.slice(_QUEUE.ptr), function(i, track) {
      if (i==_QUEUE.ptr) {
        $('.song_title').html(track.name);
        $('.song_artist').html(track.artist);
        $('.song_album').html(track.album);
        if (track.likes>0) {
          $('.song_likes').html('This song is loved by ' + track.likes + ' listener'+(track.likes>1?'s':'')).prepend($('<img>').attr('src', '/theme/cramppbo/images/tools/heart.png'));
        } else {
          $('.song_likes').html('');
        }
        if (track.requests>0) {
          $('.song_requests').html('This song has been requested ' + (track.requests==1?'once':(track.requests==2?'twice':track.requests+' times'))).prepend($('<img>').attr('src', '/theme/cramppbo/images/tools/request.png'));
        } else {
          $('.song_requests').html('');
        }       
        if (track.user != null) {         
          $('.song_requester').html('Requested by '+track.user.firstName+' '+track.user.lastName).prepend($('<img>').attr('src', track.user.icon))
        } else {
          $('.song_requester').html('');
          
        }
      }    
    
      $t = $('<div></div>').attr('id', track.key).addClass('track').css('background-image', 'url('+track.icon+')');
      $title = $('<div></div>').addClass('title');
      $track = $('<div></div>').addClass('trackname').append($('<a></a>').attr('href', '#!/'+track.artistKey+"/"+track.albumKey+"/"+track.key).html(track.name));
      $artist = $('<div></div>').addClass('artist').append($('<a></a>').attr('href', '#!/'+track.artistKey).html(track.artist));
      $title.append($track).append($artist);
      
      if (track.user != null) {
        $userpic = $('<img>').addClass('userpic').attr('src', track.user.icon).attr('width', '14').attr('height', '14');
        $username = $('<div></div>').addClass('username').html('Requested by '+track.user.username);
        $user = $('<div></div>').addClass('user').append($userpic).append($username);

        $t.addClass('request').attr('rel',track.user.username).append($('<div></div>').addClass('indicator')).append($user);
      }
      
      $details = $('<div></div>').addClass('detail');
      if (skip==-1) {
        if (track.mark==null) {
          $details.append(getMarkButtons(track.key));
        } else {
          $details.append(getMarkStatus(track.key, track.mark));
        }
      } else if ((i==0)) {
        if (loggedIn) {
          $details.append($('<img>').attr('src', '/theme/cramppbo/images/play_button_overlay.png').attr('id', 'playbutton').click(function() {
            $(this).attr('src', '/theme/cramppbo/images/ajax-loader-large-dark.gif').delay(2000).fadeOut(500, function() {
              RdioPlayer().rdio_seek(currentPosition + skip);
              RdioPlayer().rdio_setMute(0);
              $('#queue').addClass('playing');
              skip = -1;
              
              refreshQueueDisplay();
            });
          }));
        }
      }
      
      $t.append($details).append($title);
      if ((playerstate==1)) {
        $t.hover(function() {
          $('.request[rel='+$(this).attr('rel')+']').find('.user').fadeIn();
          $(this).children('.detail').fadeIn();        
        }, function() {
          if (skip==-1) {
            $(this).children('.detail').fadeOut();      
            $('.request[rel='+$(this).attr('rel')+']').find('.user').fadeOut();
          }
        });
      }
  
      if (i==0) {
        $('#queue').prepend($t);
      } else {
        $('#queue').append($t);
      }
    });
  }
    
  function refreshListeners(listeners) {
    $('#toolbar .listeners').empty();
    $.each(listeners, function(i, listener) {
      $l = $('<img>').attr('rel', listener.key).attr('src', listener.icon).attr('alt', listener.username).attr('title', listener.username).qtip({
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
      }).click(function() {
        display({
          url: '/profile.php?key='+$(this).attr('rel')+'&view=full',
          width: 800,
          top: 100
        })
      });
      
      if (_QUEUE.q[0].muted.indexOf(listener.key)>=0) {
        $l.addClass('muted');
      }
      $('#toolbar .listeners').append($l);
    })
  }

  function refreshRequestBadge(num) {
    $('#tools .nowlistening .indicators .requests').remove();
    if (num>0) {
      $('#tools .nowlistening .indicators').append($('<img>').addClass('requests').attr('src', '/theme/cramppbo/images/tools/doc_new.png'));    
    }
  }
  
  function scrollTo(linkInfo) {
    $.fancybox.close();
    
    linkInfo = linkInfo.split('/');
    link = linkInfo[0];
    if (linkInfo.length>1) {
      linfo = linkInfo.slice(1);
    } else {
      linfo = new Array();
    }
    
    $('#collection #album').empty();
    $('#collection #browser').slideDown(400, function() {
      $(this).children('#music').scrollTo('#'+link, 800, {
        onAfter: function() { 
          $('#'+link).trigger('click', [linfo]);
        }
      });
    });    
  }
  

  $(document).ready(function() {
    // keyboard shortcuts
    $(document).bind('keydown', 'Ctrl+m', function() {
      toggleMute();
    });
    
    $(document).bind('keydown', 'Ctrl+i', function() {
      ignoreCurrent();
    });    
    
    $(document).bind('keydown', 'Ctrl+down', function() {
      setVolume(parseInt($('#volume').attr('rel'))-1);
    })

    $(document).bind('keydown', 'Ctrl+up', function() {
      setVolume(parseInt($('#volume').attr('rel'))+1);
    })
  
    $('a[href^="#!/"]').live('click', function() {
      scrollTo($(this).attr('href').substr(3));
      return false;
    });
    
    $('a[href^="#_"]').live('click', function() {
      linkInfo = $(this).attr('href').substr(3).split('/');

      switch (linkInfo.length) {
        case 2: // album
          $.ajax({
            url: '/data.php',
            dataType: 'json',
            data: 'a='+linkInfo[1]+'&all=true',
            async: false,
            success: function(d) {
              $content = $('<div></div>').addClass('album').attr('id', d[linkInfo[1]].key)
                          .append($('<img>').attr('src', d[linkInfo[1]].icon).attr('width',125).attr('height',125))
                          .append($('<div></div>').addClass('detail')
                            .append($('<h1></h1>').html(d[linkInfo[1]].artist + ": " + d[linkInfo[1]].name)));
            }
          });
          break;
        case 1: // artist
          $.ajax({
            url: '/data.php',
            dataType: 'json',
            data: 'r='+linkInfo[1]+'&all=true',
            async: false,
            success: function(d) {
              $content = $('<div></div>').addClass('album').attr('id', d[linkInfo[1]].key)
                          .append($('<img>').attr('src', d[linkInfo[1]].icon).attr('width',125).attr('height',125))
                          .append($('<div></div>').addClass('detail')
                            .append($('<h1></h1>').html(d[linkInfo[1]].artist + ": " + d[linkInfo[1]].name)));
            }
          });
          break;
      }
      display("The selected item is not in the rrrrradio collection.<br />Would you like to request that it be added?<br /><br />"+$('<div>').append($content.clone()).remove().html(), {
        yes: function() {
          $.ajax({
            url: '/controller.php',
            dataType: 'json',
            data: 'r=request&item='+$(this).parent().siblings('.album').attr('id'),
            async: false,
            success: function(d) {
              display("The selected item has been submitted for consideration", {
                ok: function() {
                  $.fancybox.close();
                }
              });            
            }
          });
        },
        no: function() {
          $.fancybox.close();
        }
      });
      
      return false;
    });
    
    $('input[title!=""]').live({
      blur: function() {
        if ($(this).val()=='') $(this).val($(this).attr('title')).addClass('empty');
      },
      focus: function() {
        if ($(this).val()==$(this).attr('title')) $(this).val('').removeClass('empty');
      }
    });  
    
    $('.like').live('click', function() { 
        $('.qtip').qtip('hide');
        $.fancybox.close();
        setmark($(this).attr('rel'), 1); 
      });
    
    $('.dislike').live('click', function() { 
        $('.qtip').qtip('hide');
        $.fancybox.close();
        setmark($(this).attr('rel'), -1); 
      })
    
    $('.approveRequest').live('click', function() {
      node = $(this);
      
      $.ajax({
        url: '/controller.php',
        dataType: 'json',
        data: 'r=approve&a='+$(this).attr('rel'),
        async: false,
        beforeSend: function() {
          $('#message .album.'+node.attr('rel')+' .button').remove();
        },
        success: function(d) {
          $('#message .album.'+node.attr('rel')).fadeOut(800, function() { $(this).remove() });    
        }
      });    
    });
    
    $('.denyRequest').live('click', function () {
      node = $(this);

      $.ajax({
        url: '/controller.php',
        dataType: 'json',
        data: 'r=deny&a='+$(this).attr('rel'),
        async: false,
        beforeSend: function() {
          $('#message .album.'+node.attr('rel')+' .button').remove();
        },        
        success: function(d) {
          $('#message .album.'+node.attr('rel')).fadeOut(800, function() { $(this).remove() });    
        }
      });
      

    })
  
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
  
    $('li.artist').live({
      click: function(event, targetInfo) {
        node = $(this);
    
        $.ajax({
          url: '/data.php',
          dataType: 'json',
          data: 'r='+$(this).attr('id'),
          async: true,
          beforeSend: function() {
            $('#collection #browser .artist.highlight').removeClass('highlight');
            node.addClass('highlight');          
            $('#collection #browser #album').empty().append($('<img>').addClass('loading').attr('src', '/theme/cramppbo/images/ajax-loader-bar.gif'));       
          },
          success: function(d) {
            displayArtistWorks(d);
            
            $('.ajax-loader').remove();
          },
          complete: function() {
            if ((targetInfo!=undefined) && (targetInfo.length>0)) {
              albumKey = targetInfo[0];
              tinfo = targetInfo.slice(1);
              
              $('#collection #browser #album').scrollTo('#'+albumKey+' .detail', 400);
              if (tinfo.length>0) {
                $('#collection #album .track.highlight').removeClass('highlight');
                $('#'+albumKey+' #'+tinfo[0]).addClass('highlight');
              }
            }
          }      
        })
      },
      dblclick: function() {
        node = $(this);
    
        $.ajax({
          url: '/data.php',
          dataType: 'json',
          data: 'r='+$(this).attr('id')+'&force=1',
  
          beforeSend: function() {
            node.append($('<div class="ajax-loader"></div>'));        
          },
          success: function(d) {
            displayArtistWorks(d);
            $('.ajax-loader').remove();
          }      
        });
      }
    });
    
    var clicks = 0;
    $('li.track').live({
      click: function() {
        node = $(this);
        clicks++;
        if (clicks == 1) {
          setTimeout(function() {
            if(clicks == 1) {
              // SINGLE CLICK
              displayTrack(node.attr('id'));             
            } else {
              // DOUBLE CLICK
              if (node.hasClass('unstreamable')) {
                displayTrack(node.attr('id'));             
              } else {
                queueTrack(node.attr('id'));
              }
            }
            clicks = 0;
          }, 500);
        }
      }      
    });
    
    $('#message .request').live('click', function() {
      queueTrack($(this).attr('rel'));
    });
      
    $.widget( "custom.catcomplete", $.ui.autocomplete, {
  		_renderMenu: function( ul, items ) {
  			var self = this,
  				currentCategory = "";
  			$.each( items, function( index, item ) {
  				if ( item.type != currentCategory ) {
  				  switch (item.type) {
  				    case 'r':
  				      type = 'Artists';
  				      break;
  				    case 'a':
  				      type = 'Albums';
  				      break;
  				    case 't':
  				      type = 'Songs';
  				      break;
  				    case '_r':
  				      type = 'More Artists...';
  				      break;
  				    case '_a':
  				      type = 'More Albums...';
  				      break;
  				  }
  					ul.append( "<li class='ui-autocomplete-category'>" + type + "</li>" );
  					currentCategory = item.type;
  				}
  				self._renderItem( ul, item );
  			});
  		},
      _renderItem: function( ul, item ) {
        $result = $( "<li></li>" ).addClass(item.type).data( "item.autocomplete", item )
    		  .append($("<a></a>").addClass('name').attr('href',(item.type.substring(0,1)=='_'?'#_/':'#!/')+item.key)
    		    .append((item.type.substr(-1)!='r') ? $('<img>').attr('src',item.icon).attr('width',64).attr('height',64) : null)
    		    .append( $("<span></span>").addClass('track main').html(item.name))		    
    		    .append( $("<span></span>").addClass('artist').html(item.artist))
    		    .append( $("<span></span>").addClass('album').html(item.album)))		    
    		  .appendTo( ul );
    		return $result;
      }		
  	});
	    
    $('#search').catcomplete({
      source: "/data.php", 
      minLength: 2,
      position: {
        my: "right top",
        at: "right bottom",
        collision: "none"
      }
    }).parent().bind('submit', function () {
      return false;
    });
  
    $('.player_mute').live('click', function() {
      toggleMute();
    });
        
    $('.catchup').live('click', function() {
      if (skip>0) {
        RdioPlayer().rdio_seek(skip+currentPosition)
        skip = -1;
      }
    });
    
    $('.export').live('click', function() {
      $content = $('<div></div>').html($(this).attr('title')+'<br /><br />')
        .append($('<form></form>').bind('submit', function() { return false; })
          .append($('<label></label>').attr('for', 'rdio_playlist').html('Playlist name'))
          .append($('<input>').attr('name', 'playlist').attr('id','rdio_playlist'))
          .append($('<input>').attr('name', 'tracks').attr('id', 'rdio_tracks').attr('type','hidden').val($(this).attr('rel')))
          );
      display($('<div>').append($content.clone()).remove().html(), {
        save: function() {
          if ($('#rdio_tracks').val()=='livequeue') {
            q = new Array();
            $.each(_QUEUE.q, function(i, v){
              q.push(v.key);
            });
            
            tracks = q.join(',');
          } else {
            tracks = $('#rdio_tracks').val();
          }
          exportToPlaylist($('#rdio_playlist').val(), tracks);
        },
        cancel: function() {
          $.fancybox.close();
        }
      })
    }).qtip({
      content: {
        text: "Export the current queue to an Rdio playlist for offline listening"
      },
      position: {
        my: 'top center',
        adjust: {
          x: -16,
          y: 10
        }          
      },
      show: {
        delay: 1000
      },
      style: {
          classes: 'ui-tooltip-dark ui-tooltip-shadow ui-tooltip-rounded'
      }
    });
    
    $('.preview').live('click', function() {
      if ($(this).hasClass('playing')) {
        stopPreview();
        $(this).removeClass('playing').html('Preview this song').prepend($('<img>'));
      } else {
        playPreview($(this).attr('rel'));
        $(this).addClass('playing').html('Stop preview').prepend($('<img>'));
      }

    });
    
    $('.requests').live('click', function() {
      $('.qtip').qtip('hide'); 
      node = $(this);   
      
      orig = node.attr('src');
      node.attr('src','/theme/cramppbo/images/ajax-loader-indicator.gif');
      
      $.ajax({
        url: '/data.php',
        dataType: 'json',
        data: 'v=requests',
        async: true,
        success: function(d) {

          $content = $('<div></div>').css('text-align','center').html('The following albums have been suggested for addition<br /><br />');
          
          for (var i in d[0]) {
            $content.append($('<div></div>').css('display','inline-block').css('margin','auto').addClass('album '+d[0][i].key).css('width','150px').css('height', '230px').attr('id', d[0][i].key)
                        .append($('<span></span>').attr('rel', d[0][i].key).addClass('approveRequest button').html('Approve'))
                        .append($('<span></span>').attr('rel', d[0][i].key).addClass('denyRequest button').html('Deny'))
                        .append($('<a></a>').attr('href',d[0][i].shortUrl).attr('target', '_blank')
                          .append($('<img>').attr('src', d[0][i].icon).attr('width',125).attr('height',125))
                          .append($('<div></div>').addClass('detail')
                            .append($('<h1></h1>').html(d[0][i].artist + "<br />" + d[0][i].name)))
                          )
                        );
            if (!d[0][i].canStream) $content.find('.detail').append($('<h2></h2>').html('Unstreamable'));

          };
          
          display($('<div>').append($content.clone()).remove().html(), {
            close: function() {
              $.fancybox.close();
            }
          });            
          
          node.attr('src',orig);
        }
      });    

    }).live('mouseover', function(event) {
      $(this).qtip({
        overwrite: false,
        content: {
          text: "Review albums have have been suggested for addition"
        },
        position: {
          my: 'top right',
          adjust: {
            x: -8,
            y: 10
          }          
        },
        show: {
          event: event.type,
          ready: true
        },
        style: {
            classes: 'ui-tooltip-dark ui-tooltip-shadow ui-tooltip-rounded'
        }
      }, event)
    }); 
  
    
    $('#volume img').click(function() {
      level = $(this).attr('rel');

      setVolume(level);
    })
  
    $('#collection .header').click(function(event) {
      if ($(event.target).attr('id')!='search') {
        $('#collection #browser').slideToggle(400, function() {
          if ($(this).is(':visible')) {
            $('#collection #search').attr('value','').fadeIn();
          } else {
            $('#collection #search').attr('value','').fadeOut();
          }
        });
      }
    });
    
    $('#welcomelink').fancybox({
      'width': 700,  
      'padding': 0
    });
    
    $('#popuplink').fancybox({
      width: 300,
      onCleanup: function() {
        $('#popup #message').css({
          width: 'auto',
          height: 'auto'
        })
        stopPreview();
      }
    })
  
    var flashvars = {
      'playbackToken': playbackToken,
      'domain': domain,
      'listener': 'RdioStream'
      };
    var flashvarsPreview = {
      'playbackToken': playbackToken,
      'domain': domain,
      'listener': 'RdioPreview'
      };      
    var params = {
      'allowScriptAccess': 'always'
    };
    var attributes = {};
    swfobject.embedSWF(api_swf, 'RdioStream', 1, 1, '9.0.0', 'expressInstall.swf', flashvars, params, attributes);
    swfobject.embedSWF(api_swf, 'RdioPreview', 1, 1, '9.0.0', 'expressInstall.swf', flashvarsPreview, params, attributes);
  
    getQueue();
    
    if (window.location.href.indexOf('#!/')>0) scrollTo(window.location.href.substring(window.location.href.indexOf('#!/')+3));
  
    setInterval("updateQueue()", refreshInterval*1000);  
  });
  
  
  
  if (window.fluid) {
    window.resizeTo(660, 770);
  }
