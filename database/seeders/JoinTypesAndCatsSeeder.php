<?php

namespace Database\Seeders;

use App\Models\MembershipCategories;
use App\Models\MembershipJoinType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class JoinTypesAndCatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MembershipJoinType::query()->truncate();
        MembershipCategories::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $csvFile = resource_path('csvs/JoinTypes_and_Categories.csv');
        $csvData = File::get($csvFile);
        $rows = array_map('str_getcsv', explode("\n", $csvData));

        $header = array_shift($rows);
        $joinTypeIndex = array_search('JOIN_TYPES', $header);
        $categoriesIndex = array_search('MEMBERSHIPS_CATEGORIES', $header);

        $joinTypesData = $categoriesData = [];
        foreach ($rows as $row) {
            if (!empty($row[$joinTypeIndex])) {
                $joinTypesData[] = [
                    'name' => $row[$joinTypeIndex],
                    'description' => $row[$joinTypeIndex],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($row[$categoriesIndex])) {
                $categoriesData[] = [
                    'name' => $row[$categoriesIndex],
                    'description' => $row[$categoriesIndex],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

        }

        !empty($joinTypesData) ? MembershipJoinType::insert($joinTypesData) : null;
        !empty($categoriesData) ? MembershipCategories::insert($categoriesData) : null;
    }
}
