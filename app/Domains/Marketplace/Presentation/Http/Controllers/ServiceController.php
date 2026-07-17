<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers;

use App\Domains\Marketplace\Application\Services\PublishServiceService;
use App\Domains\Marketplace\Application\Services\UpdateServiceService;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Marketplace\Presentation\Http\Requests\PublishServiceRequest;
use App\Domains\Marketplace\Presentation\Http\Requests\UpdateServiceRequest;
use Illuminate\Http\RedirectResponse;

class ServiceController
{
    public function store(PublishServiceRequest $request, Vendor $vendor, PublishServiceService $service): RedirectResponse
    {
        $service->handle($vendor, $request->validated(), $request->user());

        return redirect()->route('vendor.index')->with('success', 'Service published.');
    }

    public function update(UpdateServiceRequest $request, Service $service, UpdateServiceService $updateService): RedirectResponse
    {
        $updateService->handle($service, $request->validated(), $request->user());

        return redirect()->route('vendor.index')->with('success', 'Service updated.');
    }
}
