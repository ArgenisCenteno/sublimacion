<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIdByName = Category::pluck('id', 'name');

        $products = [
            [
                'category' => 'Tazas',
                'type' => 'producto',
                'name' => 'Taza sublimada clásica',
                'description' => 'Taza de cerámica blanca de 11oz, ideal para diseños personalizados a todo color.',
                'price_usd' => 8.50,
                'sku' => 'TAZ-001',
                'stock' => 40,
                'featured' => true,
                'variants' => [
                    ['name' => 'Blanca', 'stock' => 25],
                    ['name' => 'Mágica (cambia de color)', 'price_override' => 11.00, 'stock' => 15],
                ],
            ],
            [
                'category' => 'Camisas',
                'type' => 'producto',
                'name' => 'Camisa sublimada deportiva',
                'description' => 'Camisa de poliéster 100% para sublimado full print, corte unisex.',
                'price_usd' => 14.00,
                'sku' => 'CAM-001',
                'stock' => 60,
                'featured' => true,
                'variants' => [
                    ['name' => 'Talla S', 'stock' => 15],
                    ['name' => 'Talla M', 'stock' => 20],
                    ['name' => 'Talla L', 'stock' => 15],
                    ['name' => 'Talla XL', 'stock' => 10],
                ],
            ],
            [
                'category' => 'Gorras',
                'type' => 'producto',
                'name' => 'Gorra sublimada trucker',
                'description' => 'Gorra con panel frontal sublimable y malla trasera.',
                'price_usd' => 9.00,
                'sku' => 'GOR-001',
                'stock' => 30,
                'variants' => [
                    ['name' => 'Negra', 'stock' => 15],
                    ['name' => 'Blanca', 'stock' => 15],
                ],
            ],
            [
                'category' => 'Cuadros',
                'type' => 'producto',
                'name' => 'Cuadro decorativo sublimado',
                'description' => 'Cuadro en MDF de 20x30cm con impresión sublimada de alta definición.',
                'price_usd' => 18.00,
                'sku' => 'CUA-001',
                'stock' => 20,
                'variants' => [],
            ],
            [
                'category' => 'Servicios de diseño',
                'type' => 'servicio',
                'name' => 'Diseño gráfico personalizado',
                'description' => 'Diseño a medida para tus productos sublimados, incluye 2 revisiones.',
                'price_usd' => 15.00,
                'sku' => null,
                'stock' => null,
                'featured' => true,
                'variants' => [
                    ['name' => 'Plan básico (1 diseño)'],
                    ['name' => 'Plan mensual (hasta 5 diseños)', 'price_override' => 55.00],
                ],
            ],
            [
                'category' => 'Servicios de diseño',
                'type' => 'servicio',
                'name' => 'Sublimado por encargo',
                'description' => 'Trae tu propia pieza en blanco y la sublimamos con tu diseño.',
                'price_usd' => 6.00,
                'sku' => null,
                'stock' => null,
                'variants' => [],
            ],
        ];

        foreach ($products as $data) {
            $variants = $data['variants'];
            unset($data['variants']);

            $categoryName = $data['category'];
            unset($data['category']);
            $data['category_id'] = $categoryIdByName[$categoryName] ?? null;

            $product = Product::firstOrCreate(['name' => $data['name']], $data);

            if ($product->variants()->count() === 0) {
                foreach ($variants as $variant) {
                    $product->variants()->create($variant);
                }
            }
        }
    }
}
