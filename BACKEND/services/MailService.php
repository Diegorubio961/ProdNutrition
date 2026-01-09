<?php
namespace Services;

use Core\Mailer;

class MailService
{
    public static function sendWelcomeEmail(string $to, string $userName): bool
    {
        $subject = "¡Bienvenido, $userName!";
        $body    = file_get_contents(BASE_PATH . '/views/emails/welcome.html');
        $body    = str_replace('{{name}}', $userName, $body);

        return Mailer::send($to, $subject, $body, true);
    }

    public static function sendPlain(string $to, string $subject, string $text): bool
    {
        return Mailer::send($to, $subject, $text, false);
    }
}
