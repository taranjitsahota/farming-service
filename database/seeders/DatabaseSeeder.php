<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Equipment;
use App\Models\User;
use GuzzleHttp\Promise\Is;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            RoleSeeder::class,
            BusinessTimingSeeder::class,
            ServicesTableSeeder::class,
            EquipmentSeeder::class,
            CropSeeder::class,
            VillagesImportSeeder::class,
            IssueTypesSeeder::class,
            FaqsSeeder::class,
            SupportContactSeeder::class
        ]);

        $superadminRole = Role::where('name', 'superadmin')->first();

        $superadmins = [
            [
                'name' => 'Taranjit Sahota',
                'email' => 'taranjit.sahota@gmail.com',
                'password' => Hash::make('Password123'),
            ],
            [
                'name' => 'Hardeep Sahota',
                'email' => 'jaspal_hardeep@yahoo.com',
                'password' => Hash::make('Password123'),
            ],
            [
                'name' => 'Keshav Dwivedi',
                'email' => 'keshavdwivedi75@gmail.com',
                'password' => Hash::make('Password123'),
            ],
        ];

        foreach ($superadmins as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            if (! $user->hasRole($superadminRole)) {
                $user->assignRole($superadminRole);
            }
        }
    }
}
