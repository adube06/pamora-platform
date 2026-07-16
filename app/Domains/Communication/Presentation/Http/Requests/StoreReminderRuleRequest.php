<?php

namespace App\Domains\Communication\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReminderRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('schedule-reminder', $this->route('occasion'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'timeline_event_id' => [
                'required',
                'integer',
                Rule::exists('timeline_events', 'id')->where('occasion_id', $this->route('occasion')->id),
            ],
            // 2 hours / 24 hours / 7 days before the Timeline Event, matching
            // the PRD's own three Reminder Rule examples exactly.
            'offset_minutes' => ['required', 'integer', Rule::in([120, 1440, 10080])],
        ];
    }
}
