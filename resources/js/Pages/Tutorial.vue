<template>
    <Head>
        <title>แนะนำการใช้งาน</title>
    </Head>
    <div class=" bg-soft-theme-light w-full p-4 md:p-12">
        <h1 class=" font-semibold text-xl text-dark-theme-light">
            แนะนำการใช้งาน
        </h1>
        <div class=" p-4 bg-white shadow rounded mt-4">
            <p>ARI คลินิกโรงพยาบาลศิริราช ให้บริการแจ้งเตือนคิวสวอปผ่านทาง LINE official account สำหรับบุคลากรและนักศึกษาในคณะฯ ที่มารับบริการ โดยสามารถเปิดใช้งานได้ด้วยตนเองตามขั้นตอนดังนี้</p>
            <ul class="list-outside list-decimal pl-4 mt-6">
                <li class="my-6">
                    Login ระบบ ARI คลินิก เพื่อตั้งค่าการแจ้งเตือนได้ที่ <a
                        :href="route('login')"
                        target="_blank"
                        class="text-blue-400"
                        v-text="route('login')"
                    />  ด้วย Siriraj AD (รหัสเข้า eDoc/SiIT)
                </li>
                <li class="my-6">
                    เมื่อ Login แล้วจะอยู่ที่หน้า <span class="font-semibold">"ตั้งค่า"</span> หากพบเป็นหน้าอื่นสามารถไปยังหน้าตั้งค่าได้จากเมนูมุมบนขวา
                    <img
                        class="mx-auto lg:ml-12 w-80 rounded shadow"
                        :src="route('home')+'/image/tutorial/personal-menu.png'"
                        alt=""
                    >
                </li>
                <li class="my-6">
                    ทำการตั้งค่าการแจ้งเตือนผ่านทาง LINE ตามคำแนะนำในระบบ
                    <img
                        class="mx-auto lg:ml-12 w-80 rounded shadow"
                        :src="route('home')+'/image/tutorial/link-line.png'"
                        alt=""
                    >
                </li>
                <li class="my-6">
                    ทำการยืนยัน HN ในหน้าการตั้งค่า
                    <img
                        class="mx-auto lg:ml-12 w-80 rounded shadow"
                        :src="route('home')+'/image/tutorial/link-patient.png'"
                        alt=""
                    >
                </li>
                <li class="my-6">
                    เมื่อทำการตั้งค่าการแจ้งเตือนและยืนยัน HN สำเร็จเรียบร้อยแล้ว สามารถสอบถามลำดับคิวทำสวอปของท่าน โดยการพิมพ์คำว่า <span class="font-semibold">"คิวตรวจ"</span> ส่งมาใน LINE
                    <img
                        class="mx-auto lg:ml-12 w-80 rounded shadow"
                        :src="route('home')+'/image/tutorial/get-swab-queue.png'"
                        alt=""
                    >
                </li>
                <li class="my-6">
                    เมื่อใกล้ถึงคิวของท่านจะมีการแจ้งเตือนผ่านทาง LINE
                    <img
                        class="mx-auto lg:ml-12 w-80 rounded shadow"
                        :src="route('home')+'/image/tutorial/calling-queue-notify.png'"
                        alt=""
                    >
                </li>
            </ul>
        </div>
        <div class=" p-4 bg-white shadow rounded mt-4">
            <p>ข้อควรทราบ</p>
            <ul class="list-outside list-disc pl-4 mt-6">
                <li class="my-6">
                    ท่านต้องมารับการตรวจคัดกรองที่ ARI คลินิกก่อนเสมอ
                </li>
                <li class="my-6">
                    คิวทำสวอปจะเป็นคนละคิวกับที่ท่านได้เมื่อตอนเปิดสิทธิ์การรักษา โดยคิวนี้จะได้เมื่อพยาบาลคัดกรองหรือแพทย์สั่งสวอป
                </li>
                <li class="my-6">
                    การตั้งค่าการแจ้งเตือนและการยืนยัน HN ทำเพียงครั้งเดียวเท่านั้น หากท่านมีนัดสวอปซ้ำก็ไม่จำเป็นต้องทำการตั้งค่าเหล่านี้อีก
                </li>
                <li class="my-6">
                    หากท่านลบแชทแล้วหา LINE นี้ไม่เจอ สามารถสแกน QR Code หรือคลิก Add Friend ได้ที่หน้า <span class="font-semibold">ตั้งค่า</span>
                </li>
                <li class="my-6">
                    หากท่าน Block LINE นี้ ทางคลินิกจะไม่สามารถทำการแจ้งเตือนท่านได้
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { useCheckSessionTimeout } from '@/Functions/useCheckSessionTimeout';
import { useRemoveLoader } from '@/Functions/useRemoveLoader';
import { Head } from '@inertiajs/inertia-vue3';

useCheckSessionTimeout();
useRemoveLoader();
</script>