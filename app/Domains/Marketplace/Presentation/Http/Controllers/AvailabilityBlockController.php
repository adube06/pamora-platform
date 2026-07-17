<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers;

use App\Domains\Marketplace\Application\Services\CreateAvailabilityBlockService;
use App\Domains\Marketplace\Application\Services\RemoveAvailabilityBlockService;
use App\Domains\Marketplace\Domain\Models\AvailabilityBlock;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Marketplace\Presentation\Http\Requests\CreateAvailabilityBlockRequest;
use App\Domains\Marketplace\Presentation\Http\Requests\RemoveAvailabilityBlockRequest;
use Illuminate\Http\RedirectResponse;

class AvailabilityBlockController
{
    public function store(CreateAvailabilityBlockRequest $request, Vendor $vendor, CreateAvailabilityBlockService $service): RedirectResponse
    {
        $service->handle($vendor, $request->validated(), $request->user());

        return redirect()->route('vendor.index')->with('success', 'Availability block added.');
    }

    public function destroy(RemoveAvailabilityBlockRequest $request, AvailabilityBlock $availabilityBlock, RemoveAvailabilityBlockService $service): RedirectResponse
    {
        $service->handle($availabilityBlock, $request->user());

        return redirect()->route('vendor.index')->with('success', 'Availability block removed.');
    }
}
