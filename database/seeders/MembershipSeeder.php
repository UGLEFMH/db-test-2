<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipCategories;
use App\Models\MembershipJoinType;
use App\Models\Unit;
use App\Services\Helper;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MembershipSeeder extends Seeder
{
    const POSSIBLE_DATE_FORMATS = [
        'd/m/Y',
        'd-M-Y'
    ];

    /**
     * Run the database seeds.
     * @throws Exception
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Membership::query()->truncate();

        $bucksCsvPath = 'csvs/bucks_current_memberships.csv';
        $this->processMembershipsImportFromCsv($bucksCsvPath);

        $essexCsvPath = 'csvs/essex_current_memberships.csv';
        $this->processMembershipsImportFromCsv($essexCsvPath);

        $hantsiowCsvPath = 'csvs/hantsiow_current_memberships.csv';
        $this->processMembershipsImportFromCsv($hantsiowCsvPath);

        $metropolitanCsvPath = 'csvs/metropolitan_current_memberships.csv';
        $this->processMembershipsImportFromCsv($metropolitanCsvPath);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * @throws Exception
     */
    public function processMembershipsImportFromCsv($csvPath): void
    {
        $csvFile = resource_path($csvPath);
        $handle = fopen($csvFile, 'r');

        if (!$handle) {
            throw new Exception("Unable to open CSV file.");
        }

        $joinTypeObjects = MembershipJoinType::all();
        $categoriesObjets = MembershipCategories::all();

        $lcNoIndex = $lcNameIndex = $glRefIndex = $membershipStatusIndex = $joinDateIndex = $joinTypeIndex = $joinCategoryIndex = $effectiveDateIndex = null;
        $header = fgetcsv($handle);

        if ($header) {
            $lcNoIndex = array_search('LC No', $header);
            $lcNameIndex = array_search('LC Name', $header);
            $glRefIndex = array_search('GL Ref', $header);
            $membershipStatusIndex = array_search('Membership Status', $header);
            $joinDateIndex = array_search('Join Date', $header);
            $joinTypeIndex = array_search('Join Type', $header);
            $joinCategoryIndex = array_search('Join Category', $header);
            $effectiveDateIndex = array_search('Status Effective Date', $header);
        }

        $chunkSize = 500; // You can adjust this based on your needs

        while (($chunk = Helper::readChunk($handle, $chunkSize)) !== false) {
            foreach ($chunk as $row) {
                if (!empty($row[$glRefIndex]) && in_array($row[$joinTypeIndex], Membership::JOIN_TYPES_ARRAY)) {
                    $memberId = (Member::where('glref', $row[$glRefIndex])->first())->id;
                    $unitId = (Unit::query()->where('unit_no', substr($row[$lcNoIndex], 1))->where('name', $row[$lcNameIndex])->first())->id;
                    $joinTypeId = ($joinTypeObjects->where('name', $row[$joinTypeIndex])->first())->id;
                    $categoryId = ($categoriesObjets->where('name', $row[$joinCategoryIndex])->first())->id;

                    foreach (self::POSSIBLE_DATE_FORMATS as $format) {
                        try {
                            $carbonDate = Carbon::createFromFormat($format, $row[$joinDateIndex]);
                            $joinDate = $carbonDate->format('Y-m-d');

                            if ($joinDate > date('Y-m-d') || $joinDate < date('1900-01-01')) {
                                Log::error('Join date is not valid for glref: ' . $row[$glRefIndex]);
                                continue;
                            }

                            $membershipParentId = DB::table('memberships')->insertGetId([
                                'member_id' => $memberId,
                                'unit_id' => $unitId,
                                'in_out' => Membership::IN_OUT_VALUE_IN,
                                'date_effective' => $joinDate,
                                'membership_join_type_id' => $joinTypeId,
                                'membership_category_id' => $categoryId,
                            ]);

                            break;
                        } catch (\Exception $e) {
                            $membershipParentId = null;
                        }
                    }

                    if (!empty($membershipParentId) && $row[$membershipStatusIndex] !== Membership::MEMBERSHIP_STATUS_CURRENT) {
                        $joinTypeId = ($joinTypeObjects->where('name', $row[$membershipStatusIndex])->first())->id;

                        foreach (self::POSSIBLE_DATE_FORMATS as $format) {
                            try {
                                $carbonDate = Carbon::createFromFormat($format, $row[$effectiveDateIndex]);
                                $effectiveDate = $carbonDate->format('Y-m-d');

                                DB::table('memberships')->insert([
                                    'member_id' => $memberId,
                                    'unit_id' => $unitId,
                                    'in_out' => Membership::IN_OUT_VALUE_OUT,
                                    'date_effective' => $effectiveDate,
                                    'parent_id' => $membershipParentId,
                                    'membership_join_type_id' => $joinTypeId,
                                    'membership_category_id' => $categoryId,
                                ]);

                                break;
                            } catch (\Exception $e) {
                                Log::error($e->getMessage());
                            }
                        }
                    }
                }
            }
        }

        fclose($handle);
    }
}
