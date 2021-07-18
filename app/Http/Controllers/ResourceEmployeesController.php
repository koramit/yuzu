<?php

namespace App\Http\Controllers;

use App\Managers\EmployeeManager;

class ResourceEmployeesController extends Controller
{
    public function __invoke($id)
    {
        return (new EmployeeManager)->manage($id);
    }
}
