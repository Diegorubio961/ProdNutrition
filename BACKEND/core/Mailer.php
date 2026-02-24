<?php

namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Core\Logger;

class Mailer
{
    public static function send(
        string $to,
        string $subject,
        string $body,
        bool   $isHtml = true,
        ?string $from  = null
    ): bool {
        // 1️⃣ Decide el driver
        $driver  = Env::get('MAIL_DRIVER') ?: (
            Env::get('APP_ENV') === 'production' ? 'smtp' : 'mail'
        );

        return $driver === 'smtp'
            ? self::sendViaSmtp($to, $subject, $body, $isHtml, $from)
            : self::sendViaMail($to, $subject, $body, $isHtml, $from);
    }

    /* ---------- SMTP (PHPMailer) ---------- */
    private static function sendViaSmtp(
        string $to,
        string $subject,
        string $body,
        bool $isHtml,
        ?string $from
    ): bool {
        Logger::info("➡️ Usando SMTP");
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = Env::get('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = Env::get('MAIL_USER');
            $mail->Password   = Env::get('MAIL_PASS');
            $mail->SMTPSecure = 'tls';
            $mail->Port       = Env::get('MAIL_PORT', 587);

            $mail->setFrom($from ?: Env::get('MAIL_FROM'), 'Soporte');
            $mail->addAddress($to);

            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->SMTPDebug = 2;  // Mostrar log detallado
            $mail->Debugoutput = function ($str, $level) {
                Logger::info("SMTP DEBUG [$level]: $str");
            };
            $mail->Timeout = 10;   // Evita que se quede colgado más de 10 segundos



            if (!$isHtml) $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            Logger::info("SMTP Mailer error: {$mail->ErrorInfo}");
            return false;
        }
    }

    /* ---------- mail() nativo ---------- */
    private static function sendViaMail(
        string $to,
        string $subject,
        string $body,
        bool $isHtml,
        ?string $from
    ): bool {
        Logger::info("➡️ Usando mail()");
        $headers  = 'From: ' . ($from ?: Env::get('MAIL_FROM', 'no-reply@localhost')) . "\r\n";
        if ($isHtml) {
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        }

        return mail($to, $subject, $body, $headers);
    }
}
