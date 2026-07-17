<?php

namespace App\Domains\People\Presentation\Http\Requests;

use App\Domains\People\Domain\Enums\Responsibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AssignResponsibilitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assign-responsibilities', $this->route('occasionMember')->occasion);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'responsibilities' => ['nullable', 'array'],
            'responsibilities.*' => [new Enum(Responsibility::class)],
        ];
    }
}
