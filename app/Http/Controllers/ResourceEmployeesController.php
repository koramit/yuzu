<?php

namespace App\Http\Controllers;

use App\Contracts\AuthenticationAPI;

class ResourceEmployeesController extends Controller
{
    public function __invoke($id, AuthenticationAPI $api)
    {
        $data = $api->getUserById($id);

        if (! ($data['ok'] ?? false) || ! ($data['found'] ?? false)) {
            return [
                'found' => false,
            ];
        }
        $isMd = str_contains($data['name'], 'พญ.') || str_contains($data['name'], 'นพ.');

        $remark = collect(explode(' ', $data['remark']));
        $position = $remark->first();
        $division = $remark->last();
        if ($position === 'อาจารย์' && $isMd) {
            $position = 'อาจารย์แพทย์';
        }

        return [
            'found' => true,
            'name' => $data['name'],
            'position' => $position,
            'division' => $division,
        ];
    }
}
