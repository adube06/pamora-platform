<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers;

use App\Domains\Marketplace\Application\Services\ApplyAsVendorService;
use App\Domains\Marketplace\Domain\Enums\VendorCategory;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Marketplace\Presentation\Http\Requests\ApplyAsVendorRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorController
{
    public function index(Request $request): Response
    {
        $vendor = Vendor::where('owner_id', $request->user()->id)->first();

        if ($vendor !== null) {
            return Inertia::render('Marketplace/Profile', [
                'vendor' => $vendor,
            ]);
        }

        return Inertia::render('Marketplace/Apply', [
            'categoryOptions' => collect(VendorCategory::cases())
                ->map(fn (VendorCategory $category) => ['value' => $category->value, 'label' => $category->label()])
                ->values(),
        ]);
    }

    public function store(ApplyAsVendorRequest $request, ApplyAsVendorService $service): RedirectResponse
    {
        $service->handle($request->user(), $request->validated());

        return redirect()->route('vendor.index')->with('success', 'Vendor application submitted.');
    }
}
