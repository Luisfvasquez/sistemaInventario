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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('type', [
                'purchase',
                'sale',
                'adjustment',
                'return',
                'loss',
                'transfer',
            ]);

            /*
            polymorphic:
            Purchase
            Order
            ManualAdjustment
            etc
            */
            $table->string('reference_type');

            $table->unsignedBigInteger('reference_id');

            $table->decimal('quantity', 12, 2);

            $table->decimal('previous_stock', 12, 2);

            $table->decimal('new_stock', 12, 2);

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
