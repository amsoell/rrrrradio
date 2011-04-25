<?php
  function authenticate($cb=null) {
    $c = new Config();
    
    if (!is_null($cb)) $c->rdio_callback_url = $cb;
    $rdio = new Rdio(RDIO_CONSKEY, RDIO_CONSSEC);
    $db = new Db();
    
    if (isset($_SESSION['access_key']) && isset($_SESSION['access_secret'])) {
      $db->query("UPDATE user SET lastseen=UNIX_TIMESTAMP(NOW()) WHERE `key`='".addslashes($_SESSION['user']->key)."'");    
      return true;
    } 
    elseif (isset($_COOKIE['rrrrr_userkey']) && isset($_COOKIE['rrrrr_token'])) {
      $rs = $db->query("SELECT `key`, token, secret, curator FROM user WHERE `key`='".$_COOKIE['rrrrr_userkey']."' AND token='".addslashes($_COOKIE['rrrrr_token'])."' LIMIT 1");
      
      if ($rec = mysql_fetch_array($rs)) {
        $u = $rdio->get(array("keys"=>$rec['key'], "extras"=>"username"));
        $_SESSION['user'] = $u->result->$rec['key'];
        $_SESSION['user']->isCurator = ($rec['curator']==1?true:false);
        $_SESSION['access_key'] = $rec['token'];
        $_SESSION['access_secret'] = $rec['secret'];
      }
    }
    
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
    
    if (isset($_SESSION['user']) && property_exists($_SESSION['user'], "key")) {
      setcookie("rrrrr_userkey", $_SESSION['user']->key, time()+60*60*24*30);
      setcookie("rrrrr_token", $_SESSION['access_key'], time()+60*60*24*30);
      $db->query("REPLACE INTO user (`key`, state, token, secret, lastseen, curator) VALUES ('".addslashes($_SESSION['user']->key)."', 2, '".addslashes($_SESSION['access_key'])."', '".addslashes($_SESSION['access_secret'])."', UNIX_TIMESTAMP(NOW()), ".($_SESSION['user']->isCurator?1:0).")");
    }    
  }

  function lastfmGet($args) {
    $db = new Db();
    $c = new Config();
    
/*
    $rs = $db->query("SELECT `return` FROM api_usage WHERE api='lastfm' AND params='".addslashes(json_encode($args))."' AND executed>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR)) ORDER BY executed DESC LIMIT 1");
    if (($rec = mysql_fetch_array($rs)) && (!is_null($rec['return']))) {
      $output = $rec['return'];
    } else {
*/
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
//    }    
    
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
  
  function debug($m) {
    if (defined('STDIN') && isset($_REQUEST['debug'])) {
      print microtime(true)."\n";
      print_r($m);
      print "\n\n";

    }
  }

  function parseArgs($argv){
      array_shift($argv);
      $out = array();
      foreach ($argv as $arg){
          if (substr($arg,0,2) == '--'){
              $eqPos = strpos($arg,'=');
              if ($eqPos === false){
                  $key = substr($arg,2);
                  $out[$key] = isset($out[$key]) ? $out[$key] : true;
              } else {
                  $key = substr($arg,2,$eqPos-2);
                  $out[$key] = substr($arg,$eqPos+1);
              }
          } else if (substr($arg,0,1) == '-'){
              if (substr($arg,2,1) == '='){
                  $key = substr($arg,1,1);
                  $out[$key] = substr($arg,3);
              } else {
                  $chars = str_split(substr($arg,1));
                  foreach ($chars as $char){
                      $key = $char;
                      $out[$key] = isset($out[$key]) ? $out[$key] : true;
                  }
              }
          } else {
              $out[] = $arg;
          }
      }
      return $out;
  }
  
  if(defined('STDIN')) $_REQUEST = parseArgs($argv);  
?>
