<?php

class FlexiDomainUtil {
  
  public static function checkDomainAvailability($domain) {
    if(preg_match('/[;\&\|\>\<]/', $domain)) exit; //Could be a hack attempt
    
    exec("whois " . escapeshellarg($domain), $output); //:CAREFUL:
    $result = implode("\n", $output);

    $aInfo = pathinfo($domain);
    $extension = $aInfo["extension"];

    if ($extension != "my") {
      return (strpos($result, 'No match for domain') !== false);
    } else {
      return (strpos($result, 'does not exist in database') !== false);
    }
    
  }
  
}


?>
