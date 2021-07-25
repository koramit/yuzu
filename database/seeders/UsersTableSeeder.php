<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $password = Hash::make('secret');

        // nurse
        $u = new User();
        $u->name = 'nurse';
        $u->login = 'nurse.sri';
        $u->password = $password;
        $u->profile = [
            'full_name' => 'คุณ พยาบาล ชำนาญกิจ',
            'tel_no' => '084111888',
            'org_id' => '10010001',
            'division' => 'ฝ่ายการพยาบาล',
            'position' => 'พยาบาล',
            'pln' => null,
            'remark' => 'พยาบาล พยาบาล ฝ่ายการพยาบาล',
            'home_page' => 'visits.screen-list',
        ];
        $u->save();
        $u->assignRole('nurse');

        // md
        $u = new User();
        $u->name = 'md';
        $u->login = 'md.sri';
        $u->password = $password;
        $u->profile = [
            'full_name' => 'พญ. แพทย์ ชำนาญการ',
            'tel_no' => '084111999',
            'org_id' => '10010002',
            'division' => 'คณะแพทย์',
            'position' => 'แพทย์',
            'pln' => 55555,
            'remark' => 'แพทย์ แพทย์ คณะแพทย์',
            'home_page' => 'visits.exam-list',
        ];
        $u->save();
        $u->assignRole('md');

        // staff
        $u = new User();
        $u->name = 'staff';
        $u->login = 'staff.sri';
        $u->password = $password;
        $u->profile = [
            'full_name' => 'นางสาว เวชระเบียน ชำนาญเกิน',
            'tel_no' => '084111777',
            'org_id' => '10010003',
            'division' => 'คณะแพทย์',
            'position' => 'เจ้าหน้าที่เวชระเบียน',
            'pln' => null,
            'remark' => 'เจ้าหน้าที่เวชระเบียน เจ้าหน้าที่เวชระเบียน งานเวชระเบียน',
            'home_page' => 'visits.mr-list',
        ];
        $u->save();
        $u->assignRole('staff');

        // admin
        $u = new User();
        $u->name = 'admin';
        $u->login = 'admin.sri';
        $u->password = $password;
        $u->profile = [
            'full_name' => 'คุณ ธุรการ ชำนาญกเกม',
            'tel_no' => '084111888',
            'org_id' => '10010004',
            'division' => 'ฝ่ายการธุรการ',
            'position' => 'ธุรการ',
            'pln' => null,
            'remark' => 'ธุรการ ธุรการ ฝ่ายการธุรการ',
            'home_page' => 'visits',
        ];
        $u->save();
        $u->assignRole('admin');

        // id
        $u = new User();
        $u->name = 'id';
        $u->login = 'id.sri';
        $u->password = $password;
        $u->profile = [
            'full_name' => 'พญ. เขตร้อน ชำนาญเชื้อ',
            'tel_no' => '084123987',
            'org_id' => '10010005',
            'division' => 'คณะแพทย์',
            'position' => 'แพทย์',
            'pln' => 54321,
            'remark' => 'แพทย์ แพทย์ คณะแพทย์',
            'home_page' => 'visits.exam-list',
        ];
        $u->save();
        $u->assignRole('md');
        $u->assignRole('id_md');
    }
}
