<?php
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/includes/PHPMailer/Exception.php';
require_once __DIR__ . '/includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    $mail->setFrom(MAIL_FROM, MAIL_NAME);
    $mail->addAddress('YOUR_PERSONAL_EMAIL@gmail.com'); // Put your own email here

    $mail->isHTML(true);
    $mail->Subject = 'DriveEase Connection Test';
    $mail->Body    = '<h1>Success!</h1><p>Your SMTP connection is working perfectly.</p>';

    $mail->send();
    echo "Email sent successfully!";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>