<?php 
session_start();

if( isset($_POST['submit'])) {
   if(($_SESSION['security_code'] == $_POST['security_code']) && (!empty($_SESSION['security_code'])) ) {
      // Insert you code for processing the form here
	  echo 'Thank you. Your message said "'.$_POST['message'].'"';
   } else {
      // Insert your code for showing an error message here
	  echo 'Sorry, you have provided an invalid security code';
   }
} else {
?>

	<form action="form.php" method="post">
		Message: <input type="text" name="message" /><br />
		<img src="CaptchaSecurityImages.php?width=100&height=40&character=5" /><br />
		Security Code: <input id="security_code" name="security_code" type="text" /><br />
		<input type="submit" name="submit" value="Submit" />
	</form>


<?php
	}
?>