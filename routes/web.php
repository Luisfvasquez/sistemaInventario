<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/index', [AdminController::class, 'index'])->name('admin.index');
        Route::resource('products', ProductController::class)->names('admin.products');
        Route::delete('products/{id}/image', [ProductController::class, 'destroyImage'])->name('admin.products.destroyImage');
        Route::get('forzar-actualizacion-dolar', [ProductController::class, 'forzarActualizacionDolar'])->name('admin.products.forzarActualizacionDolar');
        Route::resource('clients', ClientController::class)->names('admin.clients');
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
        Route::resource('payment_methods', PaymentMethodController::class)->only(['store', 'update', 'destroy'])->names('admin.payment_methods');
    });

Route::middleware('auth')->group(function () {
    Route::get('/client/dashboard', function () {
        return view('client.dashboard');
    })->name('client.dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
