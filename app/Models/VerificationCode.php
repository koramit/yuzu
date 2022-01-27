<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function setIssueAttribute($value)
    {
        $issues = [
            'line-verification' => 1
        ];

        $this->attributes['issue'] = $issues[$value] ?? null;
    }
}
