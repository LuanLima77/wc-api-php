<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 0); //0=NOLIMIT
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class LiteratourMail {
 
    
   

    function sendCollectFailure($email,$pagina)
   {

 // Instantiation and passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = 3;
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'smtp.zoho.com';                    // Set the SMTP server to send through
    $mail->SMTPAuth   = true;
    //$mail->SMTPSecure = 'tls';
    $mail->Username   = 'contato@espiritualidadedivina.com';                     // SMTP username
    $mail->Password   = 'shadow123';                              // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

    //Recipients
    $mail->setFrom('contato@espiritualidadedivina.com', 'Literatour');
    $mail->addAddress($email,'Luan');     // Add a recipient
    //$mail->addAddress('ellen@example.com');               // Name is optional
    //$mail->addReplyTo('info@example.com', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('77luanlima@gmail.com', 'Luan Lima');
//https://rastreamentocorreios.info/consulta/JN420977697BR
    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Literatour - A coleta parou!';
    $mail->Body    = "
              <html>
              <head><title>A coleta não foi bem-sucedida! :( </title></head>
              <body>
              Infelizmente o seu robô de coleta experimentou um erro e parou na página <strong>$pagina</strong>!
                  </body>
              </html>";
    $mail->AltBody = 'Infelizmente o seu robô de coleta não conseguiu finalizar sua execução na página '.$pagina.'!';
    $mail->send();
    echo 'Email enviado <br>';
   } catch (Exception $e) {
    //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }

}
}

