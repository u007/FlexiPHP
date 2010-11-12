<?php

class FlexiDiffUtil {

  /**
   * Get list of files which differ or is new from "frompath"
   * @author James
   * @param String $sFromPath
   * @param String $sToPath
   * @param array $aExclude, default: empty array
   * @param boolean $bRecursive, default: true
   * @return array("path", "type"=>"dir"/"file", "status" => "new"/"modified/conflict/include")
   */
  public static function getDiffList($sFromPath, $sToPath, $aExclude = array(), $aInclude = array(), $bRecursive=true, $bCompare = true) {
    $bDebug = false;
    $aResult = array();
    $sFromPath  .= substr($sFromPath, -1,1) == "/" ? "" : "/";
    $sToPath    .= substr($sToPath, -1,1) == "/" ? "" : "/";
    
    $aList = FlexiFileUtil::getDirectoryList($sFromPath, $aExclude, $aInclude);
    foreach ($aList as $sFile) {
      $sFromFile = $sFromPath.$sFile; //copy from
      $sToFile = $sToPath.$sFile; //copy to
      $sFromType = is_dir($sFromPath.$sFile) ? "dir" : "file";
      if($bDebug) echo "src: " . $sFromFile . "\r\n<br/>";

      $bInclude = !$bCompare;

      if ($sFile != "." && $sFile != "..") {

        if (! $bInclude) {
          foreach($aInclude as $sInclude) {
            //if ($bDebug) echo "include: " . $sInclude . "\r\n<br/>";
            list($sIncludeName, $sIncludeName2) = explode("/", $sInclude);
            if ($bDebug) echo "checking: " . $sFile . ", include: " . $sIncludeName . "\r\n<br/>";
            if ($sFile == $sIncludeName && empty($sIncludeName2)) {
              $bInclude = true;
              break;
            }
          } //foreach include
        }

        if ($bInclude) {
          //is in include list
          $aResult[] = array("path" => $sFromFile, "type" => $sFromType, "status" => "include");

        } else {
          if ($bCompare && file_exists($sToFile)) {
            //check if file exists
            if (is_file($sFromFile) && is_file($sToFile)) {
              //both is file, time to checksum
              $sFromMD5 = md5_file($sFromFile);
              $sToMD5 = md5_file($sToFile);

              if ($sFromMD5 != $sToMD5) {
                $aResult[] = array("path" => $sFromFile, "type" => $sFromType, "status" => "modified");
              } // else is same, ignoring
            } // if both is file, compare md5
            else if (is_dir($sFromFile) && is_file($sToFile) || is_file($sFromFile) && is_dir($sToFile)) {
              $aResult[] = array("path" => $sFromFile, "type" => $sFromType, "status" => "conflict");
            } //if conflict dir / file type
            else if(is_dir($sFromFile) && is_dir($sToFile)) {
              //both is directory, ignoring as its the same
            }
            //if to path exissts
          } else {
            $aResult[] = array("path" => $sFromFile, "type" => $sFromType, "status" => $bCompare ? "new" : "include");
          }
        } //if not in include list
        
        if ($bRecursive && is_dir($sFromFile)) {
          //if($bDebug) echo "Looping into dir: " . $sFromFile . "\r\n<br/>";
          $aChildExclude = array();
          foreach($aExclude as $sExclude) {
            $aExcludePath = explode("/", $sExclude);
            if (count($aExcludePath) > 1) {
              array_shift($aExcludePath);
              $aChildExclude[] = implode("/", $aExcludePath);
            }//else, ignore
          }

          $aChildInclude = array();
          foreach($aInclude as $sInclude) {
            $aIncludePath = explode("/", $sInclude);
            if (count($aIncludePath) > 1) {
              array_shift($aIncludePath);
              $aChildInclude[] = implode("/", $aIncludePath);
            }//else, ignore
          }

          if (count($aChildInclude) > 1) {
            if ($bDebug) echo "Include to child: " . print_r($aChildInclude,true) . "\r\n<br/>";
          }

          $aResult = array_merge($aResult, self::getDiffList($sFromFile, $sToFile, $aChildExclude, $aChildInclude, true, !$bInclude));
        }
      }
      
    } //foreach path

    return $aResult;
  }

}

?>
