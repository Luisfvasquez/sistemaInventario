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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            Unidad / caja / bulto / etc
            */
            $table->foreignId('bulk_id')
                ->nullable()
                ->constrained('bulks')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            /*
            Cantidad comercial
            */
            $table->decimal('quantity', 12, 2);

            /*
            Cantidad REAL inventario
            */
            $table->decimal('base_quantity', 12, 2);

            /*
            Precio vendido
            */
            $table->decimal('unit_price', 12, 2);

            /*
            costo histórico
            */
            $table->decimal('unit_cost', 12, 2)
                ->nullable();

            /*
            subtotal
            */
            $table->decimal('subtotal', 12, 2);

            /*
            descuento línea
            */
            $table->decimal('discount', 12, 2)
                ->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
