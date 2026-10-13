<?php

namespace App\Http\Requests\Checkout;

use App\Models\Country;
use App\Rules\InternationalPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'checkout_token' => ['required', 'string', 'size:40'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:32', new InternationalPhone],
            'shipping_method_id' => ['required', 'integer'],
            'payment_method' => ['required', Rule::in(['cod', 'bank_transfer'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'billing_same' => ['nullable', 'boolean'],
            'shipping.name' => ['required', 'string', 'max:120'],
            'shipping.phone' => ['required', 'string', 'max:32', new InternationalPhone],
            'shipping.country_code' => ['required', 'string', 'size:2'],
            'shipping.region' => ['nullable', 'string', 'max:120'],
            'shipping.city' => ['required', 'string', 'max:120'],
            'shipping.line1' => ['required', 'string', 'max:180'],
            'shipping.line2' => ['nullable', 'string', 'max:180'],
            'shipping.postal_code' => ['nullable', 'string', 'max:20'],
            'billing.name' => ['required_unless:billing_same,1', 'nullable', 'string', 'max:120'],
            'billing.phone' => ['nullable', 'string', 'max:32', new InternationalPhone],
            'billing.country_code' => ['required_unless:billing_same,1', 'nullable', 'string', 'size:2'],
            'billing.region' => ['nullable', 'string', 'max:120'],
            'billing.city' => ['required_unless:billing_same,1', 'nullable', 'string', 'max:120'],
            'billing.line1' => ['required_unless:billing_same,1', 'nullable', 'string', 'max:180'],
            'billing.line2' => ['nullable', 'string', 'max:180'],
            'billing.postal_code' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->guardAddress('shipping', $validator);
            if (! $this->boolean('billing_same')) {
                $this->guardAddress('billing', $validator);
            }
        });
    }

    private function guardAddress(string $prefix, Validator $validator): void
    {
        $country = Country::query()->where('code', $this->input($prefix.'.country_code'))->where('is_active', true)->first();

        if (! $country) {
            $validator->errors()->add($prefix.'.country_code', 'Choose a supported country.');

            return;
        }

        if ($country->address_requires_region && blank($this->input($prefix.'.region'))) {
            $validator->errors()->add($prefix.'.region', $country->region_label.' is required for '.$country->name.'.');
        }

        if ($country->address_requires_postal && blank($this->input($prefix.'.postal_code'))) {
            $validator->errors()->add($prefix.'.postal_code', 'Postal code is required for '.$country->name.'.');
        }
    }
}
