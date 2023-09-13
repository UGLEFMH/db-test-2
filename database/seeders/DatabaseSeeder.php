<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RegionSeeder::class);
        $this->call(UnitSeeder::class);
        $this->call(MemberSeeder::class);
        $this->call(JoinTypesAndCatsSeeder::class);
        $this->call(MembershipSeeder::class);
    }
}
