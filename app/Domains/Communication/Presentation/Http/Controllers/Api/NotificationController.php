<?php

namespace App\Domains\Communication\Presentation\Http\Controllers\Api;

use App\Domains\Communication\Application\Services\MarkNotificationReadService;
use App\Domains\Communication\Domain\Models\Notification;
use App\Domains\Communication\Presentation\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($request->user()->notifications()->latest()->get()),
        ]);
    }

    public function markRead(Request $request, Notification $notification, MarkNotificationReadService $service): JsonResponse
    {
        $request->user()->can('markRead', $notification) || abort(403);

        return response()->json([
            'success' => true,
            'data' => new NotificationResource($service->handle($notification)),
        ]);
    }
}
