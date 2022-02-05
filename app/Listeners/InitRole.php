<?php

namespace App\Listeners;

use App\Events\Registered;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class InitRole
{
    protected $roles;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->roles = [
            'root' => [10022569],
            'admin' => [10022743, 10010205, 10019410, 10035479, 10039018, 10034123],
            'md' => [10022743,  10010205, 10019410, 10035479, 10039018, 10034123],
            'id_md' => [
                10004523, 10004410, 10005617, 10006561, 10011383, 10017612, 10011229, 10018518, 10018516, 10030820, 10034123,
                10038506, 10038274, 10038336, 10038490, 10038177, 10038423,
            ],
            'pm_md' => [10003963, 10022783, 10027514, 10028530, 10032666],
        ];
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        $profile = Session::get('profile');

        if ($profile['is_md']) {
            $event->user->assignRole('md');
            $event->user->home_page = 'visits.exam-list';
            $event->user->save();
        } elseif ($event->user->profile['position'] === 'พยาบาล') {
            $event->user->assignRole('nurse');
            $event->user->assignRole('staff');
            $event->user->home_page = 'visits.screen-list';
            $event->user->save();
        } elseif ($event->user->profile['position'] === 'ผู้ช่วยพยาบาล') {
            $event->user->assignRole('nurse');
            $event->user->assignRole('staff');
            $event->user->home_page = 'visits.screen-list';
            $event->user->save();
        } elseif (str_contains($event->user->profile['remark'], 'งานเวชระเบียน')
            || ($event->user->profile['position'] === 'เจ้าหน้าที่ธุรการ' && str_contains($event->user->profile['remark'], 'ฝ่ายการพยาบาล'))
        ) {
            $event->user->assignRole('staff');
            $event->user->home_page = 'visits.mr-list';
            $event->user->save();
        }

        foreach ($this->roles as $role => $ids) {
            if (collect($ids)->contains($profile['org_id'])) {
                $event->user->assignRole($role);
                $event->user->home_page = 'visits';
                $event->user->save();
            }
        }

        Session::forget('profile');
    }
}
