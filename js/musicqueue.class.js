function musicQueue() {
  this.q = [];
  this.ptr = -1;
  this.getNext = function() {
    return this.q[++this.ptr];
  }
  this.currentTrack = function() {
    return this.q[this.ptr];
  }
  this.EOF = function() {
    return ((this.ptr+1)>=this.q.length);
  };
  this.push = function(newTracks) {
    return this.q.push(newTracks);
  }
  this.init = function(tracks) {
    this.q = tracks;
    this.ptr = -1;
  }
  this.length = function() {
    return this.q.length;
  }
  this.updateQueue = function(tracks) {
    // find the track in the internal queue that matches the first track in `tracks`
    for (i=0;i<this.q.length;i++) {
      if (this.q[i].key == tracks[0].key) {
        q = [];
        
        q[0] = tracks[0];
        for (j=1;j<tracks.length;j++) {
          q[j] = tracks[j];
        }
        
        this.q = q;
        this.ptr = 0;        
    
        break;
      }
    }
  }

}