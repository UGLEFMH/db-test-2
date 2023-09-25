<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\Unit;
use App\Models\UnitType;
use App\Services\Helper;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @throws Exception
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Unit::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $lodgeUnitTypeId = (UnitType::where('name', 'Lodge')->first())->id;
        $bucksRegionId = (Region::where('name', RegionSeeder::BUCKS_REGION_NAME)->first())->id;
        $bucksCsvPath = 'csvs/bucks_current_memberships.csv';
        $this->processUnitsImportFromCsv($lodgeUnitTypeId, $bucksRegionId, $bucksCsvPath);

        $essexRegionId = (Region::where('name', RegionSeeder::ESSEX_REGION_NAME)->first())->id;
        $essexCsvPath = 'csvs/essex_current_memberships.csv';
        $this->processUnitsImportFromCsv($lodgeUnitTypeId, $essexRegionId, $essexCsvPath);

        $hantsiowRegionId = (Region::where('name', RegionSeeder::HANTSIOW_REGION_NAME)->first())->id;
        $hantsiowCsvPath = 'csvs/hantsiow_current_memberships.csv';
        $this->processUnitsImportFromCsv($lodgeUnitTypeId, $hantsiowRegionId, $hantsiowCsvPath);

        $metropolitanRegionId = (Region::where('name', RegionSeeder::METROPOLITAN_REGION_NAME)->first())->id;
        $metropolitanCsvPath = 'csvs/metropolitan_current_memberships.csv';
        $this->processUnitsImportFromCsv($lodgeUnitTypeId, $metropolitanRegionId, $metropolitanCsvPath);
    }

    /**
     * @param $unitTypeId
     * @param $regionId
     * @param $csvPath
     * @return void
     * @throws Exception
     */
    public function processUnitsImportFromCsv($unitTypeId, $regionId, $csvPath): void
    {
        $csvFile = resource_path($csvPath);
        $handle = fopen($csvFile, 'r');

        if (!$handle) {
            throw new Exception("Unable to open CSV file.");
        }

        $lcNoIndex = null;
        $lcNameIndex = null;
        $header = fgetcsv($handle);

        if ($header) {
            $lcNoIndex = array_search('LC No', $header);
            $lcNameIndex = array_search('LC Name', $header);
        }

        $chunkSize = 500; // You can adjust this based on your needs

        while (($chunk = Helper::readChunk($handle, $chunkSize)) !== false) {
            $insertData = [];

            foreach ($chunk as $row) {
                if (!empty($row[$lcNoIndex]) && !empty($row[$lcNameIndex]) && !$this->alreadyExists(substr($row[$lcNoIndex], 1), $row[$lcNameIndex])) {
                    $insertData[] = [
                        'unit_no' => substr($row[$lcNoIndex], 1),
                        'name' => $row[$lcNameIndex],
                        'unit_type_id' => $unitTypeId,
                        'region_id' => $regionId,
                    ];
                }
            }

            $uniqueArray = array_map("unserialize", array_unique(array_map("serialize", $insertData)));
            if (!empty($uniqueArray)) {
                DB::table('units')->insert($uniqueArray);
            }
        }

        fclose($handle);
    }

    /**
     * @param $lcNo
     * @param $lcName
     * @return bool
     */
    public function alreadyExists($lcNo, $lcName): bool
    {
        return Unit::query()->where('unit_no', $lcNo)->where('name', $lcName)->exists();
    }
}
