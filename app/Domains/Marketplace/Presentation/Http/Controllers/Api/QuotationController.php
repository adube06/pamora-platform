<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers\Api;

use App\Domains\Marketplace\Application\Services\RequestQuotationService;
use App\Domains\Marketplace\Application\Services\SubmitQuotationService;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Presentation\Http\Requests\RequestQuotationRequest;
use App\Domains\Marketplace\Presentation\Http\Requests\SubmitQuotationRequest;
use App\Domains\Marketplace\Presentation\Http\Resources\QuotationResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\JsonResponse;

class QuotationController
{
    public function store(RequestQuotationRequest $request, Occasion $occasion, RequestQuotationService $requestQuotationService): JsonResponse
    {
        $service = Service::findOrFail($request->validated('service_id'));

        $quotation = $requestQuotationService->handle($occasion, $service, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new QuotationResource($quotation),
        ], 201);
    }

    public function submit(SubmitQuotationRequest $request, Quotation $quotation, SubmitQuotationService $service): JsonResponse
    {
        $submitted = $service->handle($quotation, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new QuotationResource($submitted),
        ]);
    }
}
