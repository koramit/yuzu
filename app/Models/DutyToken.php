<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class DutyToken extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function generate(User $user = null)
    {
        do {
            $unique = true;
            $rand = str_pad(random_int(0, 9999), 4, '0');
            foreach (static::all() as $token) {
                if ($token->toekn === $rand) {
                    $unique = false;
                    break;
                }
            }
        } while (!$unique);

        return static::create(['token' => $rand, 'user_id' => $user?->id]);
    }

    public function setTokenAttribute($value)
    {
        $this->attributes['token'] =  Crypt::encryptString($value);
    }

    public function getTokenAttribute()
    {
        return Crypt::decryptString($this->attributes['token']);
    }
}
