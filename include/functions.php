<?php
  function authenticate() {
    $db = new Db();
    $c = new Config();  
  
    if(!isset($_GET['oauth_token']) && $_SESSION['state']==1) $_SESSION['state'] = 0;
  
    $oauth = new OAuth($c->rdio_conskey,$c->rdio_conssec,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
    $oauth->enableDebug();
    if(!isset($_GET['oauth_token']) && !$_SESSION['state']) {
      $request_token_info = $oauth->getRequestToken($c->rdio_req_url);
      $_SESSION['secret'] = $request_token_info['oauth_token_secret'];
      $_SESSION['state'] = 1;
      header('Location: '.$c->rdio_auth_url.'?oauth_token='.$request_token_info['oauth_token'].'&oauth_callback='.urlencode($c->rdio_callback_url));
      exit;
    } else if($_SESSION['state']==1) {
      $oauth->setToken($_GET['oauth_token'],$_SESSION['secret']);
      $access_token_info = $oauth->getAccessToken($c->rdio_acc_url);
      $_SESSION['state'] = 2;
      $_SESSION['token'] = $access_token_info['oauth_token'];
      $_SESSION['secret'] = $access_token_info['oauth_token_secret'];
      
      $args = array("method" => "currentUser", "extras" => "username");      
      $oauth = new OAuth($c->rdio_conskey, $c->rdio_conssec, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
      $oauth->setToken($access_token_info['oauth_token'],$access_token_info['oauth_token_secret']);
      $oauth->setAuthType(OAUTH_AUTH_TYPE_FORM);
      $oauth->fetch($c->rdio_api_url, $args, OAUTH_HTTP_METHOD_FORM);
      api_log(null, $args, $oauth->getLastResponse());            
      $json = json_decode($oauth->getLastResponse());    
      $u = $json->result;
      
      $db->query("REPLACE INTO user (`key`, state, token, secret, lastseen) VALUES ('".$u->key."', 2, '".addslashes($access_token_info['oauth_token'])."', '".addslashes($access_token_info['oauth_token_secret'])."', UNIX_TIMESTAMP(NOW()))");
      $_SESSION['user'] = new User($json->result->key);
    }   
  }

  function rdioGet($args) {
    $c = new Config();  
    $db = new Db();

    $rs = $db->query("SELECT `return` FROM api_usage WHERE params='".addslashes(json_encode($args))."' AND executed>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR)) ORDER BY executed DESC LIMIT 1");
    if (($rec = mysql_fetch_array($rs)) && (!is_null($rec['return']))) {
      $json = json_decode($rec['return']);
    } else {
      $oauth = new OAuth($c->rdio_conskey, $c->rdio_conssec, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
      $oauth->setToken($_SESSION['token'],$_SESSION['secret']);
      $oauth->setAuthType(OAUTH_AUTH_TYPE_FORM);
      try {
        $oauth->fetch($c->rdio_api_url, $args, OAUTH_HTTP_METHOD_FORM);
        api_log($_SESSION['user']->key, $args, $oauth->getLastResponse());    
        $json = json_decode($oauth->getLastResponse());    
      } catch (Exception $e) {
        $json = json_decode(json_encode(array()));
      }
    }
    
    return $json;  
  }
  
  function api_log($user, $params, $return) {
    $db = new Db();

    $db->query("INSERT INTO api_usage (user, executed, params, `return`) VALUES ('".addslashes($user)."', UNIX_TIMESTAMP(NOW()), '".addslashes(json_encode($params))."', '".addslashes($return)."')");
  }
?>
