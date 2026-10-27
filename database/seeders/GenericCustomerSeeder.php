<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenericCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'venta-rapida@pos.local'],
            [
                'name' => 'Cliente Genérico (Venta Rápida)',
                'password' => bcrypt(Str::random(40)),
                'active' => true,
            ],
        );

        $user->syncRoles(['Cliente']);
    }
}
