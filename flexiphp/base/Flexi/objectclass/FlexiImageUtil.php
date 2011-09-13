<?php
/**
 * Description of FlexiImageUtil
 *
 * @author james
 */
class FlexiImageUtil {
  
  public static function imagePreview($aiWidth, $aiHeight, $sPath, $sNewPath = null) {
    $aiWidth = (int)$aiWidth;
    $aiHeight = (int)$aiHeight;
    
    if (! file_exists($sPath)) {
      throw new Exception("File not found: " . $sPath);
    }
    
    $sNewPath = empty($sNewPath) ? $sPath . "_" . md5_file($sPath). "_" . (empty($aiWidth) ? "": "w".$aiWidth) . 
        (empty($aiHeight) ? "": "h".$aiHeight): $sNewPath;
    
    //not already cached
    if (! file_exists($sNewPath)) {
      $aSize = self::imageResize($aiWidth, $aiHeight, $sPath, $sNewPath);
    }
    
    return $sNewPath;
  }
  /**
   * Resize image to max width or height
   * @param int $width
   * @param int $height
   * @param String $sPath
   * @param int Write type
   *  IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG
   * @return <type>
   */
  public static function imageResize($aiWidth, $aiHeight, $sPath, $sNewPath = null, $aiWriteType = null) {
    if (empty($aiWidth) && empty($aiHeight)) { return; }
    list($iOriWidth, $iOriHeight, $iType) = getimagesize($sPath);
    
    if ($iOriWidth <= 0 || $iOriHeight <= 0) { 
      throw new Exception("Unable to load image: " . $sPath);
    }
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
    
    if ($width > $iOriWidth) {
      //too big, use original size
      $width = $iOriWidth;
      $height = $iOriHeight;
    }
    
    switch ($iType) {
      case IMAGETYPE_GIF:   //   gif -> jpg
        $image = imagecreatefromgif($sPath);
        break;
      case IMAGETYPE_JPEG:   //   jpeg -> jpg
        $image = imagecreatefromjpeg($sPath);
        break;
      case IMAGETYPE_PNG: //   png -> jpg
        $image = imagecreatefrompng($sPath);
        break;
      default:
        throw new FlexiException("Unknown image type: " . $iType, ERROR_DATATYPE);
    }
    FlexiLogger::info(__METHOD__, "Resize from: " . $iOriWidth . "x" . $iOriHeight . " to " . $width . "x" . $height);
    $image_p = imagecreatetruecolor($width, $height);
    imagealphablending($image_p, false);
    imagesavealpha($image_p, true);
    //$transparent = imagecolorallocatealpha($image_p, 255, 255, 255, 127);
    
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $iOriWidth, $iOriHeight);
    
    $sWritePath = empty($sNewPath) ? $sPath : $sNewPath;
    $iWriteType = empty($aiWriteType) ? $iType: (int)$aiWriteType;
    
    switch ($iWriteType) {
      case IMAGETYPE_GIF:
        $image = @imagegif($image_p, $sWritePath);
        break;
      case IMAGETYPE_JPEG:
        $image = @imagejpeg($image_p, $sWritePath);
        break;
      case IMAGETYPE_PNG:
        
        $image = @imagepng($image_p, $sWritePath);
        break;
      default:
        throw new FlexiException("Unknown image type: " . $iType, ERROR_DATATYPE);
    }
    
    if ($image===false) throw new Exception("Image generation failed: " . $iWriteType . ", path: " . $sWritePath);
    return array($width, $height);
  }
  
  public static function getImageSize($sPath) {
		list($iWidth, $iHeight, $iType) = getimagesize($sPath);
		return array($iWidth, $iHeight); //wxh
	}
  
  public static function imageIsEmpty($sPath) {
		list($iWidth, $iHeight, $iType) = getimagesize($sPath);
		switch ($iType) {
      case IMAGETYPE_GIF:   //   gif -> jpg
        $image = imagecreatefromgif($sPath);
        break;
      case IMAGETYPE_JPEG:   //   jpeg -> jpg
        $image = imagecreatefromjpeg($sPath);
        break;
      case IMAGETYPE_PNG: //   png -> jpg
        $image = imagecreatefrompng($sPath);
        break;
      default:
        throw new FlexiException("Unknown image type: " . $iType, ERROR_DATATYPE);
    }
    //echo "width: " . $iWidth . ", height: " . $iHeight . "<br/>\n";
    for($iRow = 0; $iRow < $iHeight; $iRow++) {
			//echo "Loop: " . $iRow . "<br/>\n";
			for ($iCol = 0; $iCol < $iWidth; $iCol++) {
				//echo "Loop-x: " . $iCol . "<br/>\n";
				$rgb = imagecolorat($image, $iCol, $iRow);
				$colors = imagecolorsforindex($image, $rgb);
				//echo "colors: " . print_r($colors,true) . "<br/>\n";
				if ($colors["alpha"] <=100) { // 127 - total transparent, 0- totally appear / opaque
					return false; //is not empty, has something visible
				}
			}//for x
		}//for y
		
		return true; //is empty, all alpha lower than 100
	}
}
?>
