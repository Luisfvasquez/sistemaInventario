<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPanelController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ClientPanelController::class, 'storefront'])->name('storefront');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/index', [AdminController::class, 'index'])->name('admin.index');
        Route::resource('products', ProductController::class)->names('admin.products');
        Route::delete('products/{id}/image', [ProductController::class, 'destroyImage'])->name('admin.products.destroyImage');
        Route::get('forzar-actualizacion-dolar', [ProductController::class, 'forzarActualizacionDolar'])->name('admin.products.forzarActualizacionDolar');
        Route::resource('clients', ClientController::class)->names('admin.clients');
        Route::post('clients/{client}/abonos', [ClientController::class, 'registerAbono'])
            ->name('admin.clients.registerAbono');
        Route::patch('orders/{order}/verification', [ClientController::class, 'updateOrderVerification'])
            ->name('admin.orders.updateVerification');
        Route::resource('admins', AdminController::class)->names('admin.admins');
        Route::resource('categories', CategoryController::class)->names('admin.categories');
        Route::post('categories/quick-store', [CategoryController::class, 'quickStore'])
            ->name('admin.categories.quickStore');
        Route::resource('suppliers', SupplierController::class)->names('admin.suppliers');
        Route::resource('inventories', InventoryController::class)->names('admin.inventories');
        Route::get('purchases/index', [PurchaseController::class, 'index'])->name('admin.purchases.index');
        Route::get('purchases/create', [PurchaseController::class, 'create'])->name('admin.purchases.create');
        Route::post('purchases', [PurchaseController::class, 'store'])->name('admin.purchases.store');
        Route::resource('orders', OrderController::class)->names('admin.orders');

        // Rutas API para el Punto de Venta (POS)
        Route::get('pos/products/search', [OrderController::class, 'searchProduct'])->name('admin.pos.products.search');
        Route::get('pos/clients/search', [OrderController::class, 'searchClient'])->name('admin.pos.clients.search');
        Route::post('pos/clients/quick-store', [OrderController::class, 'storeClient'])->name('admin.pos.clients.store');

        Route::resource('payment_methods', PaymentMethodController::class)->only(['store', 'update', 'destroy'])->names('admin.payment_methods');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas exclusivas del panel de cliente
Route::middleware(['auth', 'role:client'])
    ->prefix('client')
    ->group(function () {
        Route::get('/dashboard', [ClientPanelController::class, 'dashboard'])->name('client.dashboard');
        Route::get('/productos', [ClientPanelController::class, 'products'])->name('client.products');
        Route::post('/checkout', [ClientPanelController::class, 'checkout'])->name('client.checkout');
        Route::get('/compras', [ClientPanelController::class, 'purchases'])->name('client.purchases');
        Route::get('/facturas', [ClientPanelController::class, 'invoices'])->name('client.invoices');
        Route::get('/perfil', [ClientPanelController::class, 'profile'])->name('client.profile');
        Route::patch('/perfil', [ClientPanelController::class, 'profileUpdate'])->name('client.profile.update');
    });

require __DIR__.'/auth.php';
