<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FlexiCryptUtil
 *
 * @author james
 */
class FlexiCryptUtil {
  public static $sStreamMode = null;
  public static $oBlowfish = null;
    //put your code here
  public static function getMode() {
    if (self::$sStreamMode == null ) {
      $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MYCRYPT_MODE_ECB);
      $iv=mcrypt_create_iv($iv_size,MCRYPT_RAND);
      
      self::$sStreamMode = $iv;
    }
    return self::$sStreamMode;
  }

  public static function compress($sData) {
    return gzdeflate($aData, 1);
  }

  public static function uncompress($sData) {
    return gzinflate($sData);
  }

  public static function encrypt($sData, $asKey=null) {
    $sKey = empty($asKey) ? FlexiConfig::$sEncryptionKey : $asKey;

    $blowfish = new Crypt_Blowfish($sKey);
    return $blowfish->encrypt($sData);
    //return mcrypt_encrypt( MCRYPT_BLOWFISH, $sKey, $sData, MCRYPT_MODE_CBC, self::getMode() );
  }

  public static function decrypt($sData, $asKey=null) {
    $sKey = empty($asKey) ? FlexiConfig::$sEncryptionKey : $asKey;

    $blowfish = new Crypt_Blowfish($sKey);
    $sResult = $blowfish->decrypt($sData);

//    if (strlen($sResult) > 0) {
//      while (ord($sResult[strlen($sResult)-1]) == 0) {
//        $sResult = substr($sResult,0,-1);
//      }
//    }
    
    return $sResult;
    //return mcrypt_decrypt( MCRYPT_BLOWFISH, $sKey, $sData, MCRYPT_MODE_CBC, self::getMode() );
  }

  /**
   * Base64 URL encrypt
   * @param String $asData
   * @param String $asKey
   * @return String
   */
  public static function b64URLCompressEncrypt($asData, $asKey=null) {
    $sData = $asData;
    //var_dump($sData);
    //$sData = base64_encode($sData);
    //$sData = addslashes($sData);
    //$sData = self::compress($sData);

    //echo "compressed: " . $sData;
    //$sData = bin2hex($sData);

    //$sData = base64_encode($sData);
    //echo "compressed: " . $sData;
    $sData = self::encrypt($sData, $asKey);


    $sData = base64_encode($sData);
    //echo "enc: " . $sData;
    //echo "base64: " . $sData;
    //$sData = htmlentities($sData, ENT_COMPAT, "UTF-8");
    $sData = urlencode($sData);
    //$sData = htmlentities($sData);
    return $sData;
  }

  /**
   * Dummy url decrypt, might use in future
   * @param String $asData
   * @param String $asKey
   * @return String
   */
  public static function b64URLDecompressDecrypt($asData, $asKey=null) {
    $sData = $asData;
    
    //$sData = html_entity_decode($sData, ENT_COMPAT, "UTF-8");
    $sData = urldecode($sData);

    //var_dump($sData);
    //echo "xxxx";
    //$sData = html_entity_decode($asData);
    $sData = base64_decode($sData);
    $sData = self::decrypt($sData, $asKey);
    //var_dump($sData);
    //echo "----";
    //$sData = self::uncompress($sData);
    
    //$sData = stripslashes($sData);
    
    $sData = base64_decode($sData);

    //$sData = hex2bin($sData);
    //echo "bin: " . $sData;
    
    //$sData = base64_decode($sData);
    //
    //echo "result: ";
    //var_dump($sData);
    return $sData;
  }

  /**
   * Dummy url decrypt, might use in future
   * @param String $asData
   * @param String $asKey
   * @return String
   */
  public static function b64DecompressDecrypt($asData, $asKey=null) {
    $sData = $asData;

    //$sData = html_entity_decode($sData, ENT_COMPAT, "UTF-8");
    //$sData = urldecode($asData);
    //var_dump($sData);
    //echo "xxxx";
    $sData = urldecode($sData);
    //$sData = html_entity_decode($asData);
    $sData = base64_decode($sData);


    $sData = self::decrypt($sData, $asKey);
    var_dump($sData);
    //echo "----";
    //$sData = self::uncompress($sData);

    //$sData = stripslashes($sData);

    $sData = base64_decode($sData);

    //$sData = hex2bin($sData);
    //echo "bin: " . $sData;

    //$sData = base64_decode($sData);
    //
    //echo "result: ";
    //var_dump($sData);
    return $sData;
  }

  /**
   * Base64 URL encrypt
   * @param String $asData
   * @param String $asKey
   * @return String
   */
  public static function b64URLEncrypt($asData, $asKey=null) {
    $sData = self::b64Encrypt($asData, $asKey);
    //$sData = htmlentities($sData, ENT_COMPAT, "UTF-8");
    $sData = urlencode($sData);
    //$sData = htmlentities($sData);
    return $sData;
  }

  /**
   * Dummy url decrypt, might use in future
   * @param String $asData
   * @param String $asKey
   * @return String
   */
  public static function b64URLDecrypt($asData, $asKey=null) {
    $sData = html_entity_decode($asData, ENT_COMPAT, "UTF-8");
    //$sData = html_entity_decode($asData);
    $sData = self::b64Decrypt($sData, $asKey);
    return $sData;
  }

  public static function b64Encrypt($asData, $asKey=null) {
    $sData = $asData;
    $sData = base64_encode($sData);
    $sData = self::encrypt($sData, $asKey);
    return base64_encode($sData);
  }

  public static function b64Decrypt($asData, $asKey=null) {
    $sData = $asData;
    $sData = base64_decode($sData);
    $sData = self::decrypt($sData, $asKey);
    $sData = base64_decode($sData);

    return $sData;
  }

}