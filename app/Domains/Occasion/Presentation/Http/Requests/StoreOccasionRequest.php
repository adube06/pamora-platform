<?php

namespace App\Domains\Occasion\Presentation\Http\Requests;

use App\Domains\Occasion\Domain\Enums\OccasionType;
use App\Domains\Occasion\Domain\Enums\OccasionVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreOccasionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Any authenticated user may create an Occasion (they become its
        // Host) — this request only needs authentication, which the route
        // middleware already enforces.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(OccasionType::class)],
            'description' => ['nullable', 'string'],
            'primary_date' => ['nullable', 'date'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'location' => ['nullable', 'string', 'max:255'],
            'visibility' => ['nullable', new Enum(OccasionVisibility::class)],
        ];
    }
}
