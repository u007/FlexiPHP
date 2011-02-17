<?php

class FlexiTranslator {
  protected $sKey = "";
  protected $aLang = array();
  protected $sURL = "https://ajax.googleapis.com/ajax/services/language/translate?v=1.0";

  public function setKey($sKey) {
    $this->sKey = $sKey;
  }

  public function getText($sTitle, $sTargetLang="en", $sSourceLang="en") {
    if (!isset($this->aLang[$sSourceLang])) {
      $this->aLang[$sSourceLang] = array();
    }

    if (!isset($this->aLang[$sSourceLang][$sTargetLang])) {
      $this->aLang[$sSourceLang][$sTargetLang] = array();
    }

    if (isset($this->aLang[$sSourceLang][$sTargetLang][$sTitle])) {
      return $this->aLang[$sSourceLang][$sTargetLang][$sTitle];
    }

    $sURL = $this->sURL;
    $sURL .= "&q=" . urlencode($sTitle);
    $sURL .= "&langpair=" . urlencode($sSourceLang) . "%7C" . urlencode($sTargetLang);
    $sURL .= "&key=" . $this->sKey;

    /*
    echo "url: " .$sURL;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_REFERER, FlexiConfig::$sBaseURL);
    $body = curl_exec($ch);
    curl_close($ch);
    // now, process the JSON string
    echo "body(" . $body . ")";
    */
    $body = file_get_contents($sURL);
    $json = json_decode($body);
    //echo "body2(" . $body2 . ")";
    //die(var_dump($json));
    // now have some fun with the results...
    if (empty($body)) {
      throw new Exception("Google translate error: " . $body);
    }
    if (isset($json->error)) {
      throw new Exception("Google translate error: " . print_r($json->error,true));
    }

    $sReturn = $json->responseData->translatedText;
    $this->aLang[$sSourceLang][$sTargetLang][$sTitle] = $sReturn;
    return $sReturn;
  }
}