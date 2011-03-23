<?php
  function authenticate() {
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
    }   
  }

  function rdioGet($args) {
    $c = new Config();
    
    $oauth = new OAuth($c->rdio_conskey, $c->rdio_conssec, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
    $oauth->setToken($_SESSION['token'],$_SESSION['secret']);
    $oauth->setAuthType(OAUTH_AUTH_TYPE_FORM);
    $oauth->fetch($c->rdio_api_url, $args, OAUTH_HTTP_METHOD_FORM);
    $json = json_decode($oauth->getLastResponse());    
    
    return $json;  
  }
?>