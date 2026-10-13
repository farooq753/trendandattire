<?php

namespace App\Http\Requests\Account;

use App\Rules\InternationalPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32', new InternationalPhone],
            'locale' => ['required', Rule::in(['en'])],
            'timezone' => ['required', 'timezone'],
            'currency_code' => ['required', 'exists:currencies,code'],
            'country_code' => ['nullable', 'exists:countries,code'],
            'measurement_unit' => ['required', Rule::in(['cm', 'in'])],
            'measurement_history_consent' => ['nullable', 'boolean'],
        ];
    }
}
