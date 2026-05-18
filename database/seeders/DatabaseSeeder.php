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
            'dni' => '0000000000',
            'name' => 'Admin',
            'last_name' => 'System',
            'phone_number' => '0000000000',
            'email' => 'lvasquez@iwan.cl',
            'password' => bcrypt('12345678'),
            'is_active' => true,
        ]);

        $user->assignRole('admin');
    }
}
