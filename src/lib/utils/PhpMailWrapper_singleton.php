<?php



require_once __DIR__ . "/PhpMailer/src/Exception.php";
require_once __DIR__ . "/PhpMailer/src/PHPMailer.php";
require_once __DIR__ . "/PhpMailer/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class PhpMailWrapper{
    var $mailer;
    public function __construct()
    {
        $mail = new PHPMailer();
        $mail->Host = NOREPLY_SMTP;
        $mail->Username = NOREPLY_USER;                     //SMTP username
        $mail->Password = NOREPLY_PASSWORD;           //Enable implicit TLS encryption
        $mail->Port = NOREPLY_SMTP_PORT;
        $mail->isSMTP();

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->SMTPAuth = true;                              //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom(NOREPLY_MAIL, 'NoReplay-' . COMPANY_NAME);
        $this->mailer = $mail;
    }
}
