<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $cities = [
            ['city' => 'Port Blair', 'state_id' => 1, 'lat' => 11.6683, 'lng' => 92.7378],
            ['city' => 'Diglipur', 'state_id' => 1, 'lat' => 13.2667, 'lng' => 93],
            // Add all the other cities here
        ];

        DB::table('cities')->insert($cities);
    }
}
