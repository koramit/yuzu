<?php

namespace App\Managers;

use App\Models\ChatLog;
use App\Models\User;
use App\Models\Visit;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        return null; // turn off push for awhile
        try {
            $this->client->post($this->baseEndpoint.'message/push', [
                'to' => $userId,
                'messages' => $messages,
            ]);
        } catch (Exception $e) {
            Log::error('LINE-push-error');
            Log::error($messages);
            Log::error($e->getMessage());
            return;
        }

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
        $messages[] = $this->buildStickerMessage(packageId: 6359, stickerId: collect([11069855, 11069867, 11069868, 11069870])->random());
        $messages[] = $this->buildTextMessage(__('bot.auto_reply'));
        $this->replyMessage(userId: $userId, replyToken: $replyToken, messages: $messages);
    }

    public function replyUnauthorized(string $userId, string $replyToken, string $username)
    {
        $messages[] = $this->buildTextMessage(__('bot.user_not_verified', ['PLACEHOLDER' => $username]));
        $this->replyMessage(userId: $userId, replyToken: $replyToken, messages: $messages);
    }

    public function replyAuto(string $replyToken, User $user)
    {
        if (Cache::has("bot-auto-reply-to-user-{$user->id}")) {
            return;
        }

        $messages[] = $this->buildTextMessage(__('bot.auto_reply'));
        $this->replyMessage(userId: $user->profile['notification']['user_id'], replyToken: $replyToken, messages: $messages);
        Cache::put("bot-auto-reply-to-user-{$user->id}", true, now()->addMinutes(10));
    }

    public function replyRefollow(string $userId, string $replyToken)
    {
        $messages[] = $this->buildTextMessage(__('bot.regreeting'));
        $this->replyMessage(userId: $userId, replyToken: $replyToken, messages: $messages);
    }

    public function replyInvalidVerificationCode(string $userId, string $replyToken)
    {
        $messages[] = $this->buildTextMessage(__('bot.invalid_verification_code'));
        $this->replyMessage(userId: $userId, replyToken: $replyToken, messages: $messages);
    }

    public function buildTextMessage(string $text, array $placeholders = [])
    {
        foreach ($placeholders as $search => $replace) {
            $text = str_replace(":{$search}:", $replace, $text);
        }

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
            'notify_swab_queue' => 6,
            'get_today_lab' => 7,
            'get_today_stat' => 8,
            'notify_drink_water' => 9,
            'notify_clear_patient' => 10,
            'notify_lab_progress' => 11,
            'notify_lab_detected' => 12,
            'notify_lab_finished' => 13,
            'notify_croissant_need_help' => 14,
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
        if ($event['message']['type'] !== 'text') {
            return $this->replyAuto(replyToken: $event['replyToken'], user: $user);
        }

        if (!$reply = (new BotCommandsManager)->handleCommand(cmd: $event['message']['text'], user: $user)) {
            return $this->replyAuto(replyToken: $event['replyToken'], user: $user);
        }

        $messages[] = $this->buildTextMessage(text: $reply['text'], placeholders: ['username' => $user->profile['notification']['nickname']]);
        if (isset($reply['sticker'])) {
            $sticker = collect(config('sticker.line.'.$reply['sticker']))->random();
            $messages[] = $this->buildStickerMessage(packageId: $sticker['packageId'], stickerId: $sticker['stickerId']);
        }
        $this->replyMessage(userId: $user->profile['notification']['user_id'], replyToken: $event['replyToken'], messages: $messages, mode: $reply['mode'] ?? 'reply');
    }
}
