<?php

namespace App\Support;

use Illuminate\Mail\MailManager;

/**
 * Pushes the mail settings saved in the author dashboard into the runtime
 * configuration so the app can send mail without a .env edit or a redeploy.
 */
final class MailConfigurator
{
    public static function apply(): bool
    {
        $mailer = Settings::get('mail_mailer', 'log');

        if ($mailer === 'log') {
            config(['mail.default' => 'log']);

            return false;
        }

        config([
            'mail.default' => $mailer,
            'mail.mailers.smtp.host' => Settings::get('mail_host', ''),
            'mail.mailers.smtp.port' => (int) Settings::get('mail_port', '587'),
            'mail.mailers.smtp.username' => Settings::get('mail_username'),
            'mail.mailers.smtp.password' => Settings::get('mail_password'),
            'mail.mailers.smtp.encryption' => Settings::get('mail_encryption', 'tls') ?: null,
        ]);

        if ($from = Settings::get('mail_from_address')) {
            config([
                'mail.from.address' => $from,
                'mail.from.name' => Settings::get('mail_from_name', config('app.name')),
            ]);
        }

        if (class_exists(MailManager::class) && method_exists(app(MailManager::class), 'forgetMailers')) {
            app(MailManager::class)->forgetMailers();
        }

        return true;
    }

    public static function isConfigured(): bool
    {
        return Settings::get('mail_mailer', 'log') !== 'log'
            && Settings::get('mail_host') !== null
            && Settings::get('mail_from_address') !== null;
    }
}
