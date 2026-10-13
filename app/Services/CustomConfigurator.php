<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCustomizationOption;
use App\Support\Money;
use Illuminate\Support\Arr;

class CustomConfigurator
{
    public function __construct(private MeasurementNormalizer $measurements) {}

    /**
     * Recalculate a custom garment from server-side catalog data.
     *
     * @param  array<string, mixed>  $input
     * @return array{ok: bool, errors: array<string, string>, unit_price: string, lead_days: int, configuration: array<int, array<string, string>>, measurements: array<int, array<string, string>>, personalization: array<string, mixed>}
     */
    public function price(Product $product, array $input): array
    {
        $errors = [];
        $configuration = [];
        $normalizedMeasurements = [];
        $price = $product->effectivePrice();
        $lead = (int) ($product->production_lead_days ?? 0);

        if (! $product->acceptsCustom()) {
            $errors['product'] = 'This garment is not offered as a custom order.';
        }

        $product->loadMissing(['fabrics', 'customizationOptions.values', 'measurementSchemas']);

        $fabricId = Arr::get($input, 'fabric_id');
        $fabrics = $product->fabrics->where('is_active', true);

        if ($fabrics->isNotEmpty()) {
            $fabric = $fabrics->firstWhere('id', (int) $fabricId);

            if (! $fabric) {
                $errors['fabric_id'] = 'Choose a fabric.';
            } else {
                $price = Money::add($price, (string) $fabric->price_delta);
                $lead += (int) $fabric->extra_lead_days;
                $configuration[] = [
                    'option_key' => 'fabric',
                    'label' => 'Fabric',
                    'value' => $fabric->name,
                    'price_delta' => Money::normalize((string) $fabric->price_delta),
                ];
            }
        }

        $selected = Arr::get($input, 'options', []);

        foreach ($product->customizationOptions->where('is_active', true) as $option) {
            if ($option->input_type === 'file') {
                continue;
            }

            $valueId = $selected[$option->code] ?? null;
            $value = $option->values->first(fn ($row) => $row->is_active && (string) $row->id === (string) $valueId);

            if (! $value) {
                if ($option->is_required) {
                    $errors['options.'.$option->code] = $option->name.' is required.';
                }

                continue;
            }

            $price = Money::add($price, (string) $value->price_delta);
            $lead += (int) $value->extra_lead_days;
            $configuration[] = [
                'option_key' => $option->code,
                'label' => $option->name,
                'value' => $value->label,
                'price_delta' => Money::normalize((string) $value->price_delta),
            ];
        }

        $sizing = Arr::get($input, 'sizing_method');

        if (! in_array($sizing, ['standard', 'custom'], true)) {
            $errors['sizing_method'] = 'Choose a standard size or custom measurements.';
        }

        if ($sizing === 'standard') {
            $size = trim((string) Arr::get($input, 'standard_size'));
            $allowed = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];

            if (! in_array($size, $allowed, true)) {
                $errors['standard_size'] = 'Choose a standard size from XS to 3XL.';
            } else {
                $configuration[] = [
                    'option_key' => 'size',
                    'label' => 'Size',
                    'value' => $size,
                    'price_delta' => '0.0000',
                ];
            }
        }

        if ($sizing === 'custom') {
            $submitted = Arr::get($input, 'measurements', []);

            foreach ($product->measurementSchemas as $field) {
                $raw = $submitted[$field->field_key] ?? null;
                $value = is_array($raw) ? ($raw['value'] ?? null) : null;
                $unit = is_array($raw) ? ($raw['unit'] ?? null) : null;

                if ($value === null || $value === '' || $unit === null || $unit === '') {
                    if ($field->is_required) {
                        $errors['measurements.'.$field->field_key] = $field->label.' is required.';
                    }

                    continue;
                }

                try {
                    $normalized = $this->measurements->normalize((string) $value, (string) $unit);
                } catch (\InvalidArgumentException) {
                    $errors['measurements.'.$field->field_key] = $field->label.' must be a number in cm or in.';

                    continue;
                }

                if (Money::compare($normalized['normalized_cm'], (string) $field->min_cm) < 0
                    || Money::compare($normalized['normalized_cm'], (string) $field->max_cm) > 0) {
                    $errors['measurements.'.$field->field_key] = $field->label.' is outside the accepted range.';

                    continue;
                }

                $normalizedMeasurements[] = [
                    'field_key' => $field->field_key,
                    'label' => $field->label,
                    'original_value' => $normalized['original_value'],
                    'original_unit' => $normalized['original_unit'],
                    'normalized_cm' => $normalized['normalized_cm'],
                ];
            }
        }

        $personalization = ['type' => 'none', 'text' => null, 'logo_path' => Arr::get($input, 'logo_path')];
        $personalizationOption = $product->customizationOptions->firstWhere('code', 'personalization');

        if ($product->allows_personalization || $personalizationOption) {
            $type = Arr::get($input, 'personalization_type', 'none');

            if (! in_array($type, ['none', 'text', 'logo'], true)) {
                $errors['personalization_type'] = 'Choose a personalization option.';
            }

            if ($type === 'text') {
                $text = trim((string) Arr::get($input, 'personalization_text'));

                if ($text === '' || mb_strlen($text) > 20) {
                    $errors['personalization_text'] = 'Embroidery text must be 1 to 20 characters.';
                } else {
                    $delta = $this->personalizationDelta($personalizationOption, 'text');
                    $lead += $this->personalizationLead($personalizationOption, 'text');
                    $price = Money::add($price, $delta);
                    $personalization = ['type' => 'text', 'text' => $text, 'logo_path' => null];
                    $configuration[] = [
                        'option_key' => 'personalization',
                        'label' => 'Embroidery',
                        'value' => $text,
                        'price_delta' => $delta,
                    ];
                }
            }

            if ($type === 'logo') {
                $path = Arr::get($input, 'logo_path');

                if (! is_string($path) || $path === '') {
                    $errors['logo'] = 'Upload a logo to personalize this garment.';
                } else {
                    $delta = $this->personalizationDelta($personalizationOption, 'logo');
                    $lead += $this->personalizationLead($personalizationOption, 'logo');
                    $price = Money::add($price, $delta);
                    $personalization = ['type' => 'logo', 'text' => null, 'logo_path' => $path];
                    $configuration[] = [
                        'option_key' => 'personalization',
                        'label' => 'Logo',
                        'value' => 'Logo uploaded',
                        'price_delta' => $delta,
                    ];
                }
            }
        }

        return [
            'ok' => $errors === [],
            'errors' => $errors,
            'unit_price' => Money::round($price, 4),
            'lead_days' => $lead,
            'configuration' => $configuration,
            'measurements' => $normalizedMeasurements,
            'personalization' => $personalization,
        ];
    }

    private function personalizationDelta(?ProductCustomizationOption $option, string $value): string
    {
        $match = $option?->values->first(fn ($row) => $row->is_active && $row->value === $value);

        return Money::normalize($match ? (string) $match->price_delta : '0');
    }

    private function personalizationLead(?ProductCustomizationOption $option, string $value): int
    {
        $match = $option?->values->first(fn ($row) => $row->is_active && $row->value === $value);

        return (int) ($match->extra_lead_days ?? 0);
    }
}
