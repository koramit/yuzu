<?php

namespace App\Managers;

use App\Models\ChatLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LINEMessagingManager
{
    protected $client;
    protected $baseEndpoint;

    public function __construct()
    {
        $this->baseEndpoint = config('services.line.base_endpoint');
        $this->client = Http::withToken(config('services.line.bot_token'));
    }

    public function replyMessage(string $replyToken, array $messages)
    {
        $this->client->post($this->baseEndpoint.'message/reply', [
            'replyToken' => $replyToken,
            'messages' => $messages,
        ]);
    }

    public function pushMessage(string $userId, array $messages)
    {
        $this->client->post($this->baseEndpoint.'message/push', [
            'to' => $userId,
            'messages' => $messages,
        ]);
    }

    public function getProfile(string $userId)
    {
        $response = $this->client->get($this->baseEndpoint.'profile/'.$userId);

        return $response->json();
    }

    public function replyGreeting(string $token, string $username)
    {
        $messages[] = $this->buildTextMessage(__('bot.greeting', ['PLACEHOLDER' => $username]));
        $messages[] = $this->buildStickerMessage(packageId: 6359, stickerId: collect([11069855, 11069867, 11069868, 11069870])->random());
        $this->replyMessage($token, $messages);
    }

    public function replyUnauthorized(string $token, string $username)
    {
        $messages[] = $this->buildTextMessage(__('bot.user_not_verified', ['PLACEHOLDER' => $username]));
        $this->replyMessage($token, $messages);
    }

    public function replyAuto(string $token, User $user)
    {
        if (Cache::has("bot-auto-reply-to-user-{$user->id}")) {
            return;
        }

        $messages[] = $this->buildTextMessage(__('bot.auto_reply'));
        $this->replyMessage($token, $messages);
        Cache::put("bot-auto-reply-to-user-{$user->id}", true, now()->addMinutes(10));
    }

    public function replyRefollow(string $token)
    {
        $messages[] = $this->buildTextMessage(__('bot.regreeting'));
        $this->replyMessage($token, $messages);
    }

    public function replyInvalidVerificationCode(string $token)
    {
        $messages[] = $this->buildTextMessage(__('bot.invalid_verification_code'));
        $this->replyMessage($token, $messages);
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

    public function handleMessageEvent(object $event, User $user)
    {
        $cmds = collect(['คิวตรวจ']);

        if (!$event['message']['type'] === 'text' || !$cmds->contains($event['message']['text'])) {
            $this->replyAuto(token: $event['replyToken'], user: $user);
        }

        $messages[] = $this->buildTextMessage(text: 'คิวทำสวอปของท่านในวันนี้คือ #49');
        $this->replyMessage(replyToken: $event['replyToken'], messages: $messages);
    }
}
