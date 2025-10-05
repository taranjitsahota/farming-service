<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Faq::insert([
            [
                'question' => 'How to book equipment?',
                'answer' => 'Go to home > Select equipment > Book',
                'status' => 1,
            ],
            [
                'question' => 'Can I cancel a booking?',
                'answer' => 'Yes, visit the My Bookings section to cancel.',
                'status' => 1,
            ],
            [
                'question' => 'What if the driver doesn’t come?',
                'answer' => 'Contact support or raise an issue from your bookings.',
                'status' => 1,
            ],
        ]);
    }
}
