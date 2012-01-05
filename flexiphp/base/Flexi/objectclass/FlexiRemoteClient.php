<?php

/**
 * Description of FlexiRemoteClient
 *
 * @author james
 */
abstract class FlexiRemoteClient {
  protected $mData = array();
  public $mResult = null;
  protected $sRemoteClientName = "FlexiRemoteClient v1.2";
  protected $sRemoteKey = "";
  protected $sToken = "";
  protected $sRemoteURL = "";
  
  public function __construct($sRemoteKey) {
    $this->sRemoteKey = $sRemoteKey;
    $this->init($sRemoteKey);
  }
  
  public function setRemoteURL($sURL) {
    $this->sRemoteURL = $sURL;
  }
  
  function init($sRemoteKey) {}

  public function getToken() {
    return $this->sToken;
  }
  public function setContent($mData) {
    $this->mData = $mData;
  }

  public function getHeaders() {
    return array_merge(array(
			"User-Agent: " . $this->sRemoteClientName,
			"Accept-Language:	en-us,en;",
			"Accept-Charset:	utf-8,ISO-8859-1;q=0.7,*;q=0.7",
			"Keep-Alive:	115",
			"Connection: keep-alive",
			"Cache-Control:	max-age=0"
		), $this->_getHeaders());
  }
  
  public function doRequest($module="", $method="", $params=array()) {
    $this->mData = $params;
    if (empty($this->sRemoteURL)) throw new Exception("Remote url not set");
    $this->callRemote($this->sRemoteURL, $module, $method);
    return $this->getResult();
  }
  
  /**
   * Do call to remote url
   * @param String $asURL
   * @param String $asModule
   * @param String $asMethod
   * @return boolean: true/false
   */
  public function callRemote($asURL = "http://localhost", $asModule = "", $asMethod = "") {
    FlexiLogger::debug(__METHOD__, "Calling URL: " . $asURL);
    $bDebug = false;
    $sURL = $asURL;
    $sModule = empty($asModule) ? "default" : $asModule;
    $sMethod = empty($asMethod) ? "default" : $asMethod;
    
    //$sURL = "temp/bloomberg-stocks.html";
		$aHeader = $this->getHeaders();

		$opts = array(
			'http'=>array(
				'method'=>"POST",
				'header'=> implode("\r\n", $aHeader),
        //'content' => http_build_query(array('status' => $message)),
        'content' => $this->getRequestContent($sModule, $sMethod)
			)
		);
    
    FlexiLogger::debug(__METHOD__, "Content: " . $opts["http"]["content"]);

		$context = stream_context_create($opts);
    FlexiLogger::debug(__METHOD__, "Processing URL: " . $sURL);
    $sResult = file_get_contents($sURL, false, $context);
		if ($sResult === false || empty($sResult)) throw new Exception("Remote returned empty or false");
    try {
      $this->mResult = FlexiCryptUtil::b64Decrypt($sResult, $this->sRemoteKey);
    } catch (Exception $e) {
      throw new Exception($e->getMessage() . "<br/>\nOriginal Remote result: " . $sResult);
    }
    //echo "<hr/>";
    if ($bDebug) echo "\r\n" . __METHOD__ . ": " . $sResult . "<Br/>\r\n";
    //var_dump($this->mResult);
    if (empty($this->mResult)) {
      throw new Exception("Unknown result: " . $sResult);
    }
    
    FlexiLogger::debug(__METHOD__, "Result raw: " . $this->mResult);
    $aResult = $this->getResult();
    if (empty($aResult)) throw new Exception("Remote returned malformed result: " . $sResult);
    
    return $aResult->status;
  }

  public function getResultReturned() {
    $aResult = $this->getResult();
    return $aResult->return;
  }
  
  public function getResult() {
    return $this->getContent($this->mResult);
  }

  /**
   * get data prepared by remote call type, example: json
   * @return String
   */
  public function getRequestContent($sModule, $sMethod) {
    return $this->_prepareContent(
            array(
            "module" => $sModule,
            "method" => $sMethod,
            "token" => $this->sToken,
            "data" => $this->mData
            )
    );
  }

  abstract public function _getHeaders();
  /**
   * convert data to remote call data
   * @param Mixed $mData
   * @return String
   */

  public function _prepareContent($amData) {
    $mData = $this->prepareContent($amData);
    //echo "sending: " . $mData;
    //encrypt data before sending
    return FlexiCryptUtil::b64Encrypt($mData, $this->sRemoteKey);
  }
  
  abstract public function prepareContent($mData);
  /**
   * convert result from remote call to object
   * @param String $sData
   * @return Mixed
   */
  abstract public function getContent($sData);
  
  
  public function doGetLoginToken($sUserName, $sPassword, $sModule="FlexiRemoteServer", $sMethod="login") {
    $sURL = empty($asURL) ? $this->sRemoteURL: $asURL;
    $this->setContent(array("username" => $sUserName, "password" => $sPassword));
    $bResult = $this->callRemote($sURL, $sModule, $sMethod);
		
    if ($bResult) {
      $mResult = $this->getResultReturned();
      //echo "returned result: " ;
      if ($mResult->login_status) {
        $this->sToken = $mResult->token;
        return $this->sToken;
      } else {
        throw new Exception("Login failed");
      }
    } else {
      $oResult = $this->getResult();
			throw new Exception("Login failed: " . $sUserName . ", err: " . $oResult->msg);
		}
    return null;
  }
  
}
