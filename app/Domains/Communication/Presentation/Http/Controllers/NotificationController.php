<?php

namespace App\Domains\Communication\Presentation\Http\Controllers;

use App\Domains\Communication\Application\Services\MarkNotificationReadService;
use App\Domains\Communication\Domain\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController
{
    public function index(Request $request): Response
    {
        return Inertia::render('Notifications/Index', [
            'notifications' => $request->user()->notifications()->latest()->get(),
        ]);
    }

    public function markRead(Request $request, Notification $notification, MarkNotificationReadService $service): RedirectResponse
    {
        $request->user()->can('markRead', $notification) || abort(403);

        $service->handle($notification);

        return back();
    }
}
