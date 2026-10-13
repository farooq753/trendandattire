<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StoreFixture;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StoreFixture::basics();
    }

    public function test_customers_cannot_open_admin(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin')->assertForbidden();
    }

    public function test_inventory_manager_can_open_inventory_and_not_products(): void
    {
        $manager = StoreFixture::userWithRole('inventory-manager');

        $this->actingAs($manager)->get(route('admin.inventory.index'))->assertOk();
        $this->actingAs($manager)->get(route('admin.products.index'))->assertForbidden();
    }

    public function test_finance_manager_cannot_export_without_being_confused_with_product_access(): void
    {
        $finance = StoreFixture::userWithRole('finance-manager');

        $this->actingAs($finance)->get(route('admin.reports.index'))->assertOk();
        $this->actingAs($finance)->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs($finance)->get(route('admin.reports.export', ['type' => 'orders']))->assertOk();
    }

    public function test_super_admin_can_open_the_dashboard(): void
    {
        $admin = StoreFixture::userWithRole('super-admin');

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }
}
