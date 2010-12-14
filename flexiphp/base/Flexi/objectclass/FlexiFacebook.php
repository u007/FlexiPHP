<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/lib/fbsdk/facebook.php");

class FlexiFacebook {
  public static $instance = null;
  public static $apiId = null;
  public static $secret = null;
  
  public $facebook = null;
  public $session = null;
  public $uid = null;
  public $me = null;
  public $user = null;
  
  private function __construct($sAppId, $sSecret) {
    $this->facebook = new Facebook(array(
      'appId'  => $sAppId,
      'secret' => $sSecret,
      'cookie' => true,
    ));
  }

  public static function getAPI() {
    return self::getInstance()->getFacebookApi();
  }

  public function getFacebookApi() {
    return $this->facebook;
  }

  public static function getIsActive() {
    $oInstance = self::getInstance();
    $oInstance->startSession();
    if (is_null($oInstance->session)) { return false; }
    return true;
  }

  public function startSession() {
    if (is_null($this->session)) {
      FlexiLogger::debug(__METHOD__, "starting session");
      $this->session = $this->facebook->getSession();
      FlexiLogger::debug(__METHOD__, serialize($this->session));
    }
    return $this->session;
  }

  public function getUserID() {
    FlexiLogger::info(__METHOD__, "");
    if (empty($this->uid)) {
      $this->startSession();
      try {
        $this->uid = $this->facebook->getUser();
      } catch (FacebookApiException $e) {
        FlexiLogger::error(__METHOD__, ": failed");
      }
    }
    return $this->uid;
  }

  public function getMe() {
    if (is_null($this->me)) {
      $this->startSession();
      try {
        $this->me = $this->facebook->api('/me');
        //var_dump($this->me);
      } catch (FacebookApiException $e) {
        FlexiLogger::error(__METHOD__, "failed");
      }
    }
    return $this->me;
  }
  public function getUser() {
    if (is_null($this->user)) {
      $this->user = $this->getMe();
    }
    return $this->user;
  }
  
  public function getUserInfo($uid= null) {
    static $aUser = array();
    if (empty($uid)) { $uid = $this->getUserID(); }
    
    if (!isset($aUser[$uid.""]) || empty($aUser[$uid.""])) {
      $this->startSession();
      $fql = 'SELECT uid FROM user WHERE uid IN ('.$uid.')';
      $result = $this->facebook->api_client->fql_query($fql);
      $user = array();
      if (is_array($result) && count($result)) {
        $user = $result[0];
      }
      $aUser[$uid.""] =  $user;
    }
    return $aUser[$uid.""];
  }

  public function getFriendList() {
    $user = $this->getUser();
    if (is_null($user)) { return array(); }
    
    $fql = 'SELECT uid FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1='.$user.')';
    $_friends = $this->facebook->api_client->fql_query($fql);
    $friends = array();
    if (is_array($_friends) && count($_friends)) {
      foreach ($_friends as $friend) {
        $friends[] = $friend['uid'];
      }
    }
    $friends = implode(',', $friends);
  }

  public function getMemberFriends() {
    $fql = 'SELECT uid FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1='.$user.') AND is_app_user = 1';
    $_friends = $this->facebook->api_client->fql_query($fql);

    // Extract the user ID's returned in the FQL request into a new array.
    $friends = array();
    if (is_array($_friends) && count($_friends)) {
      foreach ($_friends as $friend) {
        $friends[] = $friend['uid'];
      }
    }
    // Convert the array of friends into a comma-delimeted string.
    return $friends;
  }

  


  public function getLogoutURL() {
    $me = $this->getMe();
    return $this->facebook->getLogoutUrl();
  }
  
  public function getLoginURL($params=array()) {
    $me = $this->getMe();
    return $this->facebook->getLoginUrl($params);
  }

  public static function setup($sAppId, $sSecret) {
    self::$apiId = $sAppId;
    self::$secret = $sSecret;
  }

  public static function getInstance() {
    if (is_null(self::$instance)) {
      self::$instance = new FlexiFacebook(self::$apiId, self::$secret);
    }
    return self::$instance;
  }
  
}

?>
