<?php

namespace App\Console\Commands;

use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UpdateExchangeRate extends Command
{
    // El nombre del comando para ejecutarlo en consola
    protected $signature = 'exchange:update-usd';

    // Descripción del comando
    protected $description = 'Consulta la API de dolarapi.com, actualiza la base de datos y la caché';

    public function handle()
    {
        try {
            // Hacemos la petición a la API con un timeout de 10 segundos
            $response = Http::timeout(10)->get('https://ve.dolarapi.com/v1/cotizaciones');

            if ($response->successful()) {
                $data = $response->json();

                // Usamos colecciones de Laravel para buscar el objeto donde moneda es "USD"
                $usdData = collect($data)->firstWhere('moneda', 'USD');

                if ($usdData && isset($usdData['promedio'])) {
                    $rate = $usdData['promedio'];
                    $date = Carbon::parse($usdData['fechaActualizacion'])->format('Y-m-d');

                    // Usamos una transacción para que, si algo falla, no se altere la BD
                    DB::transaction(function () use ($rate, $date) {
                        // 1. Desactivar la tasa actual
                        ExchangeRate::where('is_active', true)->update(['is_active' => false]);

                        // 2. Crear la nueva tasa
                        $newRate = ExchangeRate::create([
                            'currency_from' => 'USD',
                            'currency_to' => 'BS',
                            'rate' => $rate,
                            'date' => $date,
                            'is_active' => true,
                        ]);

                        // 3. Guardar en Caché permanentemente (hasta que este comando lo vuelva a sobreescribir)
                        Cache::forever('exchange_rate', $newRate->rate);
                    });

                    $this->info("¡Éxito! Tasa actualizada y en caché: {$rate} Bs/USD");

                    return Command::SUCCESS;
                }
            }

            $this->error('La API respondió, pero no se encontró la moneda USD.');

            return Command::FAILURE;

        } catch (\Exception $e) {
            $this->error('Error de conexión o guardado: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
