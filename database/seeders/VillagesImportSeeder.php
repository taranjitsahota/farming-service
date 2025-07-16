<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\District;
use App\Models\State;
use App\Models\Tehsil;
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

        // Caches to avoid duplicates and improve lookup speed
        $stateCache = State::where('country_id', $country->id)->pluck('id', 'name')->toArray();
        $districtCache = [];
        $tehsilCache = [];
        $villageData = [];

        foreach ($data as $stateData) {
            // Insert state
            $stateName = $stateData['state'];
            if (!isset($stateCache[$stateName])) {
                $state = State::create([
                    'name' => $stateName,
                    'country_id' => $country->id,
                ]);
                $stateCache[$stateName] = $state->id;
            }
            $stateId = $stateCache[$stateName];

            foreach ($stateData['districts'] as $districtData) {
                $districtName = $districtData['district'];
                $districtKey = "$stateId-$districtName";

                // Insert district
                if (!isset($districtCache[$districtKey])) {
                    $district = District::create([
                        'name' => $districtName,
                        'state_id' => $stateId,
                    ]);
                    $districtCache[$districtKey] = $district->id;
                }
                $districtId = $districtCache[$districtKey];

                foreach ($districtData['subDistricts'] as $subDistrictData) {
                    $tehsilName = $subDistrictData['subDistrict'];
                    $tehsilKey = "$districtId-$tehsilName";

                    // Insert tehsil
                    if (!isset($tehsilCache[$tehsilKey])) {
                        $tehsil = Tehsil::create([
                            'name' => $tehsilName,
                            'district_id' => $districtId,
                        ]);
                        $tehsilCache[$tehsilKey] = $tehsil->id;
                    }
                    $tehsilId = $tehsilCache[$tehsilKey];

                    // Prepare village entries
                    foreach ($subDistrictData['villages'] as $villageName) {
                        $villageData[] = [
                            'name' => $villageName,
                            'tehsil_id' => $tehsilId,
                        ];
                    }
                }
            }
        }

        // Insert villages in chunks
        foreach (array_chunk($villageData, 500) as $chunk) {
            Village::insert($chunk);
        }
    }
}
