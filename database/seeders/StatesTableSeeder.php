<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class StatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $states = [
            ['name' => 'Andaman and Nicobar Islands', 'country_id' => 1],
            ['name' => 'Andhra Pradesh', 'country_id' => 1],
            ['name' => 'Arunachal Pradesh', 'country_id' => 1],
            ['name' => 'Assam', 'country_id' => 1],
            ['name' => 'Bihar', 'country_id' => 1],
            ['name' => 'Chandigarh', 'country_id' => 1],
            ['name' => 'Chhattisgarh', 'country_id' => 1],
            ['name' => 'Dadra and Nagar Haveli and Daman and Diu', 'country_id' => 1],
            ['name' => 'Delhi', 'country_id' => 1],
            ['name' => 'Goa', 'country_id' => 1],
            ['name' => 'Gujarat', 'country_id' => 1],
            ['name' => 'Haryana', 'country_id' => 1],
            ['name' => 'Himachal Pradesh', 'country_id' => 1],
            ['name' => 'Jammu and Kashmir', 'country_id' => 1],
            ['name' => 'Jharkhand', 'country_id' => 1],
            ['name' => 'Karnataka', 'country_id' => 1],
            ['name' => 'Kerala', 'country_id' => 1],
            ['name' => 'Lakshadweep', 'country_id' => 1],
            ['name' => 'Madhya Pradesh', 'country_id' => 1],
            ['name' => 'Maharashtra', 'country_id' => 1],
            ['name' => 'Manipur', 'country_id' => 1],
            ['name' => 'Meghalaya', 'country_id' => 1],
            ['name' => 'Mizoram', 'country_id' => 1],
            ['name' => 'Nagaland', 'country_id' => 1],
            ['name' => 'Odisha', 'country_id' => 1],
            ['name' => 'Puducherry', 'country_id' => 1],
            ['name' => 'Punjab', 'country_id' => 1],
            ['name' => 'Rajasthan', 'country_id' => 1],
            ['name' => 'Sikkim', 'country_id' => 1],
            ['name' => 'Tamil Nadu', 'country_id' => 1],
            ['name' => 'Telangana', 'country_id' => 1],
            ['name' => 'Tripura', 'country_id' => 1],
            ['name' => 'Uttar Pradesh', 'country_id' => 1],
            ['name' => 'Uttarakhand', 'country_id' => 1],
            ['name' => 'West Bengal', 'country_id' => 1],
        ];
        
        
        DB::table('states')->insert($states);
    }
}
