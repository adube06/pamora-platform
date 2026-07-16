<?php

namespace App\Domains\Identity\Presentation\Http\Controllers;

use App\Domains\Identity\Application\Services\RevokeOtherSessionsService;
use App\Domains\Identity\Application\Services\RevokeSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SessionController
{
    public function destroy(Request $request, string $sessionId, RevokeSessionService $service): RedirectResponse
    {
        $service->handle($request->user(), $sessionId, $request->user());

        return back()->with('success', 'Session revoked.');
    }

    public function destroyOthers(Request $request, RevokeOtherSessionsService $service): RedirectResponse
    {
        $count = $service->handle($request->user(), $request->session()->getId(), $request->user());

        return back()->with('success', $count > 0 ? "Signed out of {$count} other session(s)." : 'No other sessions to sign out.');
    }
}
