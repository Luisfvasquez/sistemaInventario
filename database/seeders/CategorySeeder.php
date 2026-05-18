<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Bebidas',
            'Alimentos',
            'Limpieza',
            'Lacteos',
            'Snacks',
            'Otros',
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate([
                'slug' => Str::slug($category),
            ], [
                'name' => $category,
                'is_active' => true,
            ]);
        }
    }
}
