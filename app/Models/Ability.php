<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Ability extends Model
{
    use HasFactory;

    public static function clearCache()
    {
        $userCount = User::count();
        for ($i = 1; $i <= $userCount; $i++) {
            Cache::forget("uid-{$i}-abilities");
        }
    }

    /**
     * An ability may have many roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
}
