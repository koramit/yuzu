<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitAction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $actions = [
        'authorize',
        'cancel',
        'create',
        'discharge',
        'enlist_exam',
        'enlist_screen',
        'enlist_swab',
        'enqueue',
        'fill-hn',
        'print',
        'recreate',
        'sign_opd_card',
        'unlock',
        'update',
        'view',
    ];

    protected $actionLabel = [
        'authorize' => 'เปิด Visit',
        'cancel' => 'ยกเลิก',
        'create' => 'สร้าง',
        'discharge' => 'จำหน่าย',
        'enlist_exam' => 'ส่งตรวจ',
        'enlist_screen' => 'คัดกรอง',
        'enlist_swab' => 'ส่ง swab',
        'enqueue' => 'ทำ SI Flow',
        'fill-hn' => 'ทำ HN',
        'print' => 'พิมพ์',
        'recreate' => 'สร้างจากยกเลิก',
        'sign_opd_card' => 'ลงชื่อ',
        'unlock' => 'แก้ไข',
        'update' => 'บันทึก',
        'view' => 'อ่าน',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute()
    {
        return $this->actionLabel[$this->action] ?? 'undefined';
    }
}
