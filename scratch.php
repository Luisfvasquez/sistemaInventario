<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Inventory;

$search = 'a';
$searchTerm = '%'.trim($search).'%';

$inventories = Inventory::with(['product.category'])
    ->when($search, function ($query) use ($searchTerm) {
        $query->whereHas('product', function ($q) use ($searchTerm) {
            $q->where(function ($subQ) use ($searchTerm) {
                $subQ->where('name', 'like', $searchTerm)
                     ->orWhere('sku', 'like', $searchTerm);
            });
        });
    })
    ->get();

echo "Count with whereHas: " . $inventories->count() . "\n";

$inventoriesJoined = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
    ->when($search, function ($query) use ($searchTerm) {
        $query->where(function ($subQ) use ($searchTerm) {
            $subQ->where('products.name', 'like', $searchTerm)
                 ->orWhere('products.sku', 'like', $searchTerm);
        });
    })
    ->get();

echo "Count with join: " . $inventoriesJoined->count() . "\n";

