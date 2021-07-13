<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visit extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'date_visit' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'form' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'hn',
        'patient_name',
        'updated_at_for_humans',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator_id', 'id');
    }

    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updater_id', 'id');
    }

    public function approver()
    {
        return $this->belongsTo('App\Models\User', 'approver_id', 'id');
    }

    public function setPatientTypeAttribute($value)
    {
        $items = [
            'บุคคลทั่วไป' => 1,
            'เจ้าหน้าที่ศิริราช' => 2,
        ];
        $this->attributes['patient_type'] = $items[$value];
    }

    public function getPatientTypeAttribute()
    {
        if (! isset($this->attributes['patient_type'])) {
            return null;
        }

        $items = [
            1 => 'บุคคลทั่วไป',
            2 => 'เจ้าหน้าที่ศิริราช',
        ];

        return $items[$this->attributes['patient_type']];
    }

    public function setScreenTypeAttribute($value)
    {
        $items = [
            'เริ่มตรวจใหม่' => 1,
            'นัดมา swab' => 2,
            'นัดมา swab day 7' => 3,
            'นัดมา swab day 14' => 4,
        ];
        $this->attributes['screen_type'] = $items[$value];
    }

    public function getScreenTypeAttribute()
    {
        if (! isset($this->attributes['screen_type'])) {
            return null;
        }

        $items = [
            '',
            'เริ่มตรวจใหม่',
            'นัดมา swab',
            'นัดมา swab day 7',
            'นัดมา swab day 14',
        ];

        return $items[$this->attributes['screen_type']];
    }

    public function getHnAttribute()
    {
        return $this->patient ? $this->patient->hn : $this->form['patient']['hn'];
    }

    public function getPatientNameAttribute()
    {
        return $this->patient ? $this->patient->full_name : $this->form['patient']['name'];
    }

    public function getUpdatedAtForHumansAttribute()
    {
        return $this->updated_at->locale('th_TH')->diffForHumans(now());
    }
}
