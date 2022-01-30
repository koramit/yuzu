<?php

namespace App\Models;

use App\Traits\DataCryptable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Patient extends Model
{
    use HasFactory, DataCryptable, SoftDeletes;

    protected $encryptedKey = 'hn';

    protected $fillable = [
        'hn',
        'slug',
        'profile',
    ];

    /**
     * Set field 'hn'.
     *
     * @param string $value
     */
    public function setHnAttribute($value)
    {
        $this->attributes['hn'] = $this->encryptField($value);
        $this->attributes['mini_hash'] = $this->miniHash($value);
    }

    /**
     * Get field 'hn'.
     *
     * @return string
     */
    public function getHnAttribute()
    {
        return $this->decryptField($this->attributes['hn']);
    }

    /**
     * Get field 'dob'.
     *
     * @return date
     */
    public function getDobAttribute()
    {
        if (! $this->profile['dob']) {
            return null;
        }

        return Carbon::create($this->profile['dob']);
    }

    public function setProfileAttribute($data)
    {
        $this->attributes['profile'] = $this->encryptField(empty($data) ? null : json_encode($data));
    }

    public function getProfileAttribute()
    {
        return json_decode($this->decryptField($this->attributes['profile']), true);
    }

    public function getFullNameAttribute()
    {
        return implode(' ', [
            $this->profile['title'],
            $this->profile['first_name'],
            $this->profile['last_name'],
        ]);
    }

    public function getAgeInYearsAttribute()
    {
        if (! $this->dob) {
            return null;
        }

        return now()->diffInYears($this->dob);
    }

    public function getGenderAttribute()
    {
        return ($this->profile['gender'] ?? '') === 'male' ? 'ชาย' : 'หญิง';
    }

    public function getNotificationActiveAttribute()
    {
        return $this->profile['notification']['active'] ?? null;
    }

    public function chatLogs()
    {
        return $this->hasMany(ChatLog::class, 'platform_user_id', 'notification_user_id');
    }

    public function scopeWithNotificationConfig($query)
    {
        $query->addSelect([
            'notification_user_id' => User::select('profile->notification->user_id')
                        ->whereColumn('profile->patient_id', 'patients.id')
                        ->limit(1)
                        ->latest(),
            'notification_active' => User::select('profile->notification->active')
                        ->whereColumn('profile->patient_id', 'patients.id')
                        ->limit(1)
                        ->latest(),
        ]);
    }

    public function scopeWithTodaySwabNotifications($query)
    {
        $query->withNotificationConfig()
            ->with(['chatLogs' => fn ($q) => $q->todaySwabNotifications()]);
    }
}
