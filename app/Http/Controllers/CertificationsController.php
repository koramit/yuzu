<?php

namespace App\Http\Controllers;

use App\Managers\CertificateManager;
use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class CertificationsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $manager = new VisitManager();
        $flash = $manager->getFlash($user);
        $flash['main-menu-links'][] = ['icon' => 'file-excel', 'label' => 'Export Excel', 'route' => 'export.certificates', 'can' => $user->can('view_certification_list'), 'use_a_tag' => true];
        $flash['page-title'] = 'Certification';
        $manager->setFlash($flash);
        $manager = new CertificateManager();
        $dateVisit = Request::input('date_visit', now('asia/bangkok')->format('Y-m-d'));
        Session::put('certificate-list-export-date', $dateVisit);
        $certificates = Visit::with('patient')
                             ->where('swabbed', true)
                             ->whereDateVisit($dateVisit)
                             ->wherePatientType(1)
                             ->whereNotNull('form->management->np_swab_result')
                             ->where('form->management->np_swab_result', '<>', 'Detected')
                             ->get()
                             ->transform(function ($visit) use ($manager) {
                                 return $manager->getData($visit);
                             });

        return Inertia::render('Certifications/Index', [
            'certificates' => $certificates->filter(fn ($v) => $v['age'] >= 18)->sortBy([['risk', 'asc'], ['detail', 'asc']])->values()->all(),
            'dateVisit' => $dateVisit,
            'can' => ['certify' => $user->can('certify')],
        ]);
    }

    public function update()
    {
        Request::validate(['certificates' => 'required|array']);

        $certificates = Request::input('certificates');
        $manager = new CertificateManager();

        $errors = [];
        foreach ($certificates as $certificate) {
            $visit = Visit::whereSlug($certificate['slug'])->first();
            if (! $visit) {
                $errors[] = $certificate['slug'];
                continue;
            }

            if (! $manager->update($visit, $certificate)) {
                $errors[] = $certificate['slug'];
            }
        }

        return ['ok' => count($errors) === 0];
    }
}
