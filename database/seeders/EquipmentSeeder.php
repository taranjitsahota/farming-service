<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipmentsByService = [
            1 => [ // Cultivation
                "Super Seeder (7 feet)",
                "Rotavator (8/10 feet)",
                "Cultivator",
                "Potato Planter",
                "Pneumatic Planter",
                "Automatic Paddy Planter",
                "Trencher",
                "Automatic Boom Sprayer",
                "Drone Sprayer",
            ],
            2 => [ // Transportation
                "Trolley Bed (5 feet high)",
                "PTO Trolley for Sugarcane",
                "Tata 207 or UTE",
                "Additional Mileage Charge",
            ],
            3 => [ // Harvesting
                "Wheat Combine Harvester",
                "Paddy Combine Harvester",
                "Sugarcane Harvester",
                "Automatic Potato Digger & Grader",
                "Pneumatic Planter",
            ],
        ];

        foreach ($equipmentsByService as $serviceId => $equipments) {
            foreach ($equipments as $name) {
                Equipment::create([
                    'service_id' => $serviceId,
                    'name'       => $name,
                ]);
            }
        }
    }
}
