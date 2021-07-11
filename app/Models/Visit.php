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
            'ตามนัด Swab 7 วัน' => 2,
            'ตามนัด Swab 14 วัน' => 3,
            'ตามนัด Reswab 7 วัน' => 4,
            'ตามนัด Reswab 14 วัน' => 5,
        ];
        $this->attributes['screen_type'] = $items[$value];
    }

    public function getScreenTypeAttribute()
    {
        $items = [
            '',
            'เริ่มตรวจใหม่',
            'ตามนัด Swab 7 วัน',
            'ตามนัด Swab 14 วัน',
            'ตามนัด Reswab 7 วัน',
            'ตามนัด Reswab 14 วัน',
        ];

        return $items[$this->attributes['screen_type']];
    }

    public function getPatientNameAttribute()
    {
        return $this->patient ? $this->patient->full_name : $this->form['patient']['name'];
    }
}
