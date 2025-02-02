<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\Village;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VillagesImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(database_path('indianvillages.json'));
        $data = json_decode($json, true);

        $country = Country::firstOrCreate(['name' => 'India', 'countryCode' => 'IN']);

        // Cache state, district, and city IDs in arrays to reduce queries
        $stateCache = State::where('country_id', $country->id)->pluck('id', 'name')->toArray();
        $cityCache = [];
        $villageData = [];

        foreach ($data as $stateData) {
            // Check if the state exists in cache, otherwise create it
            if (!isset($stateCache[$stateData['state']])) {
                $state = State::create([
                    'name' => $stateData['state'],
                    'country_id' => $country->id,
                ]);
                $stateCache[$stateData['state']] = $state->id;
            }

            $stateId = $stateCache[$stateData['state']];

            foreach ($stateData['districts'] as $districtData) {
                foreach ($districtData['subDistricts'] as $subDistrictData) {
                    $cityName = $subDistrictData['subDistrict'];

                    // Check if the city exists in cache, otherwise create it
                    if (!isset($cityCache[$cityName])) {
                        $city = City::create([
                            'name' => $cityName,
                            'state_id' => $stateId,
                        ]);
                        $cityCache[$cityName] = $city->id;
                    }

                    $cityId = $cityCache[$cityName];

                    // Prepare villages for bulk insert
                    foreach ($subDistrictData['villages'] as $villageName) {
                        $villageData[] = [
                            'name' => $villageName,
                            'city_id' => $cityId,
                        ];
                    }
                }
            }
        }

        // Bulk insert villages in chunks for better performance
        $chunkSize = 500;
        foreach (array_chunk($villageData, $chunkSize) as $chunk) {
            Village::insert($chunk);
        }


    }
}
