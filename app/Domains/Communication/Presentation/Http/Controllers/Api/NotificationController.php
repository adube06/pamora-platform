<?php

namespace App\Domains\Communication\Presentation\Http\Controllers\Api;

use App\Domains\Communication\Application\Services\MarkNotificationReadService;
use App\Domains\Communication\Domain\Models\Notification;
use App\Domains\Communication\Presentation\Http\Resources\NotificationResource;
use App\Domains\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NotificationController
{
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(NotificationResource::collection($request->user()->notifications()->latest()->get()));
    }

    public function markRead(Request $request, Notification $notification, MarkNotificationReadService $service): JsonResponse
    {
        Gate::authorize('markRead', $notification);

        return ApiResponse::success(new NotificationResource($service->handle($notification)));
    }
}
