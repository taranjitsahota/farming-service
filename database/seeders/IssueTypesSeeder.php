<?php

namespace Database\Seeders;

use App\Models\IssueType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IssueTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IssueType::insert([
            [
                'title' => 'Driver didn’t arrive',
                'description' => "If your driver hasn’t reached the pickup point, please check the live status in your bookings section. In case of delays, our system automatically alerts the assigned driver and dispatches a backup if required.",
            ],
            [
                'title' => 'Payment Issue',
                'description' => "If your payment is not reflecting, wait a while or contact support.",
            ],
            [
                'title' => 'Equipment not delivered',
                'description' => "Please check the booking status and reach support if delayed.",
            ],
        ]);
    }
}
