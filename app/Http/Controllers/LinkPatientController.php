<?php

namespace App\Http\Controllers;

use App\Contracts\AuthenticationAPI;
use App\Contracts\PatientAPI;
use App\Managers\NotificationManager;
use App\Managers\PatientManager;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

class LinkPatientController extends Controller
{
    public function __invoke(AuthenticationAPI $api)
    {
        Request::validate([
            'hn' => 'digits:8',
            'sap_mode' => 'required'
        ]);

        $authUser = Auth::user();
        $sapUser = $api->getUserById($authUser->profile['org_id']);
        if (!$sapUser['found']) {
            return [
                'errors' => ['hn' => 'ไม่พบข้อมูล SAP ID กรุณาลองใหม่']
            ];
        }

        $patient = (new PatientManager)->manage(Request::input('hn'));
        if (!$patient['found']) {
            return [
                'errors' => ['hn' => 'ไม่พบข้อมูล HN กรุณาลองใหม่']
            ];
        }

        if (intval($patient['patient']->profile['document_id']) !== intval($sapUser['national_id'])) {
            return [
                'errors' => ['hn' => 'ข้อมูลไม่ตรงกัน กรุณาลองใหม่']
            ];
        }

        $duplicate = User::where('profile->patient_id', $patient['patient']->id)->first();
        if ($duplicate) {
            return [
                'errors' => ['hn' => 'มีผู้ใช้งาน HN นี้แล้ว กรุณาลองใหม่']
            ];
        }

        $authUser->update(['profile->patient_id' => $patient['patient']->id]);
        (new NotificationManager)->patientUserUpdate($this->user);
        return ['ok' => true];
    }
}
