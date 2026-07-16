<?php

namespace App\Domains\Media\Presentation\Http\Controllers\Api;

use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Media\Application\Services\MoveMediaAssetService;
use App\Domains\Media\Domain\Models\Album;
use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Media\Presentation\Http\Requests\MoveMediaAssetRequest;
use App\Domains\Media\Presentation\Http\Resources\MediaAssetResource;
use App\Domains\Planning\Domain\Models\Task;
use Illuminate\Http\JsonResponse;

class MediaAssetController
{
    public function move(MoveMediaAssetRequest $request, MediaAsset $mediaAsset, MoveMediaAssetService $service): JsonResponse
    {
        $albumId = $request->validated('album_id');
        $taskId = $request->validated('task_id');
        $expenseId = $request->validated('expense_id');

        $attachable = match (true) {
            $albumId !== null => Album::findOrFail($albumId),
            $taskId !== null => Task::findOrFail($taskId),
            $expenseId !== null => Expense::findOrFail($expenseId),
            default => null,
        };

        $mediaAsset = $service->handle($mediaAsset, $attachable, $request->user());

        return response()->json([
            'success' => true,
            'data' => new MediaAssetResource($mediaAsset->load(['uploadedBy:id,name', 'attachable'])),
        ]);
    }
}
