<?php

namespace App\Domains\Identity\Presentation\Http\Controllers;

use App\Domains\Identity\Application\Services\RegisterUserService;
use App\Domains\Identity\Presentation\Http\Requests\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController
{
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/Register', [
            'invitation' => $request->query('invitation'),
        ]);
    }

    public function store(RegisterRequest $request, RegisterUserService $service): RedirectResponse
    {
        $user = $service->handle($request->validated());

        Auth::login($user);

        if ($token = $request->input('invitation')) {
            return redirect()->route('invitations.show', $token);
        }

        return redirect()->route('occasions.index');
    }
}
