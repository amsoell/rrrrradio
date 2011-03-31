<?php
// Built on PECL OAuth: http://pecl.php.net/package/oauth

/*
Copyright (c) 2010-2011 Rdio Inc

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

define('RDIO_API_ENDPOINT', 'http://api.rdio.com/1/');
define('RDIO_REQUEST_TOKEN', 'http://api.rdio.com/oauth/request_token');
define('RDIO_ACCESS_TOKEN', 'http://api.rdio.com/oauth/access_token');

class Rdio {
  private $key;
  private $secret;

  function __construct($key, $secret) {
    $this->key = $key;
    $this->secret = $secret;
  }
  
  private function _getOAuth() {
    $oauth = new OAuth($this->key, $this->secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_FORM);
    if (isset($_SESSION['access_key']) && isset($_SESSION['access_secret'])) {
      $oauth->setToken($_SESSION['access_key'], $_SESSION['access_secret']);
    } else if (isset($_SESSION['request_key']) && isset($_SESSION['request_secret'])) {
      $oauth->setToken($_SESSION['request_key'], $_SESSION['request_secret']);
    }
    return $oauth;
  }
  
  public function logOut() {
    unset($_SESSION['request_key']);
    unset($_SESSION['request_secret']);
    unset($_SESSION['access_key']);
    unset($_SESSION['access_secret']);
  }
  
  public function loggedIn() {
    return (isset($_SESSION['access_key']) && isset($_SESSION['access_secret']));
  }

  public function __call($method, $arguments) {
    $db = new Db();
    
    // build the request
    if (count($arguments) > 0) {
      $params = $arguments[0];
    } else {
      $params = array();
    }
    $params['method'] = $method;
    
    // check API cache
    $rs = $db->query("SELECT `return` FROM api_usage WHERE api='rdio' AND params='".addslashes(json_encode($params))."' AND executed>=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR)) ORDER BY executed DESC LIMIT 1");
    if ( !array_key_exists('force', $arguments) && ($rec = mysql_fetch_array($rs)) && (!is_null($rec['return'])) ) {
      $json = json_decode($rec['return']);
    } else {    
      // make the request
      $oauth = $this->_getOAuth();

      $oauth->fetch(RDIO_API_ENDPOINT, $params, OAUTH_HTTP_METHOD_POST);
      $this->log($params, $oauth->getLastResponse());
      $json = json_decode($oauth->getLastResponse(), FALSE);
    }
    
    // parse the result
    return $json;    
  }
  
  public function log($arguments, $return) {
      $db = new Db();
  
      $db->query("INSERT INTO api_usage (user, api, executed, params, `return`) VALUES ('".addslashes($user)."', 'rdio', UNIX_TIMESTAMP(NOW()), '".addslashes(json_encode($arguments))."', '".addslashes($return)."')");
  }


  public function begin_authentication($callback) {
    // reset previous auth state
    $this->logOut();
    
    $oauth = $this->_getOAuth();
    $pieces = $oauth->getRequestToken(RDIO_REQUEST_TOKEN, $callback);
    
    // save the request token
    $_SESSION['request_key'] = $pieces['oauth_token'];
    $_SESSION['request_secret'] = $pieces['oauth_token_secret'];

    // build the authentication URL
    return $pieces['login_url'] . '?oauth_token=' . $pieces['oauth_token'];
  }

  public function complete_authentication($verifier) {
    $oauth = $this->_getOAuth();
    $pieces = $oauth->getAccessToken(RDIO_ACCESS_TOKEN, '', $verifier);
    
    // save the access token
    $_SESSION['access_key'] = $pieces['oauth_token'];
    $_SESSION['access_secret'] = $pieces['oauth_token_secret'];
    
    // clear the request token
    unset($_SESSION['request_key']);
    unset($_SESSION['request_secret']);
  }

}

