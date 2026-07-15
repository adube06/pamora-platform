<?php

namespace App\Domains\Occasion\Application\Services;

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Events\OccasionCreated;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Application\Services\CreateHostMembershipService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOccasionService
{
    public function __construct(
        private readonly CreateHostMembershipService $createHostMembershipService,
    ) {}

    /**
     * @param  array{title: string, type: string, description?: string, primary_date?: string, timezone?: string, location?: string, visibility?: string}  $data
     */
    public function handle(array $data, User $host): Occasion
    {
        return DB::transaction(function () use ($data, $host) {
            $occasion = Occasion::create([
                ...$data,
                'host_id' => $host->id,
                'slug' => $this->uniqueSlug($data['title']),
                'status' => OccasionStatus::Draft,
                'created_by' => $host->id,
            ]);

            // Cross-domain orchestration happens here, at the Application
            // layer, through People's own Application Service — Occasion
            // never writes to occasion_members directly (Constitution
            // Article V).
            $this->createHostMembershipService->handle($occasion, $host);

            OccasionCreated::dispatch($occasion, $host);

            return $occasion;
        });
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 1;

        while (Occasion::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
