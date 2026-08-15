<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        //PERMISOS
        $permissions = [
            // Vehicles
            'vehicles.view',
            'vehicles.create',
            'vehicles.edit',
            'vehicles.delete',
            'vehicles.import',

            // Workshop Orders
            'workshop_orders.view',
            'workshop_orders.create',
            'workshop_orders.edit',
            'workshop_orders.delete',

            // Alerts
            'alerts.view',
            'alerts.run_action',
            'alerts.resolve',
            'alerts.dismiss',

            //Clients
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',

            //Invoices
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',

            //Companies
            'companies.view',
            'companies.create',
            'companies.edit',
            'companies.delete',

            //Providers
            'providers.view',
            'providers.create',
            'providers.edit',
            'providers.delete',

            //Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            //Roles
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            //Permissions
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        //ROLES

        // SuperAdmin (todo)
        $admin = Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        // Admin (casi todo menos delete)
        $manager = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'vehicles.view',
            'vehicles.create',
            'vehicles.edit',
            'vehicles.import',
        ]);

    }
}