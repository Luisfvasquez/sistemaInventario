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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency_from'); // Ej: 'USD'
            $table->string('currency_to');   // Ej: 'VES' (Bolívares)
            $table->decimal('rate', 10, 4); // 4 decimales para precisión
            $table->date('date');           // Fecha a la que aplica la tasa
            $table->boolean('is_active')->default(true); // Para saber cuál es la tasa de "hoy"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
