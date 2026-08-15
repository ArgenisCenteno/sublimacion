<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {



        // Datos maestros
        $this->call([
            RolePermissionSeeder::class,
            BiocerraLabSeeder::class,

        ]);

        // Usuarios de ejemplo
        $test = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'active' => true,
            ],
        );
        $test->syncRoles(['Admin']);

        $makoto = User::updateOrCreate(
            ['email' => 'makoto@gmail.com'],
            [
                'name' => 'Makoto Shinkai',
                'password' => bcrypt('30138067'),
                'active' => true,
            ],
        );
        $makoto->syncRoles(['SuperAdmin']);
    }
}
