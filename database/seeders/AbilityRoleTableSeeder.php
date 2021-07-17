<?php

namespace Database\Seeders;

use App\Models\Ability;
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
            ['name' => 'update_visit'] + $datetime,
            ['name' => 'cancel_visit'] + $datetime,
            ['name' => 'sign_opd_card'] + $datetime,
            ['name' => 'sign_opd_card_on_behalf'] + $datetime,
        ]);
    }
}
