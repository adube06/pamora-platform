<?php

namespace App\Domains\Finance\Presentation\Http\Requests;

use App\Domains\Finance\Domain\Enums\ContributionMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('record-contribution', $this->route('occasion'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'contributor_name' => ['required', 'string', 'max:255'],
            'contributor_phone' => ['nullable', 'string', 'max:32'],
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', new Enum(ContributionMethod::class)],
            'message' => ['nullable', 'string', 'max:1000'],
            'contributed_at' => ['required', 'date', Rule::date()->beforeOrEqual('today')],
        ];
    }
}
