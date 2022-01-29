<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Managers\LINEMessagingManager;
use App\Managers\VerificationCodeManager;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class LINEWebhooksController extends Controller
{
    protected $user;
    protected $bot;

    /**
     * LINE request limit as of 2022-01-26
     * https://developers.line.biz/en/reference/messaging-api/#rate-limits
     * Other API endpoints 2,000 requests per second*.
     */
    public function __invoke()
    {
        // validate requests are from LINE
        $hash = hash_hmac('sha256', Request::getContent(), config('services.line.bot_secret'), true);
        $signature = base64_encode($hash);

        if (Request::header('x-line-signature') !== $signature) {
            abort(404);
        }

        if (! Request::has('events')) { // this should never happend
            Log::warning('LINE bad response');

            abort(400);
        }

        $this->bot = new LINEMessagingManager();

        foreach (Request::input('events') as $event) {
            $this->bot->log(
                platformUserId: $event['source']['userId'],
                payload: $event
            );

            $this->user = User::where('profile->notification->user_id', $event['source']['userId'])->first();
            if ($event['type'] == 'follow') {
                $this->follow($event);
            } elseif ($event['type'] == 'unfollow') {
                $this->unfollow($event);
            } elseif ($event['type'] == 'message') {
                $this->message($event);
            } elseif ($event['type'] == 'unsend') {
                //
            } else {
                // unhandle type
            }
        }
    }

    protected function follow(array $event)
    {
        // not verified yet
        if (! $this->user) {
            $profile = $this->bot->getProfile($event['source']['userId']);
            $this->bot->replyUnauthorized(userId: $event['source']['userId'], replyToken: $event['replyToken'], username: $profile['displayName']);

            return;
        }

        // still friend, just scan qrcode or click link add friend
        if ($this->user->profile['notification']['active']) {
            $this->bot->replyGreeting(userId: $event['source']['userId'], replyToken: $event['replyToken'], username: $this->user->profile['full_name']);
        }

        // unfriended then ask for the make up - refollow
        $this->user->update(['profile->notification->active' => true]);
        $this->bot->replyRefollow(userId: $event['source']['userId'], replyToken: $event['replyToken']);
    }

    protected function unfollow($event)
    {
        if ($this->user) {
            $this->user->update(['profile->notification->active' => false]);
        } else {
            Log::notice('guest '.$event['source']['userId'].' unsubscribed LINE bot');
        }
    }

    protected function message($event)
    {
        if ($this->user) {
            $this->bot->handleMessageEvent(event: $event, user: $this->user);

            return;
        }

        // if no user and message is not tverification code
        if (
            $event['message']['type'] !== 'text' ||
            !is_numeric($event['message']['text'])
        ) {
            $profile = $this->bot->getProfile($event['source']['userId']);
            $this->bot->replyUnauthorized(userId: $event['source']['userId'], replyToken: $event['replyToken'], username: $profile['displayName']);

            return;
        }

        // if no user and message is possible verification code
        $manager = new VerificationCodeManager;
        if (! $user = $manager->verifyCode(code: $event['message']['text'], issue: 'line-verification')) {
            $this->bot->replyInvalidVerificationCode(userId: $event['source']['userId'], replyToken: $event['replyToken']);

            return;
        }

        // code verified
        $this->bot->updateProfile(userId: $event['source']['userId'], user: $user);
        $this->bot->replyGreeting(userId: $event['source']['userId'], replyToken: $event['replyToken'], username: $user->profile['full_name']);
    }
}
