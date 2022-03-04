<?php

namespace App\Traits;

trait MedicalCertifiable
{
    protected function getThaiDate($dateStr)
    {
        if (! $dateStr) {
            return null;
        }

        $thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

        $ymd = explode('-', $dateStr);

        return ((int) $ymd[2]).' '.($thaiMonths[(int) $ymd[1]]).' '.(((int) $ymd[0]) + 543);
    }

    protected function getRecommendation($recommendation)
    {
        if (! $recommendation) {
            return null;
        }

        if ($recommendation === 'ไปทำงานได้') {
            return 'ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลาทุกวัน';
        } elseif ($recommendation === 'ATK positive') {
            return "กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ {$this->daysCriteria} วัน";
        } elseif ($recommendation === 'กักตัว') {
            return "กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ {$this->daysCriteria} วัน"; // CR 220124 change 14 => 10 days
        } elseif ($recommendation === 'กักตัวนัดสวอบซ้ำ') {
            return "กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ {$this->daysCriteria} วัน และนัดมาตรวจซ้ำ"; // CR 220124 change 14 => 10 days
        } else {
            return '!!!';
        }
    }
}
