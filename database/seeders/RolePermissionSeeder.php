<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos base del panel admin. Los permisos de catalogo, pedidos,
        // pagos, POS y reportes se agregan en sus respectivas fases.
        $permissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',

            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',

            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            'bank_deposits.view',
            'bank_deposits.create',
            'bank_deposits.edit',
            'bank_deposits.delete',

            'payments.view',
            'payments.create',
            'payments.verify',

            'settings.edit',

            'receipts.view',
            'receipts.annul',

            'orders.view',
            'orders.manage',

            'pos.vender',
            'cash_shifts.view',
            'cash_shifts.view_all',
            'cash_shifts.reopen',

            'reportes.ver',
            'reportes.financieros.ver',

            'activity_log.view',
            'users.impersonate',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Admin: acceso total al panel.
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        // Empleado: gestiona catalogo (productos/servicios), sin gestionar empleados/roles/permisos.
        $empleado = Role::firstOrCreate(['name' => 'Empleado', 'guard_name' => 'web']);
        $empleado->syncPermissions([
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'bank_deposits.view',
            'bank_deposits.create',
            'bank_deposits.edit',
            'bank_deposits.delete',
            'payments.view',
            'payments.create',
            'payments.verify',
            'receipts.view',
            'orders.view',
            'orders.manage',
            'pos.vender',
            'cash_shifts.view',
            'reportes.ver',
        ]);

        // Cliente: sin acceso al panel admin.
        Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
    }
}
