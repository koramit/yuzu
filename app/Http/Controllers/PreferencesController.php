<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class PreferencesController extends Controller
{
    protected $pages = [
        'visits.screen-list' => 'ห้องคัดกรอง',
        'visits.exam-list' => 'ห้องตรวจ',
        'visits.enqueue-swab-list' => 'จัดกระติก',
        'visits.swab-list' => 'ห้อง Swab',
        'visits.swab-notification-list' => 'เรียกคิว Swab',
        'visits.mr-list' => 'เวชระเบียน',
        'visits.queue-list' => 'ธุรการ',
        'visits.today-list' => 'รายการเคสวันนี้',
        'visits.lab-list' => 'ผลแลปวันนี้',
        'visits' => 'รายการเคสทั้งหมด',
        'decisions' => 'Decision',
        'certifications' => 'Certification',
        'preferences' => 'ตั้งค่า',
    ];

    public function show()
    {
        $user = Auth::user();
        Request::session()->flash('page-title', 'ตั้งค่า');
        Request::session()->flash('main-menu-links', [
            ['icon' => 'home', 'label' => 'หน้าหลัก', 'route' => $user->home_page, 'can' => true],
        ]);

        $canManageNotifications = $user->line_active && $user->role_names->intersect(config('app.specific_roles'))->count();

        return Inertia::render('Auth/Preferences', [
            'selectHomePage' => [
                'currentHomePage' => $this->pages[$user->home_page],
                'pages' => [
                    ['label' => 'ห้องคัดกรอง', 'can' => $user->can('view_screen_list')],
                    ['label' => 'ห้องตรวจ', 'can' => $user->can('view_exam_list')],
                    ['label' => 'จัดกระติก', 'can' => $user->can('view_enqueue_swab_list')],
                    ['label' => 'ห้อง Swab', 'can' => $user->can('view_swab_list')],
                    ['label' => 'เรียกคิว Swab', 'can' => $user->can('view_swab_notification_list')],
                    ['label' => 'เวชระเบียน', 'can' => $user->can('view_mr_list')],
                    ['label' => 'ธุรการ', 'can' => $user->can('view_queue_list')],
                    ['label' => 'รายการเคสวันนี้', 'can' => $user->can('view_today_list')],
                    ['label' => 'ผลแลปวันนี้', 'can' => $user->can('view_lab_list')],
                    ['label' => 'รายการเคสทั้งหมด', 'can' => $user->can('view_any_visits')],
                    ['label' => 'Decision', 'can' => $user->can('view_decision_list')],
                    ['label' => 'Certification', 'can' => $user->can('view_certification_list')],
                ],
            ],
            'layoutAppearance' => [
                'fontScaleIndex' => strval($user->profile['configs']['appearance']['fontScaleIndex'] ?? 3),
                'zenMode' => $user->profile['configs']['appearance']['zenMode'] ?? false,
            ],
            'linkMocktail' => [
                'linked' => $user->mocktail_token !== null,
                'can' => $user->can('link_mocktail'),
            ],
            'setupNotification' => [
                'line_bot_link_url' => config('services.line.bot_link_url'),
                'line_bot_qrcode' => url(config('services.line.bot_qrcode')),
                'line_verified' =>$user->line_verified,
            ],
            'manageNotification' => [
                'notifications' => [
                    // ['can' => $canManageNotifications, 'value' =>  9, 'set' => false, 'label' => 'ดื่มน้ำ'],
                    ['can' => $user->line_active && $user->role_names->intersect(collect(['admin', 'root', 'in_charge']))->count(), 'value' => 10, 'set' => false, 'label' => 'มีผู้ป่วยตกค้าง'],
                    ['can' => $canManageNotifications, 'value' => 11, 'set' => false, 'label' => 'เมื่อผลครบ'],
                    // ['can' => $canManageNotifications, 'value' => 11, 'set' => false, 'label' => 'รายงานถ่ายทอดสด'],
                    // ['can' => $canManageNotifications, 'value' => 12, 'set' => false, 'label' => 'เมื่อมีผลบวก'],
                    // ['can' => $canManageNotifications, 'value' => 13, 'set' => false, 'label' => 'เมื่อผลครบตามกลุ่มผู้ป่วย'],
                    ['can' => $user->line_active && $user->role_names->intersect(collect(['admin', 'root']))->count(), 'value' => 14, 'set' => false, 'label' => 'Croissant งอแง'],
                ],
                'subscriptions' => $user->subscribedNotifications()->pluck('id'),
            ],
            'linkPatient' => [
                'patient_id' => $user->profile['patient_id'] ?? null,
                'hn' => Patient::find($user->profile['patient_id'] ?? null)?->hn,
                'sap_mode' => $user->profile['org_id'] ?? null,
            ]
        ]);
    }

    public function update()
    {
        $data = Request::all();
        if (isset($data['home_page'])) {
            return $this->updateHomePage($data);
        } elseif (isset($data['appearance'])) {
            return $this->updateConfigs(key: 'appearance', data: $data['appearance']);
        } elseif (isset($data['notification_event_id'])) {
            $user = Auth::user();
            if ($data['subscribe']) {
                $user->subscribedNotifications()->attach($data['notification_event_id']);
            } else {
                $user->subscribedNotifications()->detach($data['notification_event_id']);
            }
            return ['ok' => true];
        } else {
            return ['ok' => true];
        }
    }

    protected function updateHomePage($data)
    {
        $newHomePage = null;
        foreach ($this->pages as $route => $name) {
            if ($data['home_page'] === $name) {
                $newHomePage = $route;
                break;
            }
        }

        if ($newHomePage) {
            $user = Auth::user();
            $user->home_page = $newHomePage;
            $user->save();
        }

        return ['ok' => true];
    }

    protected function updateConfigs(string $key, array $data)
    {
        $user = Auth::user();
        $user->update(['profile->configs->'.$key => $data]);

        return [
            'ok' => Session::put('configs', $user->profile['configs']),
        ];
    }
}
