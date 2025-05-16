<?php

namespace Database\Seeders;

use App\Models\ExamType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExamTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExamType::create([
            'name' => 'Catégorie B - Épreuve Théorique',
            'pdf_reference' => 'cat_b_theorique.pdf',
            'description' => 'Examen théorique pour le permis de conduire catégorie B (voiture)',
        ]);

        ExamType::create([
            'name' => 'Catégorie B - Épreuve Pratique',
            'pdf_reference' => 'cat_b_pratique.pdf',
            'description' => 'Examen pratique pour le permis de conduire catégorie B (voiture)',
        ]);

        ExamType::create([
            'name' => 'Catégorie C - Épreuve Hors Circulation',
            'pdf_reference' => 'cat_c_hors_circulation.pdf',
            'description' => 'Examen hors circulation pour le permis de conduire catégorie C (poids lourd)',
        ]);

        ExamType::create([
            'name' => 'Catégorie C - Épreuve En Circulation',
            'pdf_reference' => 'cat_c_en_circulation.pdf',
            'description' => 'Examen en circulation pour le permis de conduire catégorie C (poids lourd)',
        ]);
    }
} 