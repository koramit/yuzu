<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitStat extends Model
{
    protected $table = 'visits';

    protected $casts = [
        'date_visit' => 'date',
//        'enlisted_screen_at' => 'datetime',
//        'enlisted_exam_at' => 'datetime',
//        'enlisted_swab_at' => 'datetime',
//        'discharged_at' => 'datetime',
//        'authorized_at' => 'datetime',
//        'attached_opd_card_at' => 'datetime',
//        'submitted_at' => 'datetime',
//        'approved_at' => 'datetime',
        'form' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function vaccinations()
    {
        return $this->hasMany('App\Models\PatientVaccination', 'patient_id', 'patient_id');
    }
}
