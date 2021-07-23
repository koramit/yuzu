<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitFormVersion extends Model
{
    use HasFactory;

    protected $casts = [
        'form' => 'array',
    ];

    protected $guarded = [];
}
