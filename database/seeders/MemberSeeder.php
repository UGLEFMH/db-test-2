<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\People;
use App\Models\Region;
use App\Services\Helper;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @throws Exception
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        People::query()->truncate();
        Member::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $bucksOrgId = (Region::where('name', RegionSeeder::BUCKS_REGION_NAME)->first())->org_id;
        $bucksCsvPath = 'csvs/bucks_current_memberships.csv';
        $this->processMembersImportFromCsv($bucksOrgId, $bucksCsvPath);


        $essexOrgId = (Region::where('name', RegionSeeder::ESSEX_REGION_NAME)->first())->org_id;
        $essexCsvPath = 'csvs/essex_current_memberships.csv';
        $this->processMembersImportFromCsv($essexOrgId, $essexCsvPath);

        $hantsiowOrgId = (Region::where('name', RegionSeeder::HANTSIOW_REGION_NAME)->first())->org_id;
        $hantsiowCsvPath = 'csvs/hantsiow_current_memberships.csv';
        $this->processMembersImportFromCsv($hantsiowOrgId, $hantsiowCsvPath);
    }

    /**
     * @param $orgId
     * @param $csvPath
     * @return void
     * @throws Exception
     */
    public function processMembersImportFromCsv($orgId, $csvPath): void
    {
        $csvFile = resource_path($csvPath);
        $handle = fopen($csvFile, 'r');

        if (!$handle) {
            throw new Exception("Unable to open CSV file.");
        }

        $glRefIndex = null;
        $header = fgetcsv($handle);

        if ($header) {
            $glRefIndex = array_search('GL Ref', $header);
        }

        $chunkSize = 500; // You can adjust this based on your needs

        while (($chunk = Helper::readChunk($handle, $chunkSize)) !== false) {
            foreach ($chunk as $row) {
                if (!empty($row[$glRefIndex]) && !$this->alreadyExists($row[$glRefIndex])) {
                    $peopleId = DB::table('people')->insertGetId(['state' => 1]);
                    DB::table('members')->insert([
                        'org_id' => $orgId,
                        'people_id' => $peopleId,
                        'glref' => $row[$glRefIndex]
                    ]);
                }
            }
        }

        fclose($handle);
    }

    /**
     * @param $glRef
     * @return bool
     */
    public function alreadyExists($glRef): bool
    {
        return Member::query()->where('glref', $glRef)->exists();
    }
}
