<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use CountriesTableSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use StatesTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Taranjit Sahota',
            'email' => 'taranjit.sahota@gmail.com',
            'password' => Hash::make('Password123'),
            'role' => 'superadmin',
        ]);
    }
}
