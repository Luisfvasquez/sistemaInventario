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
        Schema::create('bulks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('bulk_type_id')->constrained('bulk_types');

            $table->string('name');

            $table->text('description')->nullable();

            /*
            Cantidad de unidades base.
            Ej:
            Unidad = 1
            Caja = 12
            Bulto = 144
            */
            $table->decimal('quantity', 12, 2);

            $table->decimal('purchase_price', 12, 2)->default(0);

            $table->decimal('sale_price', 12, 2)->default(0);

            $table->string('sku')->nullable();

            $table->string('sku_barcode')->nullable();

            $table->boolean('is_default')->default(false);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulks');
    }
};
