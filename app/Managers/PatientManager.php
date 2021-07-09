<?php

namespace App\Managers;

use App\Models\Patient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PatientManager
{
    public function manage($hn)
    {
        $api = app()->make('App\Contracts\PatientAPI');

        // search HN in DB
        $patient = Patient::findByEncryptedKey($hn);
        if (! $patient) {
            $data = $api->getPatient($hn);
            if (! $data['found']) {
                return $data;
            }

            unset($data['ok'], $data['found']);
            $patient = Patient::create(['hn' => $hn, 'slug' => Str::uuid()->toString(), 'profile' => $data]);

            return [
                'found' => true,
                'patient' => $patient,
            ];
        }

        // determine if update needed
        if ($patient->updated_at->diffInDays(now()) <= 5) {
            return [
                'found' => true,
                'patient' => $patient,
            ];
        }

        $data = $api->getPatient($hn);
        if (! $data['found']) {
            Log::info($hn.' hn cancle or something went wrong');

            return $patient;
        }

        unset($data['ok'], $data['found']);
        $patient = $this->update($patient, $data);

        return [
            'found' => true,
            'patient' => $patient,
        ];
    }

    public function update($patient, $profile)
    {
        $oldProfile = $patient->profile;

        if ($oldProfile['title'] !== $profile['title'] || // check if name changed
            $oldProfile['first_name'] !== $profile['first_name'] ||
            $oldProfile['last_name'] !== $profile['last_name']
        ) {
            if (isset($oldProfile['old_names'])) {
                $oldProfile['old_names'] = [];
            }
            $oldProfile['old_names'][] = implode(' ', [$oldProfile['title'], $oldProfile['first_name'], $oldProfile['last_name']]);
        }

        foreach ($profile as $key => $value) {
            if (isset($oldProfile[$key])) {
                $oldProfile[$key] = $value;
            }
        }
        $patient->update(['profile' => $oldProfile]);

        return $patient;
    }
}
