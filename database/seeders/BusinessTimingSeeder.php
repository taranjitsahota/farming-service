<?php

namespace Database\Seeders;

use App\Models\BusinessTiming;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusinessTimingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($days as $day) {
            BusinessTiming::create([
                'day' => $day,
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
            ]);
        }
    }
}
