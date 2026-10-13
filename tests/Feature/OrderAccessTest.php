<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StoreFixture;
use Tests\TestCase;

class OrderAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_see_only_their_orders_and_guests_need_a_token_plus_email(): void
    {
        StoreFixture::basics();
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $owner->id,
            'customer_email' => $owner->email,
            'number' => 'TA-2026-000321',
        ]);

        $this->actingAs($other)->get(route('account.orders.show', $order))->assertForbidden();
        $this->actingAs($owner)->get(route('account.orders.show', $order))->assertOk();
        $this->post('/logout');
        $this->get(route('account.orders.show', $order))->assertRedirect(route('login'));
        $this->get('/account/orders/1')->assertRedirect(route('login'));

        $this->get(route('tracking.show', ['token' => $order->tracking_token]))->assertForbidden();

        $this->post(route('tracking.store'), [
            'number' => $order->number,
            'email' => 'wrong@example.com',
        ])->assertSessionHasErrors('number');

        $this->post(route('tracking.store'), [
            'number' => $order->number,
            'email' => $owner->email,
        ])->assertRedirect(route('tracking.show', ['token' => $order->tracking_token]));

        $this->get(route('tracking.show', ['token' => $order->tracking_token]))
            ->assertOk()
            ->assertSee($order->number);
    }
}
