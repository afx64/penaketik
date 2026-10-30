<?php

namespace App\Support;

use Illuminate\Support\Facades\Mail;
use Throwable;

final class Notifier
{
    public static function send(?string $to, string $subject, string $body): void
    {
        if (! $to) {
            return;
        }

        try {
            MailConfigurator::apply();
            Mail::raw($body, fn ($message) => $message->to($to)->subject($subject));
        } catch (Throwable $exception) {
            // A broken mail server must never break checkout or access management.
            report($exception);
        }
    }
}
