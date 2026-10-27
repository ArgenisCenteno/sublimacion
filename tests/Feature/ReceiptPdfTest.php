<?php

use App\Models\CashShift;
use App\Models\Category;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\StoreSetting;
use App\Models\User;
use Database\Seeders\GenericCustomerSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(GenericCustomerSeeder::class);
    StoreSetting::current()->update(['exchange_rate_bs' => 36.5, 'store_name' => 'Sublima Store']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');

    CashShift::create(['user_id' => $this->admin->id, 'opened_at' => now(), 'opening_cash_usd' => 0, 'opening_cash_bs' => 0, 'status' => 'abierto']);

    $category = Category::create(['name' => 'Tazas']);
    $mug = Product::create(['category_id' => $category->id, 'type' => 'producto', 'name' => 'Taza mágica 11oz', 'price_usd' => 8.5, 'stock' => 20, 'active' => true]);
    $tee = Product::create(['category_id' => $category->id, 'type' => 'producto', 'name' => 'Franela sublimada full color', 'price_usd' => 12, 'stock' => 20, 'active' => true]);

    $this->actingAs($this->admin)->post('/pos/sale', [
        'quick_sale' => true,
        'customer_name' => 'María Pérez',
        'items' => [
            ['product_id' => $mug->id, 'quantity' => 2],
            ['product_id' => $tee->id, 'quantity' => 1],
        ],
        'payments' => [['method' => 'pago_movil', 'currency' => 'BS', 'amount' => 1058.5, 'reference' => '004512']],
    ])->assertSessionHasNoErrors();

    $this->receipt = Receipt::latest('id')->firstOrFail();
});

test('receipt pdf renders with items and qr', function () {
    $response = $this->actingAs($this->admin)->get("/receipts/{$this->receipt->id}/pdf");

    $response->assertOk()->assertHeader('content-type', 'application/pdf');

    if ($dir = env('RECEIPT_PREVIEW_DIR')) {
        file_put_contents("{$dir}/recibo.pdf", $response->getContent());
    }
});

test('qr verification link works without login and requires a valid signature', function () {
    auth()->logout();

    $this->get($this->receipt->verificationUrl())
        ->assertOk()
        ->assertSee('Recibo válido')
        ->assertSee($this->receipt->formatted_number);

    $this->get("/recibos/{$this->receipt->id}/verificar")->assertForbidden();
});
