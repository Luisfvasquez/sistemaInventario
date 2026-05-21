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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('uuid')
                ->unique();

            /*
            Cliente opcional
            */
            $table->foreignId('client_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            /*
            Usuario que verificó
            */
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            /*
            Número interno
            */
            $table->string('order_number')
                ->unique();

            /*
            Pickup o delivery
            */
            $table->enum('order_type', [
                'store',
                'store_pickup',
                'delivery',
            ])->default('store_pickup');

            /*
            Estado pago
            */
            $table->enum('payment_status', [
                'pending',
                'partial',
                'paid',
            ])->default('pending');

            /*
            Estado verificación
            */
            $table->enum('verification_status', [
                'pending',
                'verified',
                'rejected',
            ])->default('pending');

            /*
            Estado general
            */
            $table->enum('status', [
                'pending',
                'processing',
                'ready_for_pickup',
                'completed',
                'delivered',
                'cancelled',
            ])->default('pending');

            /*
            Datos rápidos invitados
            */
            $table->string('client_name')
                ->nullable();

            $table->string('client_phone')
                ->nullable();

            $table->text('delivery_address')
                ->nullable();

            /*
            Totales
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
            Observaciones
            */
            $table->text('notes')
                ->nullable();

            /*
            Fecha verificación
            */
            $table->timestamp('verified_at')
                ->nullable();

            /*
            Fecha entrega
            */
            $table->timestamp('delivered_at')
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
        Schema::dropIfExists('orders');
    }
};
