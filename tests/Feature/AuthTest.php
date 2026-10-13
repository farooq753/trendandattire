<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StoreFixture;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_login_logout_and_password_reset_do_not_reveal_accounts(): void
    {
        StoreFixture::basics();

        $this->post('/register', [
            'name' => 'Amina Shah',
            'email' => 'amina@example.com',
            'phone' => '+12025550123',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticated();
        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();

        $this->post('/login', [
            'email' => 'amina@example.com',
            'password' => 'password',
        ])->assertRedirect(route('account.dashboard'));

        $this->post('/logout');

        $known = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'amina@example.com']);
        $unknown = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'missing@example.com']);

        $known->assertSessionHas('status', 'If that email belongs to an account, a reset link is on its way.');
        $unknown->assertSessionHas('status', 'If that email belongs to an account, a reset link is on its way.');
        $this->assertSame(1, User::query()->count());
    }

    public function test_guests_are_sent_to_login_for_the_account(): void
    {
        StoreFixture::basics();

        $this->get('/account')->assertRedirect(route('login'));
    }
}
