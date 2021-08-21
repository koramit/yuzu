<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller
{
    public function __invoke()
    {
        $page = Auth::user()->home_page;
        if ($page === 'home') {
            return 'โปรดติดต่อผู้ดูแลระบบเพื่อเปิดสิทธิ์การใช้งาน';
        }
        return Redirect::route($page);
    }
}
