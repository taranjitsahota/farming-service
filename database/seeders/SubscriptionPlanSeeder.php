<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubscriptionPlan::create([
            'name' => 'General Plan',
            'price_per_kanal' => 2500,
            'min_kanals' => 4,
            'upfront_percentage' => 25,
            'emi_months' => 11,
            'services' => [
                'Land Cultivation',
                'Boom Spraying',
                'Harvesting',
                'Land Levelling',
                'Transportation',
                'Custom Services'
            ],
            'benefits' => [
                'Ease of Bookings',
                'Unlimited Access'
            ],
            'razorpay_plan_id' => null,
            'status' => true,
        ]);

        SubscriptionPlan::create([
            'name' => 'Sugarcane Plan',
            'price_per_kanal' => 1500,
            'min_kanals' => 4,
            'upfront_percentage' => 25,
            'emi_months' => 11,
            'services' => [
                'Harvesting',
                'Transport to Mill',
                'All general services'
            ],
            'benefits' => [
                'Ease of Bookings',
                'Unlimited Access'
            ],
            'razorpay_plan_id' => null,
            'status' => true,
        ]);
    }
}
