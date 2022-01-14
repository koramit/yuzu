<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class PreferencesController extends Controller
{
    protected $pages = [
        'visits.screen-list' => 'ห้องคัดกรอง',
        'visits.exam-list' => 'ห้องตรวจ',
        'visits.enqueue-swab-list' => 'จัดกระติก',
        'visits.swab-list' => 'ห้อง Swab',
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
        Request::session()->flash('page-title', 'ตั้งค่า');
        Request::session()->flash('main-menu-links', [
            ['icon' => 'home', 'label' => 'หน้าหลัก', 'route' => 'home', 'can' => true],
        ]);

        $user = Auth::user();

        return Inertia::render('Auth/Preferences', [
            'selectHomePage' => [
                'currentHomePage' => $this->pages[$user->home_page],
                'pages' => [
                    ['label' => 'ห้องคัดกรอง', 'can' => $user->can('view_screen_list')],
                    ['label' => 'ห้องตรวจ', 'can' => $user->can('view_exam_list')],
                    ['label' => 'จัดกระติก', 'can' => $user->can('view_enqueue_swab_list')],
                    ['label' => 'ห้อง Swab', 'can' => $user->can('view_swab_list')],
                    ['label' => 'เวชระเบียน', 'can' => $user->can('view_mr_list')],
                    ['label' => 'ธุรการ', 'can' => $user->can('view_queue_list')],
                    ['label' => 'รายการเคสวันนี้', 'can' => $user->can('view_today_list')],
                    ['label' => 'ผลแลปวันนี้', 'can' => $user->can('view_lab_list')],
                    ['label' => 'รายการเคสทั้งหมด', 'can' => $user->can('view_any_visits')],
                    ['label' => 'Decision', 'can' => $user->can('view_decision_list')],
                    ['label' => 'Certification', 'can' => $user->can('view_certification_list')],
                ],
            ],
            'linkMocktail' => [
                'linked' =>$user->mocktail_token !== null,
                'can' => $user->can('link_mocktail'),
            ],
        ]);
    }

    public function update()
    {
        $data = Request::all();
        if (isset($data['home_page'])) {
            return $this->updateHomePage($data);
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
}
