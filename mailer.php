<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/PHPMailer/src/Exception.php";
require_once __DIR__ . "/PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/PHPMailer/src/SMTP.php";

require_once "config.php";

/**
 * Send OTP mail
 * MUST NEVER echo / die / throw
 */
function sendOTP($toEmail, $otp)
{
    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_USER, "Face2Life");
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = "Face2Life OTP Verification";
        $mail->Body    = "
            <h2>OTP Verification</h2>
            <p>Your verification code is:</p>
            <h1>$otp</h1>
            <p>This OTP is valid for 5 minutes.</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        // 🔥 NEVER echo / die — keep JSON clean
        return false;
    }
}
