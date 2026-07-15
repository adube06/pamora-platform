<?php

namespace App\Domains\Communication\Presentation\Http\Controllers;

use App\Domains\Communication\Application\Services\PublishAnnouncementService;
use App\Domains\Communication\Presentation\Http\Requests\StoreAnnouncementRequest;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommunicationController
{
    public function index(Request $request, Occasion $occasion): Response
    {
        $request->user()->can('view', $occasion) || abort(403);

        return Inertia::render('Occasions/Communication', [
            'occasion' => $occasion,
            'announcements' => $occasion->announcements()->with('createdBy:id,name')->latest('published_at')->get(),
            'canPublishAnnouncement' => $request->user()->can('publish-announcement', $occasion),
        ]);
    }

    public function store(StoreAnnouncementRequest $request, Occasion $occasion, PublishAnnouncementService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Announcement published.');
    }
}
