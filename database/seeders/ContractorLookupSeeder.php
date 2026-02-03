<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContractorLookupSeeder extends Seeder
{
    public function run()
    {
        DB::table('contractor_types')->insert([
            ['name' => 'General Contractor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Specialty Contractor', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('occupations')->insert([
            ['name' => 'Carpenter', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Plumber', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('valid_ids')->insert([
            ['name' => 'Driver License', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Passport', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
