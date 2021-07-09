<?php

namespace App\Contracts;

interface AuthenticationAPI
{
    public function authenticate($login, $password);
}
