<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            GeographySeeder::class,
            StoreSettingSeeder::class,
            CatalogSeeder::class,
        ]);

        if (app()->environment('production')) {
            return;
        }

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@trendandattire.test'],
            [
                'name' => 'Local Admin',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_active' => true,
                'locale' => 'en',
                'currency_code' => 'USD',
            ],
        );
        $admin->roles()->sync([Role::query()->where('slug', 'super-admin')->value('id')]);

        User::query()->updateOrCreate(
            ['email' => 'customer@trendandattire.test'],
            [
                'name' => 'Local Customer',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_active' => true,
                'locale' => 'en',
                'currency_code' => 'USD',
                'country_code' => 'US',
                'phone' => '+12025550123',
            ],
        );
    }
}
