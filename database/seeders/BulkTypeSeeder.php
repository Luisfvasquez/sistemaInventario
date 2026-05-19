<?php

namespace Database\Seeders;

use App\Models\BulkType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BulkTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Unidad',
            'Caja',
            'Paquete',
            'Litro',
            'Kilo',
            'Gramo',
            'Bulto',
        ];

        foreach ($types as $type) {
            BulkType::firstOrCreate([
                'slug' => Str::slug($type),
            ], [
                'name' => $type,
                'is_active' => true,
            ]);
        }
    }
}
