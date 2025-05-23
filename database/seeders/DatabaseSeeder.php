<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            BusinessTimingSeeder::class,
            VillagesImportSeeder::class,
        ]);
        User::factory()->create([
            'name' => 'Taranjit Sahota',
            'email' => 'taranjit.sahota@gmail.com',
            'password' => Hash::make('Password123'),
            'role' => 'superadmin',
        ]);
        User::factory()->create([
            'name' => 'Hardeep Sahota',
            'email' => 'jaspal_hardeep@yahoo.com',
            'password' => Hash::make('Password123'),
            'role' => 'superadmin',
        ]);
        User::factory()->create([
            'name' => 'Keshav Dwivedi',
            'email' => 'keshavdwivedi75@gmail.com',
            'password' => Hash::make('Password123'),
            'role' => 'superadmin',
        ]);
    }
}
