<?php

namespace App\Managers;

use App\Models\NotificationEvent;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

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
        $sticker = collect(config('sticker.line.notify'))->random();
        $messages[] = $this->bot->buildStickerMessage(packageId: $sticker['packageId'], stickerId: $sticker['stickerId']);
        $this->bot->pushMessage(userId: $userId, messages: $messages, mode: 'notify_swab_queue');
    }

    public function notifySubscribers(string $mode, string $text, string $sticker = '')
    {
        // get subscribers
        $subscribers = NotificationEvent::whereName($mode)->first()?->subscribers ?? [];
        $stickers = $sticker ? collect(config('sticker.line.'.$sticker)) : null;
        foreach ($subscribers as $subscriber) {
            $messages = [];
            if (!$subscriber->line_active) {
                continue;
            }
            $messages[] = $this->bot->buildTextMessage(text: $text, placeholders: ['username' => $subscriber->profile['notification']['nickname']]);
            if ($stickers) {
                $sticker = $stickers->random();
                $messages[] = $this->bot->buildStickerMessage(packageId: $sticker['packageId'], stickerId: $sticker['stickerId']);
            }
            $this->bot->pushMessage(userId: $subscriber->profile['notification']['user_id'], messages: $messages, mode: $mode);
        }
    }

    public function notifyLabSubscribers(string $mode, string $text, string $sticker = '')
    {
        // get subscribers
        $subscribers = NotificationEvent::whereName($mode)->first()?->subscribers ?? [];
        $stickers = $sticker ? collect(config('sticker.line.'.$sticker)) : null;
        foreach ($subscribers as $subscriber) {
            $messages = [];
            if (!$subscriber->line_active || Cache::has("notify-lab-user-{$subscriber->id}")) {
                continue;
            }
            $messages[] = $this->bot->buildTextMessage(text: $text, placeholders: ['username' => $subscriber->profile['notification']['nickname']]);
            if ($stickers) {
                $sticker = $stickers->random();
                $messages[] = $this->bot->buildStickerMessage(packageId: $sticker['packageId'], stickerId: $sticker['stickerId']);
            }
            $this->bot->pushMessage(userId: $subscriber->profile['notification']['user_id'], messages: $messages, mode: $mode);
            Cache::put(key: "notify-lab-user-{$subscriber->id}", value: true, ttl: now()->addMinutes(5));
        }
    }
}
