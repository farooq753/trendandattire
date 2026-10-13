<?php

namespace Tests\Support;

use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\ShippingMethod;
use App\Models\User;
use Database\Seeders\GeographySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StoreSettingSeeder;

class StoreFixture
{
    public static function basics(): ShippingMethod
    {
        (new RolePermissionSeeder)->run();
        (new GeographySeeder)->run();
        (new StoreSettingSeeder)->run();

        return ShippingMethod::query()->where('code', 'ground')->firstOrFail();
    }

    public static function readyProduct(int $stock = 5, string $price = '50.0000'): Product
    {
        $product = Product::factory()->create([
            'regular_price' => $price,
            'sale_price' => null,
            'fulfillment_type' => 'ready',
            'is_published' => true,
            'published_at' => now(),
            'weight_grams' => 400,
        ]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock_on_hand' => $stock,
            'combination_key' => 'size:m|color:navy',
            'weight_grams' => 400,
        ]);

        return $product->fresh('variants');
    }

    public static function userWithRole(string $roleSlug): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $user->roles()->attach($role->id);

        return $user;
    }

    public static function permissionExists(string $slug): bool
    {
        return Permission::query()->where('slug', $slug)->exists();
    }
}
