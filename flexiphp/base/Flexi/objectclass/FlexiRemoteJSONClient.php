<?php

/**
 * Description of FlexiRemoteJSONClient
 *
 * @author james
 */
class FlexiRemoteJSONClient extends FlexiRemoteClient {
  public function init($sRemoteKey) {
    $this->sRemoteClientName = "FlexiRemoteJSONClient v1.0";
  }

  public function _getHeaders() {
    return array(
      "Accept: text/x-json,text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
      "Content-type: text/x-json"
    );
  }
  
  public function prepareContent($mData) {
    return json_encode($mData);
  }

  public function getContent($mData) {
    return json_decode($mData);
  }
}
