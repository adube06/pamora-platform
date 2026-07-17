<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers;

use App\Domains\Marketplace\Application\Services\AcceptQuotationService;
use App\Domains\Marketplace\Application\Services\ConfirmBookingService;
use App\Domains\Marketplace\Application\Services\RejectQuotationService;
use App\Domains\Marketplace\Application\Services\RequestQuotationService;
use App\Domains\Marketplace\Application\Services\SubmitQuotationService;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Presentation\Http\Requests\AcceptQuotationRequest;
use App\Domains\Marketplace\Presentation\Http\Requests\ConfirmBookingRequest;
use App\Domains\Marketplace\Presentation\Http\Requests\RejectQuotationRequest;
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

    public function accept(AcceptQuotationRequest $request, Quotation $quotation, AcceptQuotationService $service): RedirectResponse
    {
        $service->handle($quotation, $request->user());

        return back()->with('success', 'Quotation accepted.');
    }

    public function reject(RejectQuotationRequest $request, Quotation $quotation, RejectQuotationService $service): RedirectResponse
    {
        $service->handle($quotation, $request->user());

        return back()->with('success', 'Quotation rejected.');
    }

    public function confirm(ConfirmBookingRequest $request, Quotation $quotation, ConfirmBookingService $service): RedirectResponse
    {
        $service->handle($quotation, $request->user());

        return back()->with('success', 'Booking confirmed.');
    }
}
