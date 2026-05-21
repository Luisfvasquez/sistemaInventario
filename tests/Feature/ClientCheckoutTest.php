<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\ExchangeRate;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\AccountReceivable;
use App\Models\InventoryMovement;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\CategorySeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    // Seed roles and categories
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(CategorySeeder::class);

    // Create exchange rate
    ExchangeRate::create([
        'currency_from' => 'USD',
        'currency_to' => 'VES',
        'rate' => 40.0000,
        'date' => now(),
        'is_active' => true,
    ]);
});

test('unauthenticated user cannot access checkout', function () {
    $response = $this->post(route('client.checkout'), [
        'delivery_address' => 'Test Address',
        'notes' => 'Test Notes',
        'cart_items' => json_encode([]),
    ]);

    $response->assertRedirect('/login');
});

test('non-client role user cannot access checkout', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)->post(route('client.checkout'), [
        'delivery_address' => 'Test Address',
        'notes' => 'Test Notes',
        'cart_items' => json_encode([]),
    ]);

    $response->assertStatus(403);
});

test('client can checkout successfully with unit-based products', function () {
    $user = User::factory()->create();
    $user->assignRole('client');

    $category = Category::first();

    // Create product: unit-based, tracked, 10 units in stock
    $product = Product::create([
        'category_id' => $category->id,
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'name' => 'Unit Product',
        'slug' => 'unit-product',
        'sku' => 'UNIT-001',
        'sku_barcode' => '1234567890123',
        'price' => 5.00, // base price
        'cost' => 3.00,
        'unit_type' => 'unit',
        'track_inventory' => true,
        'allow_negative_stock' => false,
        'status' => 'active',
    ]);

    $inventory = Inventory::create([
        'product_id' => $product->id,
        'stock' => 10.00,
    ]);

    $cartData = [
        [
            'id' => $product->id,
            'quantity' => 3, // request 3 units
        ]
    ];

    $response = $this->actingAs($user)->post(route('client.checkout'), [
        'delivery_address' => 'Av. Bolívar, Local 5',
        'notes' => 'Llamar al llegar',
        'cart_items' => json_encode($cartData),
    ]);

    // Should redirect to purchases history with success message
    $response->assertRedirect(route('client.purchases'));
    $response->assertSessionHasNoErrors();

    // Assert Order was created
    $order = Order::first();
    expect($order)->not->toBeNull();
    expect((float) $order->total)->toEqual(15.00); // 3 * 5.00
    expect((float) $order->exchange_rate)->toEqual(40.0);
    expect($order->payment_status)->toEqual('pending');

    // Assert OrderDetail was created
    $detail = OrderDetail::first();
    expect($detail)->not->toBeNull();
    expect($detail->product_id)->toEqual($product->id);
    expect((float) $detail->quantity)->toEqual(3.0);
    expect((float) $detail->base_quantity)->toEqual(3.0);
    expect((float) $detail->unit_price)->toEqual(5.00); // display_price = price for unit
    expect((float) $detail->unit_cost)->toEqual(3.00); // display_cost = cost for unit
    expect((float) $detail->subtotal)->toEqual(15.00);

    // Assert stock was decremented: 10 - 3 = 7
    $inventory->refresh();
    expect((float) $inventory->stock)->toEqual(7.00);

    // Assert AccountReceivable was created (due in 7 days)
    $receivable = AccountReceivable::first();
    expect($receivable)->not->toBeNull();
    expect($receivable->order_id)->toEqual($order->id);
    expect((float) $receivable->total_amount)->toEqual(15.00);
    expect((float) $receivable->pending_amount)->toEqual(15.00);
    expect($receivable->due_date->toDateString())->toEqual(now()->addDays(7)->toDateString());

    // Assert InventoryMovement was created
    $movement = InventoryMovement::first();
    expect($movement)->not->toBeNull();
    expect($movement->product_id)->toEqual($product->id);
    expect($movement->type)->toEqual('sale');
    expect((float) $movement->quantity)->toEqual(3.0);
    expect((float) $movement->previous_stock)->toEqual(10.0);
    expect((float) $movement->new_stock)->toEqual(7.0);
});

test('client can checkout successfully with weight-based (gram) products', function () {
    $user = User::factory()->create();
    $user->assignRole('client');

    $category = Category::first();

    // Create product: weight-based (gram), price is stored per gram.
    // e.g. display_price = price * 1000 = $12.00 per Kg. So price in DB is 12 / 1000 = 0.012
    $product = Product::create([
        'category_id' => $category->id,
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'name' => 'Weighted Product',
        'slug' => 'weighted-product',
        'sku' => 'WGHT-001',
        'sku_barcode' => '3210987654321',
        'price' => 0.012, // $0.012 per gram ($12 per Kg)
        'cost' => 0.008, // $8 per Kg
        'unit_type' => 'gram',
        'track_inventory' => true,
        'allow_negative_stock' => false,
        'status' => 'active',
    ]);

    // 5000 grams in stock (5 Kg)
    $inventory = Inventory::create([
        'product_id' => $product->id,
        'stock' => 5000.00,
    ]);

    $cartData = [
        [
            'id' => $product->id,
            'quantity' => 0.250, // request 0.250 Kgs (250 grams)
        ]
    ];

    $response = $this->actingAs($user)->post(route('client.checkout'), [
        'delivery_address' => 'Calle Falsa 123',
        'notes' => 'Dejar en consejería',
        'cart_items' => json_encode($cartData),
    ]);

    $response->assertRedirect(route('client.purchases'));
    $response->assertSessionHasNoErrors();

    // Assert Order was created
    $order = Order::first();
    expect($order)->not->toBeNull();
    // Subtotal: 0.250 * 1000 = 250 grams. 250 * 0.012 = 3.00 USD
    expect((float) $order->total)->toEqual(3.00);

    // Assert OrderDetail was created
    $detail = OrderDetail::first();
    expect($detail)->not->toBeNull();
    expect($detail->product_id)->toEqual($product->id);
    expect((float) $detail->quantity)->toEqual(0.250);
    expect((float) $detail->base_quantity)->toEqual(250.0); // 250 grams
    expect((float) $detail->unit_price)->toEqual(12.00); // display price per Kg
    expect((float) $detail->unit_cost)->toEqual(8.00); // display cost per Kg
    expect((float) $detail->subtotal)->toEqual(3.00);

    // Assert stock was decremented correctly: 5000 - 250 = 4750 grams
    $inventory->refresh();
    expect((float) $inventory->stock)->toEqual(4750.00);

    // Assert InventoryMovement was created with base quantities
    $movement = InventoryMovement::first();
    expect($movement)->not->toBeNull();
    expect($movement->product_id)->toEqual($product->id);
    expect($movement->type)->toEqual('sale');
    expect((float) $movement->quantity)->toEqual(250.0);
    expect((float) $movement->previous_stock)->toEqual(5000.0);
    expect((float) $movement->new_stock)->toEqual(4750.0);
});

test('client checkout fails and rolls back when quantity exceeds available stock', function () {
    $user = User::factory()->create();
    $user->assignRole('client');

    $category = Category::first();

    // Product 1: unit-based, 5 in stock
    $product1 = Product::create([
        'category_id' => $category->id,
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'name' => 'Prod 1',
        'slug' => 'prod-1',
        'sku' => 'P1',
        'sku_barcode' => '1111111111111',
        'price' => 2.00,
        'cost' => 1.00,
        'unit_type' => 'unit',
        'track_inventory' => true,
        'allow_negative_stock' => false,
        'status' => 'active',
    ]);
    $inv1 = Inventory::create([
        'product_id' => $product1->id,
        'stock' => 5.00,
    ]);

    // Product 2: weight-based, 2000g (2 Kg) in stock
    $product2 = Product::create([
        'category_id' => $category->id,
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'name' => 'Prod 2',
        'slug' => 'prod-2',
        'sku' => 'P2',
        'sku_barcode' => '2222222222222',
        'price' => 0.010, // $10 per Kg
        'cost' => 0.005,
        'unit_type' => 'gram',
        'track_inventory' => true,
        'allow_negative_stock' => false,
        'status' => 'active',
    ]);
    $inv2 = Inventory::create([
        'product_id' => $product2->id,
        'stock' => 2000.00,
    ]);

    // Requesting more than available for Product 2 (e.g. 2.5 Kg = 2500g, but only 2000g available)
    $cartData = [
        [
            'id' => $product1->id,
            'quantity' => 2, // Valid (2 <= 5)
        ],
        [
            'id' => $product2->id,
            'quantity' => 2.5, // Invalid (2500 > 2000)
        ]
    ];

    $response = $this->actingAs($user)->post(route('client.checkout'), [
        'delivery_address' => 'Direccion de prueba',
        'notes' => '',
        'cart_items' => json_encode($cartData),
    ]);

    // Assert it fails and returns with session error
    $response->assertSessionHasErrors(['error']);
    
    // Assert no orders were created
    expect(Order::count())->toEqual(0);
    expect(OrderDetail::count())->toEqual(0);
    expect(AccountReceivable::count())->toEqual(0);
    expect(InventoryMovement::count())->toEqual(0);

    // Assert stock remains untouched (rollback verified)
    expect((float) $inv1->refresh()->stock)->toEqual(5.00);
    expect((float) $inv2->refresh()->stock)->toEqual(2000.00);
});
