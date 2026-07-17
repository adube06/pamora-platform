<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers;

use App\Domains\Marketplace\Application\Services\PublishRentalItemService;
use App\Domains\Marketplace\Application\Services\UpdateRentalItemService;
use App\Domains\Marketplace\Domain\Models\RentalItem;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Marketplace\Presentation\Http\Requests\PublishRentalItemRequest;
use App\Domains\Marketplace\Presentation\Http\Requests\UpdateRentalItemRequest;
use Illuminate\Http\RedirectResponse;

class RentalItemController
{
    public function store(PublishRentalItemRequest $request, Vendor $vendor, PublishRentalItemService $service): RedirectResponse
    {
        $service->handle($vendor, $request->validated(), $request->user());

        return redirect()->route('vendor.index')->with('success', 'Rental Item published.');
    }

    public function update(UpdateRentalItemRequest $request, RentalItem $rentalItem, UpdateRentalItemService $updateService): RedirectResponse
    {
        $updateService->handle($rentalItem, $request->validated(), $request->user());

        return redirect()->route('vendor.index')->with('success', 'Rental Item updated.');
    }
}
