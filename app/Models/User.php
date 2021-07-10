<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'profile' => 'array',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * A user may be assigned many roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Assign a new role to the user.
     *
     * @param  mixed  $role
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::whereName($role)->firstOrCreate(['name' => $role]);
        }

        $this->roles()->syncWithoutDetaching($role);

        unset($this->roles); // reload for new role
        Cache::put("uid-{$this->id}-abilities", $this->roles->map->abilities->flatten()->pluck('name')->unique(), config('session.lifetime') * 60);
        Cache::put("uid-{$this->id}-role-names", $this->roles->pluck('name'), config('session.lifetime') * 60);
    }

    public function getTimezoneAttribute()
    {
        return 'asia/bangkok';
    }

    public function getAbilitiesAttribute()
    {
        return Cache::remember("uid-{$this->id}-abilities", config('session.lifetime') * 60, function () {
            unset($this->roles); // reload for new role

            // if unique() is not activated then the output is an array
            // but the output is an associated array so, provide
            // flatten() to garantee output awlays an array
            return $this->roles->map->abilities->flatten()->pluck('name')->unique()->flatten();
        });
    }

    public function getRoleNamesAttribute()
    {
        return Cache::remember("uid-{$this->id}-role-names", config('session.lifetime') * 60, function () {
            unset($this->roles);

            return $this->roles->pluck('name');
        });
    }

    public function getFullNameAttribute()
    {
        return $this->profile['full_name'];
    }

    public function getTelNoAttribute()
    {
        return $this->profile['tel_no'];
    }
}
