<?php

namespace App\Domains\Media\Presentation\Http\Controllers;

use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Media\Application\Services\MoveMediaAssetService;
use App\Domains\Media\Domain\Models\Album;
use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Media\Presentation\Http\Requests\MoveMediaAssetRequest;
use App\Domains\Planning\Domain\Models\Task;
use Illuminate\Http\RedirectResponse;

class MediaAssetController
{
    public function move(MoveMediaAssetRequest $request, MediaAsset $mediaAsset, MoveMediaAssetService $service): RedirectResponse
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

        $service->handle($mediaAsset, $attachable, $request->user());

        return back()->with('success', 'Media moved.');
    }
}
