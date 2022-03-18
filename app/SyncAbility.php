<?php
namespace App;

use App\Models\Ability;
use App\Models\Role;

class SyncAbility
{
    public static function run()
    {
        $roleAbility = [
            'root' => ['view_any_visits','cancel_visit'],
            'admin' => ['view_any_visits','view_screen_list','view_exam_list','view_swab_list','view_mr_list','view_queue_list','view_today_list','export_visits','export_opd_cards','cancel_visit','view_visit_actions','link_mocktail','view_decision_list','view_certification_list','view_lab_list'],
            'nurse' => ['create_visit','view_screen_list','view_exam_list','view_swab_list','view_mr_list','view_queue_list','view_enqueue_swab_list','view_today_list','export_visits','update_visit','replace_visit','cancel_visit','sign_on_behalf','enlist_exam','enqueue_swab','print_opd_card'],
            'md' => ['view_screen_list','view_exam_list','view_today_list','update_visit','replace_visit','cancel_visit','sign_opd_card','print_opd_card'],
            'staff' => ['view_mr_list','view_queue_list','view_today_list','authorize_visit','attach_opd_card','print_opd_card'],
            'id_md' => ['create_visit','view_any_visits','export_visits','export_opd_cards','evaluate','link_mocktail','view_decision_list','view_certification_list','certify','view_lab_list'],
            'pm_md' => ['create_visit','view_any_visits','export_visits','export_opd_cards','view_lab_list'],
            'patient' => [],
            'ari_nurse' => ['view_any_visits','view_lab_list'],
            'icn_nurse' => ['view_any_visits','export_opd_cards','view_lab_list'],
        ];

        foreach ($roleAbility as $role => $abilities) {
            $abilityIds = Ability::whereIn('name', $abilities)
                                ->pluck('id')
                                ->all();

            Role::whereName($role)
                ->first()
                ->abilities()
                ->sync($abilityIds);
        }
    }

    public static function add()
    {
        $datetime = ['created_at' => now(), 'updated_at' => now()];

        Ability::insert([
            ['name' => 'get_a_treat'] + $datetime,
            ['name' => 'view_clinic_alert'] + $datetime,
            ['name' => 'subscribe_lab_notify'] + $datetime,
            ['name' => 'view_croissant_feedback'] + $datetime,
            ['name' => 'ask_today_stat'] + $datetime,
        ]);

        $assignment = [
            'admin' => ['get_a_treat', 'view_clinic_alert', 'subscribe_lab_notify', 'view_croissant_feedback', 'ask_today_stat'],
            'id_md' => ['get_a_treat', 'subscribe_lab_notify', 'ask_today_stat'],
            'pm_md' => ['get_a_treat', 'subscribe_lab_notify', 'ask_today_stat'],
            'ari_nurse' => ['get_a_treat', 'subscribe_lab_notify', 'ask_today_stat'],
            'icn_nurse' => ['get_a_treat', 'subscribe_lab_notify', 'ask_today_stat'],
            'in_charge' => ['get_a_treat', 'view_clinic_alert', 'subscribe_lab_notify', 'ask_today_stat'],
        ];

        foreach ($assignment as $role => $abilities) {
            $theRole = Role::whereName($role)->first();
            foreach ($abilities as $abilitie) {
                $theRole->allowTo($abilitie);
            }
        }
    }
}
