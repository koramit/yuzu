<?php

namespace App\Managers;

class EmployeeManager
{
    protected $api;

    public function __construct()
    {
        $this->api = app()->make('App\Contracts\AuthenticationAPI');
    }

    public function manage($id)
    {
        $data = $this->api->getUserById($id);

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
