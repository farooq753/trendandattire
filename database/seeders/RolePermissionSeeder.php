<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'View dashboard', 'slug' => 'dashboard.view', 'group' => 'dashboard'],
            ['name' => 'Manage products', 'slug' => 'products.manage', 'group' => 'catalog'],
            ['name' => 'Manage categories', 'slug' => 'categories.manage', 'group' => 'catalog'],
            ['name' => 'Manage inventory', 'slug' => 'inventory.manage', 'group' => 'inventory'],
            ['name' => 'View orders', 'slug' => 'orders.view', 'group' => 'orders'],
            ['name' => 'Manage orders', 'slug' => 'orders.manage', 'group' => 'orders'],
            ['name' => 'Verify payments', 'slug' => 'payments.verify', 'group' => 'finance'],
            ['name' => 'Manage refunds', 'slug' => 'refunds.manage', 'group' => 'finance'],
            ['name' => 'Manage production', 'slug' => 'production.manage', 'group' => 'production'],
            ['name' => 'View customers', 'slug' => 'customers.view', 'group' => 'customers'],
            ['name' => 'Manage customers', 'slug' => 'customers.manage', 'group' => 'customers'],
            ['name' => 'Manage coupons', 'slug' => 'coupons.manage', 'group' => 'marketing'],
            ['name' => 'Manage banners', 'slug' => 'banners.manage', 'group' => 'marketing'],
            ['name' => 'Manage settings', 'slug' => 'settings.manage', 'group' => 'settings'],
            ['name' => 'View reports', 'slug' => 'reports.view', 'group' => 'reports'],
            ['name' => 'Export reports', 'slug' => 'reports.export', 'group' => 'reports'],
            ['name' => 'Manage returns', 'slug' => 'returns.manage', 'group' => 'orders'],
            ['name' => 'Moderate reviews', 'slug' => 'reviews.moderate', 'group' => 'catalog'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(['slug' => $permission['slug']], $permission);
        }

        $all = Permission::query()->pluck('id', 'slug');

        $matrix = [
            'super-admin' => ['Super Admin', $all->keys()->all()],
            'store-manager' => ['Store Manager', ['dashboard.view', 'settings.manage', 'banners.manage', 'coupons.manage', 'reports.view', 'reports.export', 'orders.view', 'customers.view', 'customers.manage']],
            'product-manager' => ['Product Manager', ['dashboard.view', 'products.manage', 'categories.manage', 'reviews.moderate']],
            'inventory-manager' => ['Inventory Manager', ['dashboard.view', 'inventory.manage']],
            'order-manager' => ['Order Manager', ['dashboard.view', 'orders.view', 'orders.manage', 'returns.manage']],
            'production-manager' => ['Production Manager', ['dashboard.view', 'production.manage', 'orders.view']],
            'customer-support' => ['Customer Support', ['dashboard.view', 'customers.view', 'orders.view', 'returns.manage']],
            'finance-manager' => ['Finance Manager', ['dashboard.view', 'payments.verify', 'refunds.manage', 'reports.view', 'reports.export', 'orders.view']],
        ];

        foreach ($matrix as $slug => [$name, $slugs]) {
            $role = Role::query()->updateOrCreate(['slug' => $slug], ['name' => $name]);
            $role->permissions()->sync($all->only($slugs)->values()->all());
        }
    }
}
