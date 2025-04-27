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

        $stateCache = State::where('country_id', $country->id)->pluck('id', 'name')->toArray();
        $cityCache = [];
        $uniqueCities = [];
        $villageData = [];

        // First pass: gather states and collect unique cities
        foreach ($data as $stateData) {
            // Create state if not in cache
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
                    if (!isset($uniqueCities[$cityName])) {
                        $uniqueCities[$cityName] = [
                            'name' => $cityName,
                            'state_id' => $stateId,
                        ];
                    }
                }
            }
        }

        // Fetch existing cities
        $existingCities = City::whereIn('name', array_keys($uniqueCities))->pluck('id', 'name')->toArray();

        // Remove already existing cities
        $citiesToInsert = array_filter($uniqueCities, function ($city) use ($existingCities) {
            return !isset($existingCities[$city['name']]);
        });

        // Bulk insert new cities
        if (!empty($citiesToInsert)) {
            City::insert(array_values($citiesToInsert));
        }

        // Get all cities again after insert
        $cityCache = City::whereIn('name', array_keys($uniqueCities))->pluck('id', 'name')->toArray();

        // Second pass: prepare village data
        foreach ($data as $stateData) {
            $stateId = $stateCache[$stateData['state']];

            foreach ($stateData['districts'] as $districtData) {
                foreach ($districtData['subDistricts'] as $subDistrictData) {
                    $cityName = $subDistrictData['subDistrict'];
                    $cityId = $cityCache[$cityName] ?? null;

                    if ($cityId) {
                        foreach ($subDistrictData['villages'] as $villageName) {
                            $villageData[] = [
                                'name' => $villageName,
                                'city_id' => $cityId,
                            ];
                        }
                    }
                }
            }
        }

        // Insert villages in chunks
        $chunkSize = 500;
        foreach (array_chunk($villageData, $chunkSize) as $chunk) {
            Village::insert($chunk);
        }
    }
}
