<?php

namespace App\Domains\Identity\Presentation\Http\Controllers;

use App\Domains\Communication\Domain\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController
{
    public function show(Request $request): Response
    {
        return Inertia::render('Profile', [
            'user' => $request->user(),
            'notificationTypes' => collect(Notification::TYPES)
                ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
                ->values(),
            'notificationPreferences' => $request->user()->notification_preferences ?? [],
        ]);
    }
}
