<?php

require_once (APP_PATH . "lib/PHPMailer_5.2.4/class.phpmailer.php");

//require_once '../swiftMailer/lib/swift_required.php';
class email {
    /* function __construct($email,$from,$name,$temat,$tresc,$alt) {

      if (mail($email, $temat, str_replace("\n","<br>",htmlspecialchars($wiadomosc)),
      "From: $from\r\n"
      ."Reply-To: $from\r\n"
      ."X-Mailer: PHP/" . phpversion()));

      else echo "blad";
      }
     */

    function __construct($email, $from, $name, $temat, $tresc, $alt = null) {
        $mail=new PHPMailer();
 
        $mail->IsSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug=1;
        $mail->SMTPAuth=true;
        $mail->SMTPSecure=MAIL_SECURE;
        $mail->Host=MAIL_HOST;
        $mail->Port=MAIL_PORT;
        $mail->IsHTML(true);
        $mail->Username=MAIL_USERNAME;
        $mail->Password=MAIL_PASSWORD;
        $mail->From = MAIL_USERNAME;
        $mail->FromName = 'Kup Bilet';
        $mail->Subject=$temat;
        $mail->Body=$tresc;
        $mail->AddAddress($email);
        if (!$mail->Send()) {
            echo "There has been a mail error <br>";
            echo $mail->ErrorInfo . "<br>";exit;
        }
    }

}

?>