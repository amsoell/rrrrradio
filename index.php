<?php 
  include("include/header.php");
  if (strpos($_SERVER['HTTP_USER_AGENT'],"iPhone")) {
    include("include/body.touch.php");
  } else {
    include("include/body.php");
  }
  include("include/footer.php");
?>
    
