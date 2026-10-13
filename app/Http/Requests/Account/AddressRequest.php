<?php

namespace App\Http\Requests\Account;

use App\Models\Country;
use App\Rules\InternationalPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AddressRequest extends FormRequest
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
            'label' => ['nullable', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32', new InternationalPhone],
            'country_code' => ['required', 'exists:countries,code'],
            'region' => ['nullable', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'line1' => ['required', 'string', 'max:180'],
            'line2' => ['nullable', 'string', 'max:180'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_default_shipping' => ['nullable', 'boolean'],
            'is_default_billing' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $country = Country::query()->find($this->input('country_code'));

            if (! $country) {
                return;
            }

            if ($country->address_requires_region && blank($this->input('region'))) {
                $validator->errors()->add('region', $country->region_label.' is required.');
            }

            if ($country->address_requires_postal && blank($this->input('postal_code'))) {
                $validator->errors()->add('postal_code', 'Postal code is required.');
            }
        });
    }
}
