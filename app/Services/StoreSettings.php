<?php

namespace App\Services;

use App\Models\StoreSetting;

class StoreSettings
{
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = StoreSetting::query()->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->value['v'] ?? $setting->value ?? $default;
    }

    public function put(string $key, mixed $value): void
    {
        StoreSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => ['v' => $value]],
        );
    }
}
