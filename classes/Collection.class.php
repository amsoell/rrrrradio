<?php
  class Collection {
    function getRandomTrack($includeQueued=false) {
      $db = new Db();
      $q = new Queue();
      
      if (!$includeQueued) {
        // get currently queued tracks to exclude from selection
        $queuetracks = array();
        foreach ($q->getQueue() as $track) {
          $queuetracks[] = $track->key;
        }
      } else {
        $queuetracks = array("");
      }
      
      $rs = $db->query("SELECT `key` FROM track WHERE rnd>RAND() AND requested=1 AND `key` NOT IN ('".implode(",''", $queuetracks)."') ORDER BY rnd LIMIT 1");
      if ($rec = mysql_fetch_array($rs)) {
        $t = new Track($rec['key']);
        return $t;
      } else {
        return false;
      }
    }
  }
?>