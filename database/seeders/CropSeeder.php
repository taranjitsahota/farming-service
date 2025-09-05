<?php

namespace Database\Seeders;

use App\Models\Crop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CropSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $crops = [
            'Wheat',
            'Rice',
            'Maize',
            'Sugarcane',
            'Cotton',
            'Pulses',
            'Barley',
            'Jowar',
            'Bajra',
            'Ragi',
            'Mustard',
            'Groundnut',
            'Soybean',
            'Tea',
            'Coffee',
            'Rubber',
            'Jute',
            'Banana',
            'Mango',
            'Potato',
            'Onion',
            'Tomato',
            'Chili',
            'Sunflower'
        ];

        foreach ($crops as $crop) {
            Crop::create([
                'name' => $crop,
            ]);
        }
    }
}
