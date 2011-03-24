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

}