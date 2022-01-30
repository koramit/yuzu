<?php

namespace App\Managers;

use App\Models\Patient;
use App\Models\User;

class NotificationManager
{
    protected $bot;

    public function __construct()
    {
        $this->bot = new LINEMessagingManager();
    }

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

    public function notifySwabQueue(string $userId)
    {
        $messages[] = $this->bot->buildTextMessage(__('bot.notify_swab_queue'));
        $messages[] = $this->bot->buildStickerMessage(packageId:6359, stickerId:11069859);
        $this->bot->pushMessage(userId: $userId, messages: $messages, mode: 'notify_swab_queue');
    }
}
