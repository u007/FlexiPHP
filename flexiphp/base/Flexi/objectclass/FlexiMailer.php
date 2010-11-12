<?php

class FlexiMailer {
	protected static $oInstance = null;
  protected $oMailer = null;
	
	protected function __construct() {
		$this->oMailer = new PHPMailer(true); //New instance, with exceptions enabled
    $mail = &$this->oMailer;

    $mail->WordWrap   = 80;
    if (FlexiConfig::$sMailer == "smtp") {
      $mail->IsSMTP();                           // tell the class to use SMTP
      $mail->SMTPAuth   = true;                  // enable SMTP authentication
      $mail->Port       = FlexiConfig::$sMailerPort;                    // set the SMTP server port
      $mail->Host       = FlexiConfig::$sMailerHost; // SMTP server
      $mail->Username   = FlexiConfig::$sMailerUserId;     // SMTP server username
      $mail->Password   = FlexiConfig::$sMailerPassword;            // SMTP server password
    } else if(FlexiConfig::$sMailer == "sendmail") {
      $mail->IsSendmail();  // tell the class to use Sendmail
    } else if(FlexiConfig::$sMailer == "mail") {
      $mail->IsMail();  // tell the class to use Sendmail
    } else if(FlexiConfig::$sMailer == "qmail") {
      $mail->IsQmail();
    } else {
      throw new FlexiException(flexiT("Unknown mailer type") . ": " . FlexiConfig::$sMailer, ERROR_UNKNOWNTYPE);
    }

    $this->setFrom(FlexiConfig::$sSupportEmail);
	}

  public function reset() {
    $this->resetRecipient();
    $this->oMailer->ClearAttachments();
    $this->oMailer->ClearReplyTos();
    $this->oMailer->ClearCustomHeaders();
  }

  public function resetRecipient() {
    $this->oMailer->ClearAllRecipients();
    
  }

  public function setFrom($sEmail, $sName="") {
    $this->oMailer->SetFrom($sEmail, $sName);
  }

  public function addRecipient($sEmail, $sName = "") {
    $this->oMailer->AddAddress($sEmail, $sName);
  }

  public function addCC($sEmail, $sName = "") {
    $this->oMailer->AddCC($sEmail, $sName);
  }
  
  public function addBCC($sEmail, $sName = "") {
    $this->oMailer->AddBCC($sEmail, $sName);
  }

  public function setReplyTo($sEmail, $sName="") {
    $this->oMailer->AddReplyTo($sEmail, $sName);
  }

  /**
   * mail out
   *  will throw exception if error sending
   * @param String $sSubject
   * @param String $sMsg
   * @param Mixed: string /array = [["name", "email"]] $mTo
   */
	public function mail($sSubject, $sMsg, $mTo = null, $sType = "html") {

    if ($mTo != null) {
      if (gettype($mTo) == "string") {
        $this->addRecipient($mTo);
      } else if(gettype($mTo) == "array") {
        foreach($mTo as $mRecipient) {
          if (gettype($mRecipient) == "string") {
            $this->addRecipient($mRecipient);
          } else if (gettype($mRecipient)=="array") {
            $this->addRecipient($mRecipient[0], $mRecipient[1]);
          }
        }
      }
    }
    $body       = $sMsg;
    $body       = preg_replace('/\\\\/','', $body); //Strip backslashes

    $mail = &$this->oMailer;
    $mail->Subject  = $sSubject;

    if ($sType == "html") {
      $sAltBody = str_replace("<br>", "\r\n", $body);
      $sAltBody = str_replace("<br/>", "\r\n", $sAltBody);
      $sAltBody = strip_tags($sAltBody);
      $mail->AltBody    = $sAltBody;
      $mail->MsgHTML($body);
      $mail->IsHTML(true); // send as HTML
    }

    $mail->Send();
    //echo $e->errorMessage();
	}
	
	public static function getInstance($bNew = false) {
		if (self::$oInstance == null || $bNew) {
			self::$oInstance = new FlexiMailer();
		}
		
		return self::$oInstance;
	}
	
}
