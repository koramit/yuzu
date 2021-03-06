<?php

namespace App\Managers;

use App\Models\User;
use App\Models\VerificationCode;

class VerificationCodeManager
{
    protected $issues = [
        'line-verification' => 1
    ];

    public function getCode(string $issue, User $user)
    {
        if (!isset($this->issues[$issue])) {
            // invalid
        }

        // check if user already has unverified code for the issue
        $code = VerificationCode::whereUserId($user->id)
                                ->whereIssue($this->issues[$issue])
                                ->whereVerified(false)
                                ->first();

        if ($code) {
            return $code->code;
        }

        do {
            $code = rand(1000, 9999);
            $duplicateCode = VerificationCode::whereCode($code)
                                        ->whereIssue($this->issues[$issue])
                                        ->whereVerified(false)
                                        ->count();
        } while ($duplicateCode);

        VerificationCode::create([
            'code' => $code,
            'issue' => $issue,
            'user_id' => $user->id,
        ]);

        return $code;
    }

    public function verifyCode(int $code, string $issue)
    {
        $verified = VerificationCode::whereIssue($this->issues[$issue])
                                    ->whereCode($code)
                                    ->whereVerified(false)
                                    ->first();

        if (!$verified) {
            return false;
        }

        $verified->update(['verified' => true]);

        return User::find($verified->user_id);
    }
}
