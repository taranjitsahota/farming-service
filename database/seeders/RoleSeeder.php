<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $roles = [
            'superadmin',
            'admin',
            'partner',
            'driver',
            'farmer',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);
        }

        // Permissions
        // Permission::firstOrCreate(['name' => 'manage-equipment']);
        // Permission::firstOrCreate(['name' => 'view-bookings']);
        // Permission::firstOrCreate(['name' => 'accept-bookings']);

        // Assign permissions
        // $partner->givePermissionTo(['manage-equipment', 'view-bookings']);
        // $driver->givePermissionTo(['accept-bookings', 'view-bookings']);
        // $admin->givePermissionTo(['view-bookings']);
    }
}
