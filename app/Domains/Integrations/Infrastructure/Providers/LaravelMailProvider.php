<?php

namespace App\Domains\Integrations\Infrastructure\Providers;

use App\Domains\Integrations\Domain\Contracts\EmailProvider;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Wraps Laravel's own Mail facade — the mailer driver (log, smtp, ses,
 * postmark, ...) is configured entirely via .env (config/mail.php),
 * satisfying FR-004 (credentials never hardcoded) for free.
 *
 * Failures are caught and logged rather than left to break the caller's
 * flow (FR-003's "permanent failures shall be logged for investigation")
 * — a full retry/backoff policy is out of scope until there's a real
 * provider and a queue worker to retry against.
 */
class LaravelMailProvider implements EmailProvider
{
    public function send(Mailable $mailable, string $to): void
    {
        try {
            Mail::to($to)->send($mailable);
        } catch (Throwable $e) {
            Log::error('Email delivery failed', [
                'to' => $to,
                'mailable' => $mailable::class,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
