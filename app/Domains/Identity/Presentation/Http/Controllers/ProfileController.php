<?php

namespace App\Domains\Identity\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController
{
    public function show(Request $request): Response
    {
        return Inertia::render('Profile', [
            'user' => $request->user(),
        ]);
    }
}
