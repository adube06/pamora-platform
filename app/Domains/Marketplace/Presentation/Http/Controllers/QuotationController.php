<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers;

use App\Domains\Marketplace\Application\Services\RequestQuotationService;
use App\Domains\Marketplace\Application\Services\SubmitQuotationService;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Presentation\Http\Requests\RequestQuotationRequest;
use App\Domains\Marketplace\Presentation\Http\Requests\SubmitQuotationRequest;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\RedirectResponse;

class QuotationController
{
    public function store(RequestQuotationRequest $request, Occasion $occasion, RequestQuotationService $requestQuotationService): RedirectResponse
    {
        $service = Service::findOrFail($request->validated('service_id'));

        $requestQuotationService->handle($occasion, $service, $request->validated(), $request->user());

        return back()->with('success', 'Quotation requested.');
    }

    public function submit(SubmitQuotationRequest $request, Quotation $quotation, SubmitQuotationService $service): RedirectResponse
    {
        $service->handle($quotation, $request->validated(), $request->user());

        return redirect()->route('vendor.index')->with('success', 'Quotation submitted.');
    }
}
