<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Tazas', 'Camisas', 'Gorras', 'Cuadros', 'Servicios de diseño'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
