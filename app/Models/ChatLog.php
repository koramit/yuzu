<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'payload' => AsArrayObject::class,
    ];

    public function scopeSwabNotifications($query)
    {
        $query->select('id', 'platform_user_id')->where('mode', 6);
    }

    public function scopeTodaySwabNotifications($query)
    {
        $query->swabNotifications()
            ->where('created_at', '>', now()->addHours(-12)->format('Y-m-d H:i:s')); // last 12 hours
    }
}
