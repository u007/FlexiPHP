<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FlexiImageUtil
 *
 * @author james
 */
class FlexiImageUtil {
  /**
   * Resize image to max width or height
   * @param int $width
   * @param int $height
   * @param String $sPath
   * @return <type>
   */
  public static function imageResize($aiWidth, $aiHeight, $sPath, $sNewPath = null) {
    if (empty($aiWidth) && empty($aiHeight)) { return; }
    list($iOriWidth, $iOriHeight, $iType) = getimagesize($sPath);

    if (!empty($aiWidth) && !empty($aiHeight)) {
      if ($iOriWidth > $iOriHeight) {
        $percentage = ($aiWidth / $iOriWidth);
      } else {
        $percentage = ($aiHeight / $iOriHeight);
      }
    } else if(!empty($aiWidth)) {
      $percentage = ($aiWidth / $iOriWidth);
    } else if(!empty($aiHeight)) {
      $percentage = ($aiHeight / $iOriHeight);
    }
    
    //gets the new value and applies the percentage, then rounds the value
    $width = round($iOriWidth * $percentage);
    $height = round($iOriHeight * $percentage);
    switch ($iType) {
      case 1:   //   gif -> jpg
        $image = imagecreatefromgif($sPath);
        break;
      case 2:   //   jpeg -> jpg
        $image = imagecreatefromjpeg($sPath);
        break;
      case 3: //   png -> jpg
        $image = imagecreatefrompng($sPath);
        break;
      default:
        throw new FlexiException("Unknown image type: " . $iType, ERROR_DATATYPE);
    }
    FlexiLogger::info(__METHOD__, "Resize from: " . $iOriWidth . "x" . $iOriHeight . " to " . $width . "x" . $height);
    $image_p = imagecreatetruecolor($width, $height);
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $iOriWidth, $iOriHeight);

    $sWritePath = empty($sNewPath) ? $sPath : $sNewPath;
    switch ($iType) {
      case 1:   //   gif -> jpg
        $image = imagegif($image_p, $sWritePath);
        break;
      case 2:   //   jpeg -> jpg
        $image = imagejpeg($image_p, $sWritePath);
        break;
      case 3: //   png -> jpg
        $image = imagepng($image_p, $sWritePath);
        break;
      default:
        throw new FlexiException("Unknown image type: " . $iType, ERROR_DATATYPE);
    }
    return array($width, $height);
  }
}
?>
