<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class FlexiModxUtil {

  /**
   * parse all content and replace it by modx parser
   * @param String $source
   * @return String
   */
  public static function parseContent($source) {
    global $modx;

    $sResult = $source;
    // combine template and document variables
    //$sResult= $modx->mergeDocumentContent($sResult);
    // replace settings referenced in document
    $sResult= $modx->mergeSettingsContent($sResult);
    // replace HTMLSnippets in document
    $sResult= $modx->mergeChunkContent($sResult);
    // insert META tags & keywords
    $sResult= $modx->mergeDocumentMETATags($sResult);
    // find and merge snippets
    $sResult= $modx->evalSnippets($sResult);
    // find and replace Placeholders (must be parsed last) - Added by Raymond
    $sResult= $modx->mergePlaceholderContent($sResult);
    return $sResult;
  }
}

?>
