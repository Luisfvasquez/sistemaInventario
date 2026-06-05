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
            // General / Dashboard
            'admin.dashboard',
            'admin.index',

            // Productos
            'admin.products.index',
            'admin.products.create',
            'admin.products.store',
            'admin.products.show',
            'admin.products.edit',
            'admin.products.update',
            'admin.products.destroy',
            'admin.products.destroyImage',
            'admin.products.forzarActualizacionDolar',

            // Clientes
            'admin.clients.index',
            'admin.clients.create',
            'admin.clients.store',
            'admin.clients.show',
            'admin.clients.edit',
            'admin.clients.update',
            'admin.clients.destroy',
            'admin.clients.registerAbono',
            'admin.orders.updateVerification',

            // Administradores
            'admin.admins.index',
            'admin.admins.create',
            'admin.admins.store',
            'admin.admins.show',
            'admin.admins.edit',
            'admin.admins.update',
            'admin.admins.destroy',

            // Categorías
            'admin.categories.index',
            'admin.categories.create',
            'admin.categories.store',
            'admin.categories.show',
            'admin.categories.edit',
            'admin.categories.update',
            'admin.categories.destroy',
            'admin.categories.quickStore',

            // Proveedores
            'admin.suppliers.index',
            'admin.suppliers.create',
            'admin.suppliers.store',
            'admin.suppliers.show',
            'admin.suppliers.edit',
            'admin.suppliers.update',
            'admin.suppliers.destroy',

            // Inventario
            'admin.inventories.index',
            'admin.inventories.create',
            'admin.inventories.store',
            'admin.inventories.show',
            'admin.inventories.edit',
            'admin.inventories.update',
            'admin.inventories.destroy',

            // Compras
            'admin.purchases.index',
            'admin.purchases.create',
            'admin.purchases.store',

            // Ventas / Órdenes
            'admin.orders.index',
            'admin.orders.create',
            'admin.orders.store',
            'admin.orders.show',
            'admin.orders.edit',
            'admin.orders.update',
            'admin.orders.destroy',
            'admin.orders.approve',
            'admin.orders.reject',
            'admin.orders.proof',
            'admin.orders.deliver',

            // Punto de Venta (POS)
            'admin.pos.products.search',
            'admin.pos.clients.search',
            'admin.pos.clients.store',

            // Métodos de Pago
            'admin.payment_methods.store',
            'admin.payment_methods.update',
            'admin.payment_methods.destroy',

            // Configuración (Nuevos Módulos)
            'admin.roles.index',
            'admin.roles.create',
            'admin.roles.store',
            'admin.roles.show',
            'admin.roles.edit',
            'admin.roles.update',
            'admin.roles.destroy',
            'admin.users-roles.index',
            'admin.users-roles.update',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        $clientRole = Role::firstOrCreate([
            'name' => 'client',
            'guard_name' => 'web'
        ]);

        $sellerRole = Role::firstOrCreate([
            'name' => 'seller',
            'guard_name' => 'web'
        ]);

        $warehouseRole = Role::firstOrCreate([
            'name' => 'warehouse',
            'guard_name' => 'web'
        ]);

        // Admin gets all permissions
        $adminRole->syncPermissions($permissions);

        // Seller gets basic POS, Sales, and Client access
        $sellerRole->syncPermissions([
            'admin.dashboard',
            'admin.orders.index',
            'admin.orders.create',
            'admin.orders.store',
            'admin.orders.show',
            'admin.pos.products.search',
            'admin.pos.clients.search',
            'admin.pos.clients.store',
            'admin.clients.index',
            'admin.clients.create',
            'admin.clients.store',
            'admin.clients.show',
        ]);

        // Warehouse gets Inventories, Products and Purchases access
        $warehouseRole->syncPermissions([
            'admin.dashboard',
            'admin.inventories.index',
            'admin.inventories.show',
            'admin.inventories.edit',
            'admin.inventories.update',
            'admin.products.index',
            'admin.products.show',
            'admin.purchases.index',
            'admin.purchases.create',
            'admin.purchases.store',
        ]);
    }
}
