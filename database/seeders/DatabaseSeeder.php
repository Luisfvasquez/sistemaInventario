<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RolesAndPermissionsSeeder::class,
            BulkTypeSeeder::class,
            CategorySeeder::class,
        ]);

        $user = User::create([
            'dni' => '29873955',
            'name' => 'Luis',
            'last_name' => 'Vasquez',
            'phone_number' => '04145018145',
            'email' => 'wueyluis@gmail.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $user->assignRole('admin');

        $user = User::create([
            'dni' => '0',
            'name' => 'Venta sin cliente',
            'last_name' => 'sistema',
            'phone_number' => '0000000000',
            'email' => 'inventario@gmail.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);


        $user->assignRole('client');
    }
}
