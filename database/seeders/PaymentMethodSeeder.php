<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            'Efectivo',
            'Zelle',
            'Binance',
            'Tarjeta de crédito/débito',
            'Pago Móvil',
            'Transferencia',
            'Punto de Venta',
        ];

        foreach ($paymentMethods as $method) {
            if ($method == 'Zelle' or $method == 'Binance' or $method == 'Transferencia' or $method == 'Pago Móvil') {
                PaymentMethod::create([
                    'name' => $method,
                    'is_active' => true,
                    'requires_reference' => true,
                ]);
            } else {
                PaymentMethod::create([
                    'name' => $method,
                    'is_active' => true,
                    'requires_reference' => false,
                ]);
            }
        }
    }
}
