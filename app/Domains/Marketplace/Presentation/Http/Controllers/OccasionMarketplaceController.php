<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers;

use App\Domains\Marketplace\Domain\Enums\ServiceStatus;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OccasionMarketplaceController
{
    public function index(Request $request, Occasion $occasion): Response
    {
        $request->user()->can('view', $occasion) || abort(403);

        return Inertia::render('Occasions/Marketplace', [
            'occasion' => $occasion,
            'services' => Service::query()
                ->where('status', ServiceStatus::Active)
                ->whereHas('vendor', fn ($query) => $query->where('verification_status', VendorVerificationStatus::Verified))
                ->with('vendor:id,business_name')
                ->latest()
                ->get(),
            'quotations' => Quotation::where('occasion_id', $occasion->id)
                ->with('service:id,uuid,name')
                ->latest()
                ->get(),
            'canRequestQuotation' => $request->user()->can('request-quotation', $occasion),
        ]);
    }
}
