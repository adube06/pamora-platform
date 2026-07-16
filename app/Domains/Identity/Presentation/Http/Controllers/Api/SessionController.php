<?php

namespace App\Domains\Identity\Presentation\Http\Controllers\Api;

use App\Domains\Identity\Application\Services\RevokeSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController
{
    public function index(Request $request): JsonResponse
    {
        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity']);

        return response()->json(['success' => true, 'data' => $sessions]);
    }

    // "Sign out other devices" has no API mirror — Sanctum token auth has
    // no session cookie, so there is no "current session" to exclude from
    // the deletion the way there is for a web caller.
    public function destroy(Request $request, string $sessionId, RevokeSessionService $service): JsonResponse
    {
        $service->handle($request->user(), $sessionId, $request->user());

        return response()->json(['success' => true, 'data' => null]);
    }
}
