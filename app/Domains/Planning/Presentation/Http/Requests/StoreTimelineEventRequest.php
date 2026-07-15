<?php

namespace App\Domains\Planning\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimelineEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-timeline', $this->route('occasion'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
        ];
    }
}
