<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkshopChecklistTemplateSeeder extends Seeder
{
    /**
     * Seed the master checklist templates used by workshop flows.
     */
    public function run(): void
    {
        $templates = [
            [
                'type' => 'dab',
                'label' => 'Arranque del motor y ausencia de testigos de averia',
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'type' => 'dab',
                'label' => 'Funcionamiento correcto de luces y senalizacion',
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'type' => 'dab',
                'label' => 'Estado general de neumaticos',
                'sort_order' => 3,
                'active' => true,
            ],
            [
                'type' => 'dab',
                'label' => 'Revision de niveles basicos y ausencia de fugas visibles',
                'sort_order' => 4,
                'active' => true,
            ],
            [
                'type' => 'dab',
                'label' => 'Comprobacion de frenos y direccion en maniobra basica',
                'sort_order' => 5,
                'active' => true,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('workshop_checklist_templates')->updateOrInsert(
                [
                    'type' => $template['type'],
                    'label' => $template['label'],
                ],
                [
                    'sort_order' => $template['sort_order'],
                    'active' => $template['active'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }
}
