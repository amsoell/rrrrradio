function musicQueue() {
  this.q = [];
  this.ptr = 0;
  this.getNext = function() {
    return this.q[this.ptr++];
  }
  this.EOF = function() {
    return (this.ptr>=this.q.length);
  };
  this.push = function(newTracks) {
    return this.q.push(newTracks);
  }
  this.init = function(tracks) {
    this.q = tracks;
    this.ptr = 0;
  }
  this.updateQueue = function(tracks, currentlyPlaying) {
    // at the moment, this function assumes that the first track in the `tracks` parameter is the one currently playing
    // only works properly when triggered on a track change event -- i think
    
    // currentlyPlaying parameter indicates the song that is *actually* playing right now, whether or not it *should* be 
    // based on this object's queue. Need to figure away to correct when these values are different.
    // - We don't want to just skip ahead at the beginning of every track, because that could mean missing the first 2 seconds
    //   of every song
    // - Maybe set a threshold for skipping ahead. 10-15 seconds?
    
    // get key for last track in existing `q`
    lastTrackKey = this.q[this.q.length-1].key;
  
    // replace local `q` with tracks
    this.q = tracks;
    
    // set internal `ptr` to the position of the last track queued
    this.ptr = -1;
    
    for (i=this.q.length-1;(i>=0)&&(this.ptr==-1);i--) {

      if (this.q[i].key==lastTrackKey) {
        this.ptr = i+1;
      }
    }

    // at this point local `q` is set to the tracks array passed in and `ptr` points to the last track queued up.
    // when control is passed back to calling code, would most likely expect loadQueue to be called to 
    // get the remaining tracks added in to the Rdio player
  }

}