<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('uuid')->unique();

            $table->string('name');

            $table->string('slug')->unique();

            $table->text('description')->nullable();

            $table->string('sku')->unique();

            $table->string('sku_barcode')->unique();

            $table->string('brand')->nullable();

            $table->decimal('cost', 12, 2)->default(0);

            $table->decimal('price', 12, 2)->default(0);

            $table->boolean('track_inventory')->default(true);

            $table->boolean('allow_negative_stock')->default(false);

            $table->boolean('has_variants')->default(false);

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
