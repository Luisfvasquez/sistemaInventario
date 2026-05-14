<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
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
        Route::resource('clients', ClientController::class)->names('admin.clients');
        Route::resource('admins', AdminController::class)->names('admin.admins');
        Route::resource('categories', CategoryController::class)->names('admin.categories');
        Route::resource('suppliers', SupplierController::class)->names('admin.suppliers');
        Route::resource('inventories', InventoryController::class)->names('admin.inventories');
        Route::resource('purchases', InventoryController::class)->names('admin.purchases');
        Route::resource('orders', InventoryController::class)->names('admin.orders');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
