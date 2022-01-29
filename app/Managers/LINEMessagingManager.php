<?php

namespace App\Managers;

use App\Models\ChatLog;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LINEMessagingManager
{
    protected $client;
    protected $baseEndpoint;

    public function __construct()
    {
        $this->baseEndpoint = config('services.line.base_endpoint');
        $this->client = Http::timeout(2)->retry(3, 100)->withToken(config('services.line.bot_token'));
    }

    public function replyMessage(string $userId, string $replyToken, array $messages, string $mode = 'reply')
    {
        $this->client->post($this->baseEndpoint.'message/reply', [
            'replyToken' => $replyToken,
            'messages' => $messages,
        ]);

        $this->log(platformUserId: $userId, payload: $messages, mode: $mode);
    }

    public function pushMessage(string $userId, array $messages, string $mode = 'push')
    {
        $this->client->post($this->baseEndpoint.'message/push', [
            'to' => $userId,
            'messages' => $messages,
        ]);

        $this->log(platformUserId: $userId, payload: $messages, mode: $mode);
    }

    public function getProfile(string $userId)
    {
        $response = $this->client->get($this->baseEndpoint.'profile/'.$userId);

        return $response->json();
    }

    public function replyGreeting(string $userId, string $replyToken, string $username)
    {
        $messages[] = $this->buildTextMessage(__('bot.greeting', ['PLACEHOLDER' => $username]));
        $messages[] = $this->buildTextMessage(__('bot.auto_reply'));
        $messages[] = $this->buildStickerMessage(packageId: 6359, stickerId: collect([11069855, 11069867, 11069868, 11069870])->random());
        $this->replyMessage(userId: $userId, replyToken: $replyToken, messages: $messages);
    }

    public function replyUnauthorized(string $userId, string $replyToken, string $username)
    {
        $messages[] = $this->buildTextMessage(__('bot.user_not_verified', ['PLACEHOLDER' => $username]));
        $this->replyMessage(userId: $userId, replyToken: $replyToken, messages: $messages);
    }

    public function replyAuto(string $token, User $user)
    {
        if (Cache::has("bot-auto-reply-to-user-{$user->id}")) {
            return;
        }

        $messages[] = $this->buildTextMessage(__('bot.auto_reply'));
        $this->replyMessage(userId: $user->profile['notification']['user_id'], replyToken: $token, messages: $messages);
        Cache::put("bot-auto-reply-to-user-{$user->id}", true, now()->addMinutes(10));
    }

    public function replyRefollow(string $userId, string $token)
    {
        $messages[] = $this->buildTextMessage(__('bot.regreeting'));
        $this->replyMessage(userId: $userId, replyToken: $token, messages: $messages);
    }

    public function replyInvalidVerificationCode(string $userId, string $token)
    {
        $messages[] = $this->buildTextMessage(__('bot.invalid_verification_code'));
        $this->replyMessage(userId: $userId, replyToken: $token, messages: $messages);
    }

    public function buildTextMessage(string $text)
    {
        return [
            'type' => 'text',
            'text' => $text
        ];
    }

    public function buildStickerMessage(int $packageId, int $stickerId)
    {
        return [
            'type' => 'sticker',
            'packageId' => $packageId,
            'stickerId' => $stickerId,
        ];
    }

    public function log(string $platformUserId, array $payload, string $mode = 'read')
    {
        $modes = [
            'read' => 1,
            'reply' => 2,
            'push' => 3,
            'get_queue_number' => 4,
            'get_queue_update' => 5,
        ];

        return ChatLog::create([
            'platform' => 1,
            'mode' => $modes[$mode],
            'payload' => $payload,
            'platform_user_id' => $platformUserId,
        ]);
    }

    public function updateProfile(string $userId, User $user)
    {
        $profile = $this->getProfile(userId: $userId);

        return $user->update([
            'profile->notification->provider' => 'line',
            'profile->notification->user_id' => $profile['userId'],
            'profile->notification->nickname' => $profile['displayName'],
            'profile->notification->avatar' => $profile['pictureUrl'] ?? null,
            'profile->notification->status' => $profile['statusMessage'] ?? null,
            'profile->notification->active' => true,
        ]);
    }

    public function handleMessageEvent(array $event, User $user)
    {
        $cmds = collect(['คิวตรวจ']);

        if (!($event['message']['type'] === 'text') || !$cmds->contains($event['message']['text'])) {
            $this->replyAuto(token: $event['replyToken'], user: $user);
            return;
        }

        if (!$user->patient_linked) {
            $m = 'กรุณาทำการยืนยัน HN ในระบบก่อน';
        } else {
            $today = now()->tz(7)->format('Y-m-d');
            $visit = Visit::whereDateVisit($today)->wherePatientId($user->profile['patient_id'])->first();
            if (!$visit) {
                $m = 'ยังไม่มีการตรวจของท่านสำหรับวันนี้';
            } elseif (!($visit->form['management']['specimen_no'] ?? null)) {
                $m = 'ท่านยังไม่ได้คิวสำหรับวันนี้ กรุณาลองใหม่ในอีกสักครู่';
            } else {
                $m = 'คิวของท่านในวันนี้คือ #'.$visit->form['management']['specimen_no'];
            }
        }
        $messages[] = $this->buildTextMessage(text: $m);
        $this->replyMessage(userId: $user->profile['notification']['user_id'], replyToken: $event['replyToken'], messages: $messages, mode: 'get_queue_number');
    }
}
