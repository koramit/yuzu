<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Visit extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'date_visit' => 'date',
        'enlisted_screen_at' => 'datetime',
        'enlisted_exam_at' => 'datetime',
        'enlisted_swab_at' => 'datetime',
        'discharged_at' => 'datetime',
        'authorized_at' => 'datetime',
        'attached_opd_card_at' => 'datetime',
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
        'title',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator_id', 'id');
    }

    public function actions()
    {
        return $this->hasMany('App\Models\VisitAction', 'visit_id', 'id');
    }

    public function versions()
    {
        return $this->hasMany('App\Models\VisitFormVersion', 'visit_id', 'id');
    }

    public function vaccinations()
    {
        return $this->hasMany('App\Models\PatientVaccination', 'patient_id', 'patient_id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            if (is_numeric($search)) {
                $query->where('hn_stored', 'like', "{$search}%");
            } else {
                $query->where('name_stored', 'like', "%{$search}%");
            }
        });
    }

    public function scopeWithPublicPatientWalkinATKPosWithoutPCR($query, $dateVisit)
    {
        $query->orWhere(function ($query) use ($dateVisit) {
            $query->whereDateVisit($dateVisit)
              ->wherePatientType(1)
              ->whereScreenType(1)
              ->whereStatus(4)
              ->where('form->exposure->atk_positive', true)
              ->where('form->management->manage_atk_positive', 'ไม่ต้องการยืนยันผลด้วยวิธี PCR แพทย์พิจารณาให้ยาเลย (หากต้องการเข้าระบบ ให้ติดต่อ 1330 เอง)');
        });
    }

    public function setPatientTypeAttribute($value)
    {
        if (! $value) {
            $this->attributes['patient_type'] = null;
        } else {
            $items = [
                'บุคคลทั่วไป' => 1,
                'เจ้าหน้าที่ศิริราช' => 2,
            ];
            $this->attributes['patient_type'] = $items[$value] ?? null;
        }
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
        if (! $value) {
            $this->attributes['screen_type'] = null;
        } else {
            $items = [
                'เริ่มตรวจใหม่' => 1,
                'นัดมา swab ครั้งแรก' => 2,
                'นัดมา swab ซ้ำ' => 3,
            ];
            $this->attributes['screen_type'] = $items[$value] ?? null;
        }
    }

    public function getScreenTypeAttribute()
    {
        if (! isset($this->attributes['screen_type'])) {
            return null;
        }

        $items = [
            '',
            'เริ่มตรวจใหม่',
            'นัดมา swab ครั้งแรก',
            'นัดมา swab ซ้ำ',
        ];

        return $items[$this->attributes['screen_type']];
    }

    public function setStatusAttribute($value)
    {
        if (! $value) {
            $this->attributes['status'] = null;
        } else {
            $items = [
                'screen' => 1,
                'exam' => 2,
                'swab' => 3,
                'discharged' => 4,
                'canceled' => 5,
                'appointment' => 6,
                'enqueue_swab' => 7,
            ];
            $this->attributes['status'] = $items[$value] ?? null;
        }
    }

    public function getStatusAttribute()
    {
        if (! isset($this->attributes['status'])) {
            return null;
        }

        $items = [
            '',
            'screen',
            'exam',
            'swab',
            'discharged',
            'canceled',
            'appointment',
            'enqueue_swab',
        ];

        return $items[$this->attributes['status']];
    }

    public function getStatusIndexRouteAttribute()
    {
        $items = [
            '',
            'visits.screen-list',
            'visits.exam-list',
            'visits.swab-list',
            'visits.today-list', // 'discharged',
            'visits.today-list', //'canceled',
            'visits.screen-list', // 'appointment
            'visits.swab-list', // swab_manage
        ];

        return $items[$this->attributes['status']];
    }

    public function getHnAttribute()
    {
        return $this->form['patient']['hn'] ?? null;
    }

    public function getPatientNameAttribute()
    {
        return $this->form['patient']['name'] ?? null;
    }

    public function setPatientNameAttribute($value)
    {
        $this->forceFill(['form->patient->name' => $value]);
    }

    public function getUpdatedAtForHumansAttribute()
    {
        return $this->updated_at->locale('th_TH')->diffForHumans(now());
    }

    public function getCreatedAtForHumansAttribute()
    {
        return $this->created_at->locale('th_TH')->diffForHumans(now());
    }

    public function getEnlistedScreenAtForHumansAttribute()
    {
        return $this->enlisted_screen_at->locale('th_TH')->diffForHumans(now());
    }

    public function getEnlistedExamAtForHumansAttribute()
    {
        return $this->enlisted_exam_at->locale('th_TH')->diffForHumans(now());
    }

    public function getEnlistedSwabAtForHumansAttribute()
    {
        return $this->enlisted_swab_at ? $this->enlisted_swab_at->locale('th_TH')->diffForHumans(now()) : null;
    }

    public function getTitleAttribute()
    {
        return $this->patient_name.'@'.$this->date_visit->format('d M Y');
    }

    public function getAgeAtVisitAttribute()
    {
        if (! $this->patient || ! $this->patient->dob || ! $this->date_visit) {
            return null;
        }

        $ageInMonths = $this->date_visit->diffInMonths($this->patient->dob);
        if ($ageInMonths < 12) {
            return $ageInMonths;
        }

        return $this->date_visit->diffInYears($this->patient->dob);
    }

    public function getAgeAtVisitUnitAttribute()
    {
        if (! $this->patient || ! $this->patient->dob || ! $this->date_visit) {
            return null;
        }

        $ageInYears = $this->date_visit->diffInYears($this->patient->dob);
        if ($ageInYears >= 1) {
            return 'ปี';
        }

        return 'เดือน';
    }

    public function getAgeAtVisitLabelAttribute()
    {
        if (! $this->patient) {
            return null;
        }

        return trim($this->age_at_visit.' '.$this->age_at_visit_unit);
    }

    public function getPatientDepartmentAttribute()
    {
        if (! $this->patient) {
            return null;
        }

        if ($this->age_at_visit_unit === 'เดือน') {
            return 'กุมารฯ';
        }

        return $this->age_at_visit < 18 ? 'กุมารฯ' : 'อายุรศาสตร์';
    }

    public function getReadyToPrintAttribute()
    {
        return $this->status !== 'canceled' && ($this->discharged_at || $this->enlisted_swab_at) && $this->patient_id;
    }

    public function getTelNoAttribute()
    {
        return $this->form['patient']['tel_no'].' '.$this->form['patient']['tel_no_alt'];
    }

    public function getGenderAttribute()
    {
        if (! $this->patient) {
            return null;
        }

        return $this->patient->gender;
    }

    public function getSwabAtAttribute()
    {
        return $this->form['management']['swab_at'] ?? null;
    }

    public function getSpecimenNoAttribute()
    {
        return $this->form['management']['specimen_no'] ?? null;
    }

    public function getContainerSwabAtAttribute()
    {
        return $this->form['management']['container_swab_at'] ?? null;
    }

    public function getContainerNoAttribute()
    {
        return $this->form['management']['container_no'] ?? null;
    }

    public function getTrackAttribute()
    {
        return $this->form['patient']['track'] ?? null;
    }

    public function getMobilityAttribute()
    {
        return $this->form['patient']['mobility'] ?? null;
    }

    public function getStationAttribute()
    {
        if ($this->status === 'screen' || $this->status === 'appointment') {
            return 'ห้องคัดกรอง';
        } elseif ($this->status === 'swab') {
            return 'จัดกระติก';
        } elseif ($this->status === 'enqueue_swab') {
            return 'ห้อง swab';
        } elseif ($this->status === 'discharged') {
            return 'จำหน่าย';
        } elseif ($this->status === 'canceled') {
            return 'ยกเลิก';
        } elseif ($this->authorized_at) {
            return 'ห้องตรวจ';
        } elseif ($this->enqueued_at) {
            return 'เวชระเบียน';
        } else {
            return 'ธุรการ';
        }
    }

    public function getVaccinationTextAttribute()
    {
        if ($this->form['vaccination']['unvaccinated']) {
            return 'ไม่เคยฉีดวัคซีน';
        }

        $text = '';
        foreach (['Sinovac', 'Sinopharm', 'AstraZeneca', 'Moderna', 'Pfizer'] as $vaccine) {
            if ($this->form['vaccination'][$vaccine]) {
                $text .= $vaccine.' + ';
            }
        }

        $text = trim($text, ' + ');
        $text .= ' รวม ' . $this->form['vaccination']['doses'] . ' เข็ม';
        if ($this->form['vaccination']['date_latest_vacciniated']) {
            $text .= ' เมื่อ ' . Carbon::create($this->form['vaccination']['date_latest_vacciniated'])->format('d/m/y');
        }

        return $text;
    }

    public function getMenstruationAttribute()
    {
        if (!$this->patient || $this->patient->profile['gender'] === 'male') {
            return null;
        }

        return $this->form['patient']['menstruation'] ?? null;
    }

    public function getWeightAttribute()
    {
        $weight = $this->form['patient']['weight'] ?? null;
        if ($weight) {
            return $weight;
        }

        $note = $this->form['note'] ?? null;
        if (!$note) {
            return null;
        }

        $note = str_replace("\n", ' ', $note);
        $note = str_replace('(', ' ', $note);
        $note = str_replace('นน .', 'นน', $note);
        $note = str_replace('นนฝ', 'นน', $note);
        $note = preg_replace('/\s\s+/', ' ', $note); // multiple spaces to one
        $phases = explode(' ', $note);
        $weight = null;
        $phases[] = 'empty';
        for ($i = 0; $i < count($phases) -1 ; $i++) {
            $phase = strtolower($phases[$i]);
            if (
                $phase == 'นน' ||
                $phase == 'นน.' ||
                $phase == 'bw' ||
                $phase == 'น้ำหนัก' ||
                $phase == 'นำ้หนัก' ||
                $phase == 'นำหนัก'
            ) {
                $weight = strtolower($phases[$i+1]);
                $weight = str_replace('kg.', '', $weight);
                $weight = str_replace('kg', '', $weight);
                $weight = str_replace('กก.', '', $weight);
                $weight = str_replace('กก', '', $weight);
                break;
            } elseif (
                str_starts_with($phase, 'นน.') ||
                str_starts_with($phase, 'นน') ||
                str_starts_with($phase, 'น้ำหนัก') ||
                str_starts_with($phase, 'นำหนัก') ||
                str_starts_with($phase, 'นำ้หนัก') ||
                str_starts_with($phase, 'bw') ||
                str_ends_with($phase, 'kg.') ||
                str_ends_with($phase, 'kg') ||
                str_ends_with($phase, 'กก.') ||
                str_ends_with($phase, 'กก')
            ) {
                $weight = str_replace('นน.', '', $phase);
                $weight = str_replace('นน', '', $weight);
                $weight = str_replace('bw', '', $weight);
                $weight = str_replace('น้ำหนัก', '', $weight);
                $weight = str_replace('นำหนัก', '', $weight);
                $weight = str_replace('นำ้หนัก', '', $weight);
                $weight = str_replace('kg.', '', $weight);
                $weight = str_replace('kg', '', $weight);
                $weight = str_replace('กก.', '', $weight);
                $weight = str_replace('กก', '', $weight);
                break;
            }
        }

        if ($weight && is_numeric($weight)) {
            return $weight;
        }

        return null;
    }

    public function getAtkPositiveCaseAttribute()
    {
        return $this->patient_type === 'บุคคลทั่วไป'
            && $this->screen_type === 'เริ่มตรวจใหม่'
            && $this->form['exposure']['atk_positive']
            && str_starts_with(($this->form['management']['manage_atk_positive'] ?? ''), 'ไม่ต้องการยืนยันผลด้วยวิธี PCR');
    }

    public function getSccCertSentHashAttribute()
    {
        $hashData = [
            'recommendation' => $this->form['evaluation']['recommendation'] ?? null,
            'date_quarantine_end' => $this->form['evaluation']['date_quarantine_end'] ?? null,
            'date_reswab' => $this->form['evaluation']['date_reswab'] ?? null,
        ];
        return hash_hmac('sha256', json_encode($hashData), config('app.key'));
    }
}
