<?php

namespace App\Domains\Identity\Presentation\Http\Controllers;

use App\Domains\Identity\Application\Services\ForgotPasswordService;
use App\Domains\Identity\Application\Services\ResetPasswordService;
use App\Domains\Identity\Presentation\Http\Requests\ForgotPasswordRequest;
use App\Domains\Identity\Presentation\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetController
{
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function store(ForgotPasswordRequest $request, ForgotPasswordService $service): RedirectResponse
    {
        $service->handle($request->validated('email'));

        return back()->with('success', 'A password reset link has been sent to your email.');
    }

    public function edit(Request $request, string $token): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function update(ResetPasswordRequest $request, ResetPasswordService $service): RedirectResponse
    {
        $service->handle($request->validated());

        return redirect()->route('login')->with('success', 'Your password has been reset.');
    }
}
