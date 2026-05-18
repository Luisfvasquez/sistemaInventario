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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            $table->string('uuid')
                ->unique();

            $table->foreignId('supplier_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            Usuario que registró la compra
            */
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            Código interno
            Ej:
            COMP-000001
            */
            $table->string('purchase_code')
                ->unique();

            /*
            Totales monetarios
            */
            $table->decimal('subtotal', 12, 2)
                ->default(0);

            $table->decimal('tax', 12, 2)
                ->default(0);

            $table->decimal('discount', 12, 2)
                ->default(0);

            $table->decimal('total', 12, 2)
                ->default(0);

            /*
            Tasa de cambio
            */
            $table->decimal('exchange_rate', 10, 4)
                ->nullable();

            /*
            Estado
            */
            $table->enum('status', [
                'draft',
                'pending',
                'completed',
                'cancelled',
            ])->default('draft');

            /*
            Fecha real de compra
            */
            $table->timestamp('purchased_at')
                ->nullable();

            /*
            Observaciones
            */
            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
