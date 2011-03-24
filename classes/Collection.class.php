<?php
  class Collection {
    function getRandomTrack() {
      $db = new Db();

      $rs = $db->query("SELECT `key` FROM track WHERE rnd>RAND() AND requested=1 ORDER BY rnd LIMIT 1");
      if ($rec = mysql_fetch_array($rs)) {
        $t = new Track($rec['key']);
        return $t;
      } else {
        return false;
      }
    }
  }
?>