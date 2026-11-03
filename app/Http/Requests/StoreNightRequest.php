<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'venue' => ['required', 'string', 'max:120'],
            'starts_at' => ['required', 'date', 'after:now'],
            'slot_minutes' => ['required', 'integer', 'min:15', 'max:180'],
            'slot_count' => ['required', 'integer', 'min:1', 'max:24'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'starts_at' => 'start time',
            'slot_minutes' => 'slot length',
            'slot_count' => 'number of slots',
        ];
    }
}
