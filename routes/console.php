<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Ejecutar a las 7:15 AM y a la 1:15 PM (Le damos 15 min de margen por si la API de BCV se retrasa en actualizar)
// schedule()->command('exchange:update-usd')
//     ->twiceDaily(7, 13)
//     ->timezone('America/Caracas');

Schedule::command('exchange:update-usd')
    ->dailyAt('7:05') // 7:05 AM
    ->timezone('America/Caracas')
    ->withoutOverlapping()
    ->onSuccess(function () {
        Log::info('Tasa de cambio actualizada correctamente');
    })
    ->onFailure(function () {
        Log::info('No se pudo actualizar la tasa de cambio');
    });

Schedule::command('exchange:update-usd')
    ->dailyAt('14:05') // 2:05 PM
    ->timezone('America/Caracas')
    ->withoutOverlapping()
    ->onSuccess(function () {
        Log::info('Tasa de cambio actualizada correctamente');
    })
    ->onFailure(function () {
        Log::info('No se pudo actualizar la tasa de cambio');
    });
