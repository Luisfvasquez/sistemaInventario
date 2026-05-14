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
        Schema::create('accounts_receivable', function (Blueprint $table) {
            $table->id();

            /*
            Orden asociada
            */
            $table->foreignId('order_id')
                ->unique()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            Cliente asociado
            redundancia útil para reportes
            */
            $table->foreignId('client_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            /*
            Totales
            */
            $table->decimal('total_amount', 12, 2);

            $table->decimal('paid_amount', 12, 2)
                ->default(0);

            $table->decimal('pending_amount', 12, 2);

            /*
            Estado financiero
            */
            $table->enum('status', [
                'pending',
                'partial',
                'paid',
                'expired',
                'cancelled',
            ])->default('pending');

            /*
            Fecha límite general
            */
            $table->date('due_date')
                ->nullable();

            /*
            Observaciones
            */
            $table->text('notes')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_receivables');
    }
};
