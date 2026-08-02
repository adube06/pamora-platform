<?php

namespace App\Domains\Communication\Presentation\Http\Controllers\Api;

use App\Domains\Communication\Application\Services\UpdatePreferencesService;
use App\Domains\Communication\Presentation\Http\Requests\UpdatePreferencesRequest;
use App\Domains\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class PreferenceController
{
    public function update(UpdatePreferencesRequest $request, UpdatePreferencesService $service): JsonResponse
    {
        $user = $service->handle($request->user(), $request->validated());

        return ApiResponse::success(['notification_preferences' => $user->notification_preferences]);
    }
}
