<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientVaccination extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'vaccinated_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function getBrandAttribute()
    {
        return config('services.vaccine_brands')[$this->brand_id] ?? null;
    }
}
