<?php

namespace App\Domains\Integrations\Domain\Contracts;

use Illuminate\Mail\Mailable;

/**
 * FR-001 (Provider Abstraction) — business domains depend on this
 * interface, never on Laravel's Mail facade directly, so the transport
 * can be swapped without touching business logic.
 */
interface EmailProvider
{
    public function send(Mailable $mailable, string $to): void;
}
