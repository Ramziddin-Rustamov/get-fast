<?php

namespace Database\Seeders;

use App\Models\V1\ParcelType;
use Illuminate\Database\Seeder;

class ParcelTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name_uz' => 'Hujjat / konvert',
                'name_ru' => 'Документы / конверт',
                'name_en' => 'Documents / envelope',
                'icon' => 'document',
            ],
            [
                'name_uz' => 'Kichik quti',
                'name_ru' => 'Маленькая коробка',
                'name_en' => 'Small box',
                'icon' => 'box_small',
            ],
            [
                'name_uz' => "O'rta quti",
                'name_ru' => 'Средняя коробка',
                'name_en' => 'Medium box',
                'icon' => 'box_medium',
            ],
            [
                'name_uz' => 'Katta yuk',
                'name_ru' => 'Крупный груз',
                'name_en' => 'Large cargo',
                'icon' => 'box_large',
            ],
            [
                'name_uz' => 'Oziq-ovqat',
                'name_ru' => 'Продукты питания',
                'name_en' => 'Food',
                'icon' => 'food',
            ],
            [
                'name_uz' => 'Dori-darmon',
                'name_ru' => 'Лекарства',
                'name_en' => 'Medicine',
                'icon' => 'medicine',
            ],
        ];

        foreach ($types as $type) {
            ParcelType::updateOrCreate(
                ['name_en' => $type['name_en']],
                $type + ['is_active' => true]
            );
        }
    }
}
