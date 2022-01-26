<?php

namespace App\Http\Controllers;

use App\Managers\VerificationCodeManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class RequestVerificationCodeController extends Controller
{
    public function __invoke()
    {
        Request::validate([
            'issue' => 'required|string',
        ]);

        return [
            'code' => (new VerificationCodeManager)->getCode(issue: Request::input('issue'), user: Auth::user())
        ];
    }
}
