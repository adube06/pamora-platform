<?php

namespace App\Domains\Communication\Presentation\Http\Controllers\Api;

use App\Domains\Communication\Application\Services\UpdatePreferencesService;
use App\Domains\Communication\Presentation\Http\Requests\UpdatePreferencesRequest;
use Illuminate\Http\JsonResponse;

class PreferenceController
{
    public function update(UpdatePreferencesRequest $request, UpdatePreferencesService $service): JsonResponse
    {
        $user = $service->handle($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => ['notification_preferences' => $user->notification_preferences],
        ]);
    }
}
