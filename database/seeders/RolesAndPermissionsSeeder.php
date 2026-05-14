<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',

            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            'purchases.view',
            'purchases.create',

            'orders.view',
            'orders.create',

            'clients.view',
            'suppliers.view',

            'inventory.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
            ]);
        }

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
        ]);

        $clientRole = Role::firstOrCreate([
            'name' => 'client',
        ]);

        $adminRole->syncPermissions($permissions);

        Role::firstOrCreate([
            'name' => 'seller',
        ]);

        Role::firstOrCreate([
            'name' => 'warehouse',
        ]);
    }
}
