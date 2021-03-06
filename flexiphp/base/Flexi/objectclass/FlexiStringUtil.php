<?php

class FlexiStringUtil
{
  private static $iSeed = 1;

  public static function cleanName($sValue) {
    return preg_replace('/[^a-zA-Z0-9_\s]/',"", $sValue);
  }
  
  public static function isCleanName($sValue) {
    return preg_match("/[^a-zA-Z0-9_\s]/", $sValue) > 0 ? false: true;
  }

  public static function cleanAlphaNumeric($sValue) {
    return preg_replace('/[^a-zA-Z0-9]/',"", $sValue);
  }

  public static function isAlphaNumeric($sValue) {
    return preg_match('/[^a-zA-Z0-9]/', $sValue) > 0 ? false: true;
  }

	public static function isValidEmail($sEmail) {
    return preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', $sEmail) > 0 ? true: false;
  }
	public static function getDDMMYYFromISODate($sValue, $sSep = "-")
	{
		if (empty($sValue)) { return null; }
		if ($sValue == "0000-00-00") { return null; }
		
		$aDate = explode("-", $sValue);
		if (count($aDate) >= 3)
		{
			return $aDate[2] . "-" . $aDate[1] . "-" . $aDate[0];
		}
		
		return null;
	}
	
	public static function getISODateFromDMY($sValue)
	{
		return self::getISODateFromDDMMYY($sValue);
	}
	
	public static function getISODateFromDDMMYYYY($sValue)
	{
		return self::getISODateFromDDMMYY($sValue);
	}
	
	public static function getISODateFromDDMMYY($sValue)
	{
		if (empty($sValue)) { return null; }
		
		if (strpos($sValue, "-") !==false)
		{
			$sSep = "-";
		}
		else if (strpos($sValue, "/") !==false)
		{
			$sSep = "/";
		}
		else if (strpos($sValue, ".") !==false)
		{
			$sSep = ".";
		}
		
		$aDate = explode($sSep, $sValue);
		if (count($aDate) >= 3)
		{
			return $aDate[2] . "-" . $aDate[1] . "-" . $aDate[0];
		}
		
		return null;
	}

  public static function createRandomPassword($charcount = 6) {
    static $aList = array();
    $sPass = trim(self::_createRandomPassword($charcount));
    while(array_search($sPass, $aList) !== false) {
      $sPass = trim(self::_createRandomPassword($charcount));
    }
    $aList[] = $sPass;
    return $sPass;
  }
	
	public static function createRandomAlphaNumeric($charcount = 6) {
    static $aList = array();
    $sPass = trim(self::_createRandomAlphaNumeric($charcount));
    while(array_search($sPass, $aList) !== false) {
      $sPass = trim(self::_createRandomAlphaNumeric($charcount));
    }
    $aList[] = $sPass;
    return $sPass;
  }
	
	public static function _createRandomAlphaNumeric($charcount = 6) {
		$chars = "abcdefghijkmnopqrstuvwxyz023456789";
		return self::createRandomChars($charcount, $chars);
	}

	public static function _createRandomPassword($charcount = 6)
	{
		$chars = "abcdefghijkmnopqrstuvwxyz023456789";
		return self::createRandomChars($charcount, $chars);
	}

  public static function createRandomChars($charcount, $chars) {
    
		$i = 0;
		$pass = '' ;
    if (strlen($chars) < 1) { return ""; }
		while ($i < $charcount) {
      self::$iSeed += 1;
      mt_srand((double)microtime()*1000000 + self::$iSeed);
      $num = mt_rand(0, strlen($chars)-1);
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}
		return $pass;
  }
	
	public static function attributesToString($aAttributes, $asDelimiter="\"")
	{
		if (is_null($aAttributes)) { return ""; }
		
		$aResult = array();
		foreach ($aAttributes as $sKey => $sValue) {
			$mValue = FlexiParser::parseHTMLInputValue($sValue, $asDelimiter);
			$aResult[] = $sKey . "=" . $asDelimiter . $mValue . $asDelimiter;
		}
		return implode(" ", $aResult);
	}

  public static function getPathExtension($sPath) {
    $path_info = pathinfo($sPath);
    return $path_info['extension'];
  }
  /**
   * Replace [+var+] with placeholders values on tpl
   * @param array $placeholders
   * @param String $tpl
   * @return String
   */
  public static function parseChunk($placeholders, $tpl) {
		$keys = array();
		$values = array();
    if ($placeholders == null) { throw new FlexiException("Place holder cannot be null", ERROR_EOF); }
		foreach ($placeholders as $key=>$value) {
			$keys[] = '[+'.$key.'+]';
			$values[] = $value;

      $keys[] = '[[+'.$key.']]';
			$values[] = $value;
		}
		return str_replace($keys,$values,$tpl);
  }

  /**
   * Strip tags with only wanted tags
   *  and also avoid harmful attributes like mouseover
   *  example: stripTagsAttributes($string,'<strong><em><a>','href,rel');
   * @param String $string
   * @param String $allowtags array / string
   * @param Mixed $allowattributes array / string
   * @return String
   * @refer http://www.php.net/manual/en/function.strip-tags.php#91498
   */
  public static function stripTagsAttributes($string,$allowtags=NULL,$allowattributes=NULL){
    $string = strip_tags($string,$allowtags);
    if (!is_null($allowattributes)) {
        if(!is_array($allowattributes))
            $allowattributes = explode(",",$allowattributes);
        if(is_array($allowattributes))
            $allowattributes = implode(")(?<!",$allowattributes);
        if (strlen($allowattributes) > 0)
            $allowattributes = "(?<!".$allowattributes.")";
        $string = preg_replace_callback("/<[^>]*>/i",create_function(
            '$matches',
            'return preg_replace("/ [^ =]*'.$allowattributes.'=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[0]);'
        ),$string);
    }
    return $string;
  }


}
