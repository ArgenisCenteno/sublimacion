<?php

use App\Models\Payment;
use App\Models\StoreSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can update the dollar rate from the navbar endpoint', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin)
        ->from('/dashboard')
        ->put('/settings/exchange-rate', ['exchange_rate_bs' => 36.5])
        ->assertRedirect('/dashboard')
        ->assertSessionHasNoErrors();

    expect((float) StoreSetting::current()->exchange_rate_bs)->toBe(36.5);

    $this->get('/')->assertInertia(fn ($page) => $page->where('exchangeRate.bs', 36.5));
});

test('users without permission cannot update the rate', function () {
    $user = User::factory()->create();
    $user->assignRole('Cliente');

    $this->actingAs($user)
        ->put('/settings/exchange-rate', ['exchange_rate_bs' => 99])
        ->assertForbidden();
});

test('bolivar payments are converted with the current rate', function () {
    StoreSetting::current()->update(['exchange_rate_bs' => 40]);

    expect(Payment::computeAmounts('pago_movil', 'BS', 400))
        ->toMatchArray(['amount_usd' => 10.0, 'amount_bs' => 400.0]);
});
