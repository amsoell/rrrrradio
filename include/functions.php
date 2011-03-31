<?php
  function authenticate() {
    $c = new Config();
    $rdio = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
    
    $op = $_GET["op"];
    if($op == "login") {
      $callback_url = $c->rdio_callback_url . '?op=login-callback';
      $auth_url = $rdio->begin_authentication($callback_url);
      header("Location: ".$auth_url);
    } else if($op == "login-callback") {
      $rdio->complete_authentication($_GET["oauth_verifier"]);
      header("Location: ".$c->rdio_callback_url);
    } else if($op == "logout") {
      $rdio->logOut();
      header("Location: ".$c->rdio_callback_url);
    }  
  }

  function lastfmGet($args) {
    $db = new Db();
    $c = new Config();
    
    $rs = $db->query("SELECT `return` FROM api_usage WHERE api='lastfm' AND params='".addslashes(json_encode($args))."' AND executed>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR)) ORDER BY executed DESC LIMIT 1");
    if (($rec = mysql_fetch_array($rs)) && (!is_null($rec['return']))) {
      $output = $rec['return'];
    } else {
      $qs = "?";
      foreach ($args as $key=>$val) {
        $qs .= $key."=".$val."&";
      }
      $qs .= "api_key=".$c->lastfm_conskey;
      
      $ch = curl_init($c->lastfm_api_url.$qs);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);       
      curl_close($ch);
      
      api_log_lastfm($_SESSION['user']->key, $args, $output);
    }    
    
    return new SimpleXMLElement($output, LIBXML_NOCDATA);
  }
  
  function api_log($user, $params, $return) {
    $db = new Db();

    $db->query("INSERT INTO api_usage (user, api, executed, params, `return`) VALUES ('".addslashes($user)."', 'rdio', UNIX_TIMESTAMP(NOW()), '".addslashes(json_encode($params))."', '".addslashes($return)."')");
  }

  function api_log_lastfm($user, $params, $return) {
    $db = new Db();

    $db->query("INSERT INTO api_usage (user, api, executed, params, `return`) VALUES ('".addslashes($user)."', 'lastfm', UNIX_TIMESTAMP(NOW()), '".addslashes(json_encode($params))."', '".addslashes($return)."')");
  }

?>
