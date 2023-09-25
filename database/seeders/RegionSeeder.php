<?php

namespace Database\Seeders;

use App\Models\MasonicOrder;
use App\Models\Organisation;
use App\Models\Region;
use App\Models\RegionType;
use App\Models\UnitType;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    const MASONIC_ORDERS_VALUES = ['Craft', 'Royal Arch'];
    const ORGANISATION_VALUES = ['UGLE', 'SGC'];
    const REGION_TYPE_VALUES = ['Metropolitan', 'Province', 'District'];
    const UNIT_TYPE_VALUES = ['Lodge', 'Chapter', 'Lol', 'Col'];

    const BUCKS_REGION_NAME = 'PGL Bucks.';
    const ESSEX_REGION_NAME = 'PGL Essex';
    const HANTSIOW_REGION_NAME = 'PGL Hants. & I. of W.';
    const METROPOLITAN_REGION_NAME = 'METROPOLITAN';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::MASONIC_ORDERS_VALUES as $name) {
            MasonicOrder::updateOrInsert(['name' => $name]);
        }

        foreach (self::ORGANISATION_VALUES as $name) {
            $masonicOrderName = $name == 'UGLE' ? 'Craft' : 'Royal Arch';
            $order = MasonicOrder::where('name', $masonicOrderName)->first();
            Organisation::updateOrInsert(
                ['name' => $name],
                [
                    'masonic_order_id' => $order->id
                ]
            );
        }

        foreach (self::REGION_TYPE_VALUES as $name) {
            RegionType::updateOrInsert(['name' => $name]);
        }

        foreach (self::UNIT_TYPE_VALUES as $name) {
            UnitType::updateOrInsert(['name' => $name]);
        }

        $ugleOrg = Organisation::where('name', 'UGLE')->first();
        $provinceRegionType = RegionType::where('name', 'Province')->first();
        Region::updateOrInsert(
            ['name' => self::BUCKS_REGION_NAME],
            [
                'org_id' => $ugleOrg->id,
                'region_type_id' => $provinceRegionType->id
            ]
        );

        Region::updateOrInsert(
            ['name' => self::ESSEX_REGION_NAME],
            [
                'org_id' => $ugleOrg->id,
                'region_type_id' => $provinceRegionType->id
            ]
        );

        Region::updateOrInsert(
            ['name' => self::HANTSIOW_REGION_NAME],
            [
                'org_id' => $ugleOrg->id,
                'region_type_id' => $provinceRegionType->id
            ]
        );

        Region::updateOrInsert(
            ['name' => self::METROPOLITAN_REGION_NAME],
            [
                'org_id' => $ugleOrg->id,
                'region_type_id' => $provinceRegionType->id
            ]
        );
    }
}
