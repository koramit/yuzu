<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datetime = now();

        Division::insert([
            [
                'name' => 'บุคคลทั่วไป',
                'name_eng' => 'Public',
                'name_eng_short' => 'public',
                'department' => 'public',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'name' => 'คณะแพทยศาสตร์ศิริราชพยาบาล',
                'name_eng' => 'Faculty of Medicine Siriraj Hospital',
                'name_eng_short' => 'faculty',
                'department' => 'faculty',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'name' => 'ภาควิชาอายุรศาสตร์',
                'name_eng' => 'Department of Medicine',
                'name_eng_short' => 'medicine',
                'department' => 'medicine',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'name' => 'ฝ่ายการพยาบาล',
                'name_eng' => 'Department of Nursing Siriraj Hospital',
                'name_eng_short' => 'nursing',
                'department' => 'nursing',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'name' => 'โรคติดเชื้อและอายุรศาสตร์เขตร้อน',
                'name_eng' => 'Division of Infectious Diseases and Tropical Medicine',
                'name_eng_short' => 'infectious',
                'department' => 'medicine',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'name' => 'ฝ่ายเภสัชกรรม',
                'name_eng' => 'Pharmacy Department',
                'name_eng_short' => 'pharmacy',
                'department' => 'pharmacy',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'name' => 'วักกะวิทยา',
                'name_eng' => 'Division of Nephrology',
                'name_eng_short' => 'nephrology',
                'department' => 'medicine',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ]);
    }
}
