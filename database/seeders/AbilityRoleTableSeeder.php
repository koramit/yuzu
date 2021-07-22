<?php

namespace Database\Seeders;

use App\Models\Ability;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AbilityRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datetime = ['created_at' => now(), 'updated_at' => now()];

        Ability::insert([
            ['name' => 'create_visit'] + $datetime,
            ['name' => 'view_any_visits'] + $datetime,
            ['name' => 'view_screen_list'] + $datetime,
            ['name' => 'view_exam_list'] + $datetime,
            ['name' => 'view_swab_list'] + $datetime,
            ['name' => 'view_mr_list'] + $datetime,
            ['name' => 'update_visit'] + $datetime, // save
            ['name' => 'correct_visit'] + $datetime,
            ['name' => 'cancel_visit'] + $datetime,
            ['name' => 'sign_opd_card'] + $datetime, // MD save to swab and save to discharge
            ['name' => 'sign_on_behalf'] + $datetime, // nurse save to swab
            ['name' => 'enlist_exam'] + $datetime, // nurse save to swab
            // ['name' => 'check_visit'] + $datetime, // check visited + check printed
            ['name' => 'authorize_visit'] + $datetime,
            ['name' => 'attach_opd_card'] + $datetime,
            ['name' => 'print_opd_card'] + $datetime,
            ['name' => 'self_screening'] + $datetime,
        ]);

        Role::insert([
            ['name' => 'root'] + $datetime, // view_any_visits, cancel_visit
            ['name' => 'admin'] + $datetime, // view_any_visits, cancel_visit
            ['name' => 'nurse'] + $datetime, // create_visit, view_any_visits, update_visit+, sign_on_behalf, enlist_exam, print_opd_card, correct_visit+, cancel_visit+
            ['name' => 'md'] + $datetime, // create_visit, view_any_visits, update_visit+, sign_opd_card, print_opd_card, correct_visit+, cancel_visit+
            ['name' => 'staff'] + $datetime, // view_any_visits, authorize_visit, attach_opd_card, print_opd_card
            ['name' => 'id_md'] + $datetime, //
            ['name' => 'pm_md'] + $datetime, //
            ['name' => 'patient'] + $datetime, // self_screening
        ]);

        $assignment = [
            'root' => ['view_any_visits', 'cancel_visit'],
            'admin' => ['view_screen_list', 'view_exam_list', 'view_swab_list', 'view_mr_list', 'view_any_visits', 'cancel_visit'],
            'md' => ['create_visit', 'view_exam_list', 'view_mr_list', 'update_visit', 'sign_opd_card', 'print_opd_card', 'correct_visit', 'cancel_visit'],
            'nurse' => ['create_visit', 'view_screen_list', 'view_exam_list', 'view_swab_list', 'view_mr_list', 'update_visit', 'sign_on_behalf', 'enlist_exam', 'print_opd_card', 'correct_visit', 'cancel_visit'],
            'staff' => ['view_mr_list', 'authorize_visit', 'attach_opd_card', 'print_opd_card'],
        ];

        foreach ($assignment as $role => $abilities) {
            $theRole = Role::whereName($role)->first();
            foreach ($abilities as $abilitie) {
                $theRole->allowTo($abilitie);
            }
        }
    }
}
