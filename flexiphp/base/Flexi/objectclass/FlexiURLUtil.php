<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class FlexiURLUtil {

  /**
   * Digest url string into respective info
   * @param String $sURL
   * @return array();
   */
  public static function parseURL($sURL) {
    $aResult = parse_url($sURL);
    $aResult["aQuery"] = array();
    //var_dump($aResult["query"]);
    if (! empty($aResult["query"])) {
       parse_str($aResult["query"], $aResult["aQuery"]);
    }

    //var_dump($aResult);
    return $aResult;
  }

  /**
   * Get Query string from array
   * @param array $aQuery [key=>value,...]
   * @return String x=1&y=2...
   */
  public static function getQueryStringFromArray($aQuery) {

    foreach($aQuery as $sKey => $sValue)
    {
      $sKey = str_replace("?", "", $sKey);
      $sQuery .= empty($sQuery) ? "" : "&";
      $sQuery .= urlencode($sKey) . "=" . urlencode($sValue);
    }
    return $sQuery;
  }

}