<?php

namespace App\Domains\Communication\Presentation\Http\Controllers;

use App\Domains\Communication\Application\Services\UpdatePreferencesService;
use App\Domains\Communication\Presentation\Http\Requests\UpdatePreferencesRequest;
use Illuminate\Http\RedirectResponse;

class PreferenceController
{
    public function update(UpdatePreferencesRequest $request, UpdatePreferencesService $service): RedirectResponse
    {
        $service->handle($request->user(), $request->validated());

        return back()->with('success', 'Preferences updated.');
    }
}
