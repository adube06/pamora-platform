<?php

namespace App\Domains\People\Application\Services;

use App\Domains\People\Domain\Events\RsvpSubmitted;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use Illuminate\Validation\ValidationException;

class SubmitRsvpService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * @param  array{rsvp_status: string, guest_count?: int|null, rsvp_message?: string|null}  $data
     */
    public function handle(OccasionMember $member, array $data): OccasionMember
    {
        $this->ensureOccasionAcceptsActivity($member->occasion);

        // BR-013: a Guest may respond only once unless the Host reopens RSVP.
        if ($member->rsvp_status !== null) {
            throw ValidationException::withMessages([
                'rsvp_status' => 'You have already responded. Ask the Host to reopen RSVP to respond again.',
            ]);
        }

        $member->update([
            'rsvp_status' => $data['rsvp_status'],
            'rsvp_responded_at' => now(),
            'guest_count' => $data['guest_count'] ?? null,
            'rsvp_message' => $data['rsvp_message'] ?? null,
        ]);

        RsvpSubmitted::dispatch($member);

        return $member;
    }
}
