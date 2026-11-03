<?php

namespace App\Http\Requests;

use App\Models\Night;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Night $night */
        $night = $this->route('night');

        return [
            'dj_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255'],
            'genre' => ['nullable', 'string', 'max:80'],
            'mix_url' => ['nullable', 'url:https', 'max:255'],
            // Only slots that belong to this night can be picked.
            'slot_id' => ['nullable', 'integer', Rule::exists('slots', 'id')->where('night_id', $night->id)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'dj_name' => 'DJ name',
            'mix_url' => 'mix link',
            'slot_id' => 'slot',
        ];
    }
}
