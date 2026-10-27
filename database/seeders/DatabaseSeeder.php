<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Datos maestros
        $this->call([
            RolePermissionSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            GenericCustomerSeeder::class,
        ]);

        // Usuarios de ejemplo
        $admin = User::updateOrCreate(
            ['email' => 'admin@sublimacion.test'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'active' => true,
            ],
        );
        $admin->syncRoles(['Admin']);

        $empleado = User::updateOrCreate(
            ['email' => 'empleado@sublimacion.test'],
            [
                'name' => 'Empleado',
                'password' => bcrypt('password'),
                'active' => true,
            ],
        );
        $empleado->syncRoles(['Empleado']);

        $cliente = User::updateOrCreate(
            ['email' => 'cliente@sublimacion.test'],
            [
                'name' => 'Cliente',
                'password' => bcrypt('password'),
                'active' => true,
            ],
        );
        $cliente->syncRoles(['Cliente']);
    }
}
