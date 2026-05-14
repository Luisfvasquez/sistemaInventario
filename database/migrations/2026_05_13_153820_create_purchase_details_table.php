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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            Si se compró:
            - unidad
            - pack
            - caja
            - bulto
            */
            $table->foreignId('bulk_id')
                ->nullable()
                ->constrained('bulks')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            /*
            Cantidad comercial.
            Ej:
            3 bultos
            2 cajas
            5 unidades
            */
            $table->decimal('quantity', 12, 2);

            /*
            Cantidad REAL convertida
            Ej:
            3 bultos * 24 = 72 unidades
            */
            $table->decimal('base_quantity', 12, 2);

            /*
            Precio por presentación
            */
            $table->decimal('unit_cost', 12, 2);

            /*
            subtotal línea
            */
            $table->decimal('subtotal', 12, 2);

            /*
            Último costo histórico
            */
            $table->decimal('previous_cost', 12, 2)
                ->nullable();

            /*
            Nuevo costo resultante
            */
            $table->decimal('new_cost', 12, 2)
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
