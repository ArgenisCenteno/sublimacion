<?php

use App\Models\CashShift;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\GenericCustomerSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(GenericCustomerSeeder::class);

    $this->cashier = User::factory()->create();
    $this->cashier->assignRole('Admin');

    CashShift::create([
        'user_id' => $this->cashier->id,
        'opened_at' => now(),
        'opening_cash_usd' => 0,
        'opening_cash_bs' => 0,
        'status' => 'abierto',
    ]);

    $category = Category::create(['name' => 'Tazas']);
    $this->product = Product::create([
        'category_id' => $category->id,
        'type' => 'producto',
        'name' => 'Taza mágica',
        'price_usd' => 10,
        'stock' => 10,
        'active' => true,
    ]);
});

function saleItems(Product $product): array
{
    return [
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
        'payments' => [['method' => 'efectivo', 'currency' => 'USD', 'amount' => 10]],
    ];
}

test('quick sale does not require customer data', function () {
    $this->actingAs($this->cashier)
        ->post('/pos/sale', ['quick_sale' => true, ...saleItems($this->product)])
        ->assertSessionHasNoErrors();

    $order = Order::latest('id')->first();
    expect($order->customer_name)->toBe('Consumidor final')
        ->and($order->user_id)->toBe(User::where('email', 'venta-rapida@pos.local')->value('id'));
});

test('regular sale still requires customer data', function () {
    $this->actingAs($this->cashier)
        ->post('/pos/sale', ['quick_sale' => false, ...saleItems($this->product)])
        ->assertSessionHasErrors(['customer_name', 'customer_phone', 'customer_email']);
});
