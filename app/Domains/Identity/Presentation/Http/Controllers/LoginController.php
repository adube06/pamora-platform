<?php

namespace App\Domains\Identity\Presentation\Http\Controllers;

use App\Domains\Identity\Application\Services\AuthenticateUserService;
use App\Domains\Identity\Presentation\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoginController
{
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/Login', [
            'invitation' => $request->query('invitation'),
        ]);
    }

    public function store(LoginRequest $request, AuthenticateUserService $service): RedirectResponse
    {
        $service->handle($request->validated());

        $request->session()->regenerate();

        if ($token = $request->input('invitation')) {
            return redirect()->route('invitations.show', $token);
        }

        return redirect()->intended(route('occasions.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
