<template>
    <InertiaHead>
        <title>Policies and Terms</title>
    </InertiaHead>
    <div class=" bg-soft-theme-light w-full p-12">
        <h1 class=" font-semibold text-xl text-dark-theme-light">
            นโยบายความเป็นส่วนตัว
        </h1>
        <div class=" p-4 bg-white shadow rounded mt-4">
            <ul class=" list-outside list-disc pl-4">
                <!-- <li class="my-2">
                    เราเก็บข้อมูลส่วนบุคคลของท่านจาก LINE หรือ Telegram ตามความยินยอมของท่าน
                </li> -->
                <li class="my-2">
                    เราเก็บข้อมูลส่วนบุคคลของท่านจากระบบพิสูจน์ตัวตนขององค์กรที่ท่านใช้เพื่อเปิดสิทธิ์การใช้งาน
                </li>
                <li class="my-2">
                    เราเก็บข้อมูลพฤติกรรมการใช้งานของท่าน
                </li>
            </ul>
        </div>
        <div class=" p-4 bg-white shadow rounded mt-4">
            <ul class=" list-outside list-disc pl-4">
                <li class="my-2">
                    เราใช้ข้อมูลส่วนบุคคลของท่านเพื่อป้องกันการใช้งานโดยไม่ได้รับอนุญาต
                </li>
                <li class="my-2">
                    เราใช้ข้อมูลส่วนบุคคลของท่านเพื่อให้บริการการสื่อสารระหว่างท่านกับระบบ
                </li>
                <li class="my-2">
                    เราใช้ข้อมูลพฤติกรรมการใช้งานของท่านเพื่อช่วยเหลือท่านในกรณีเกิดปัญหาการใช้งานและปรับปรุงบริการให้ดียิ่งขึ้น
                </li>
            </ul>
        </div>

        <div class=" p-4 bg-white shadow rounded mt-4">
            <ul class=" list-outside list-disc pl-4">
                <li class="my-2">
                    เราไม่เปิดเผยหรือถ่ายโอนข้อมูลส่วนบุคคลและพฤติกรรมการใช้งานของท่านไปยังบุคคลภายนอกทุกกรณี เว้นแต่เป็นไปตามข้อบังคับทางกฎหมาย
                </li>
                <li class="my-2">
                    เราไม่แสดงข้อมูลส่วนบุคคลของท่านโดยปริยาย แต่จะแสดงเมื่อผู้เกี่ยวข้องร้องขอเพื่อติดต่อท่านในกรณีจำเป็นเท่านั้น
                </li>
                <li class="my-2">
                    เราไม่มีการใช้คุกกี้ (Cookies) เพื่อติดตาม แสดงโฆษณา หรือรวบรวมการใช้งานเว็บไซต์อื่นๆ ของท่านในระบบของเรา มีการใช้งานคุกกี้ที่จำเป็นต่อการจดจำบัญชีผู้ใช้ของท่านเท่านั้น
                </li>
            </ul>
        </div>

        <h2 class=" font-semibold text-xl text-dark-theme-light mt-6">
            ข้อตกลงการใช้งาน
        </h2>

        <div class=" p-4 bg-white shadow rounded mt-4">
            <ul class=" list-outside list-disc pl-4">
                <li class="my-2">
                    ไม่อนุญาตให้ท่านใช้บัญชีผู้ใช้งานร่วมกัน ตัวท่านไม่ใช้บัญชีของผู้อื่นและไม่มอบบัญชีของท่านให้ผู้อื่นใช้
                </li>
                <li class="my-2">
                    ขณะนี้ระบบอยู่ระหว่างการพัฒนา หากพบข้อผิดพลาดในการใช้งาน ท่านสามารถแจ้งปัญหาได้ที่ผู้ดูแลระบบ ทางทีมพัฒนาต้องขออภัยในความไม่สะดวก
                </li>
                <li class="my-2">
                    ความเห็นและข้อเสนอแนะของท่านสามารถช่วยให้เราพัฒนาการให้บริการได้ดียิ่งขึ้น
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { useCheckSessionTimeout } from '@/Functions/useCheckSessionTimeout';
export default {
    setup () {
        useCheckSessionTimeout();
    }
};
</script>