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
        Schema::create('payment_installments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_receivable_id')
                ->constrained('accounts_receivable')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            Cuota #
            */
            $table->integer('installment_number');

            /*
            Monto cuota
            */
            $table->decimal('amount', 12, 2);

            /*
            Monto pagado
            */
            $table->decimal('paid_amount', 12, 2)
                ->default(0);

            /*
            Pendiente
            */
            $table->decimal('pending_amount', 12, 2);

            /*
            Vencimiento
            */
            $table->date('due_date');

            /*
            Fecha pago completo
            */
            $table->timestamp('paid_at')
                ->nullable();

            /*
            Estado cuota
            */
            $table->enum('status', [
                'pending',
                'partial',
                'paid',
                'late',
                'cancelled',
            ])->default('pending');

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
        Schema::dropIfExists('payment_installments');
    }
};
