<?php

namespace App\Managers;

use App\Models\Patient;
use App\Models\User;

class NotificationManager
{
    public function patientUserUpdate(User $user)
    {
        $notification = $user->profile['notification'] ?? null;
        $patient = ($user->profile['patient_id'] ?? null) ? Patient::find($user->profile['patient_id']) : null;

        if (!$notification || !$patient) {
            return;
        }

        $profile = $patient->profile;
        $profile['notification'] = [
            'user_id' => $notification['user_id'],
            'active' => $notification['active'],
        ];
        return $patient->update(['profile' => $profile]);
    }
}
