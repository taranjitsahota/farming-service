<?php

namespace Database\Seeders;

use App\Models\SupportContact;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupportContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SupportContact::create([
            'email'         => 'support@ezykheti.com',
            'phone'         => '+91 91234 56789',
            'whatsapp_url'  => 'https://wa.me/919123456789',
        ]);
    }
}
