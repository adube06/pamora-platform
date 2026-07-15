<?php

namespace App\Domains\Communication\Presentation\Http\Controllers\Api;

use App\Domains\Communication\Application\Services\PublishAnnouncementService;
use App\Domains\Communication\Presentation\Http\Requests\StoreAnnouncementRequest;
use App\Domains\Communication\Presentation\Http\Resources\AnnouncementResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunicationController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        $request->user()->can('view', $occasion) || abort(403);

        return response()->json([
            'success' => true,
            'data' => AnnouncementResource::collection(
                $occasion->announcements()->with('createdBy:id,name')->latest('published_at')->get()
            ),
        ]);
    }

    public function store(StoreAnnouncementRequest $request, Occasion $occasion, PublishAnnouncementService $service): JsonResponse
    {
        $announcement = $service->handle($occasion, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new AnnouncementResource($announcement->load('createdBy:id,name')),
        ], 201);
    }
}
