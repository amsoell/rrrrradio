function musicQueue() {
  this.q = [];
  this.ptr = -1;
  this.locked = false;
  
  this.lock = function() {
    this.locked = true;
  }
  
  this.unlock = function() {
    this.locked = false;
  }
  
  this.getNext = function() {
    this.lock();
    this.ptr++;
    for (i=0;i<this.ptr;i++) {
      if (this.q[i].prune==1) {
        this.q.shift();
        this.ptr--;
        i--;
      }
    }
    this.unlock();    
    return this.currentTrack();
  }
  this.currentTrack = function() {
    return this.q[this.ptr];
  }

  this.push = function(newTracks) {
    return this.q.push(newTracks);
  }
  this.init = function(tracks) {
    this.q = tracks;
  }
  this.length = function() {
    return this.q.length;
  }
  this.updateQueue = function(tracks) {   
    if (!this.locked) {
      offset = 0;
      
      for (i=0;i<this.q.length;i++) {
        if (this.q[i].key==tracks[0].key) {
          offset = i;
          break;
        }
      }
      
      for (i=0; i<offset; i++) {
        this.q[i].prune = 1;
      }
      
      for (i=0; i<tracks.length; i++) {
        this.q[i+offset] = tracks[i];
      }
    }
  }

}