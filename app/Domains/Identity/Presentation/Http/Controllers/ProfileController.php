<?php

namespace App\Domains\Identity\Presentation\Http\Controllers;

use App\Domains\Communication\Domain\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController
{
    public function show(Request $request): Response
    {
        $currentSessionId = $request->session()->getId();
        $lifetimeMinutes = (int) config('session.lifetime');

        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn ($session) => [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'device' => $this->device($session->user_agent),
                'last_active_at' => now()->createFromTimestamp($session->last_activity)->toIso8601String(),
                'expires_at' => now()->createFromTimestamp($session->last_activity)->addMinutes($lifetimeMinutes)->toIso8601String(),
                'is_current' => $session->id === $currentSessionId,
            ]);

        return Inertia::render('Profile', [
            'user' => $request->user(),
            'notificationTypes' => collect(Notification::TYPES)
                ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
                ->values(),
            'notificationPreferences' => $request->user()->notification_preferences ?? [],
            'sessions' => $sessions,
        ]);
    }

    /**
     * Dependency-free device/browser heuristic — no user-agent parsing
     * package installed, and this is the only place that needs one.
     */
    private function device(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'Unknown device';
        }

        $os = match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Mac OS') => 'macOS',
            str_contains($userAgent, 'iPhone') => 'iPhone',
            str_contains($userAgent, 'iPad') => 'iPad',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown OS',
        };

        $browser = match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') && ! str_contains($userAgent, 'Chrome') => 'Safari',
            default => 'Unknown browser',
        };

        return "{$browser} on {$os}";
    }
}
