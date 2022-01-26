<template>
    <div
        class="bg-white rounded shadow-sm p-4 mb-4 sm:mb-6 md:mb-12"
    >
        <h2 class="font-semibold text-thick-theme-light border-b-2 border-dashed pb-2">
            ตั้งค่าการแจ้งเตือน
        </h2>
        <div>
            <p class="mt-2 italic p-2">
                ๏ ทำการขอรหัสยืนยัน จากนั้นสแกน QR Code หรือคลิก <span class="underline">LINE Add Friend</span> เพื่อเพิ่มเพื่อนแล้วใส่รหัสยืนยันในแชทเพื่อระบุตัวตน
            </p>
            <SpinnerButton
                :spin="form.processing"
                class="btn-dark w-full mt-4"
                @click="requestVerificationCode"
            >
                ขอรหัสยืนยัน
            </SpinnerButton>
            <div class="mt-12 p-8 border-2 border-thick-theme-light rounded">
                <img
                    class="w-40 md:w-60 h-40 md:h-60 mx-auto"
                    :src="route('home') + '/image/yuzu-line-bot.png'"
                    alt="🍊"
                >
                <a
                    href="#"
                    target="_blank"
                    class="btn btn-bitter w-40 md:w-60 mt-12 mx-auto flex justify-center items-center cursor-pointer"
                >
                    <icon
                        name="line-app"
                        class="w-6 h-6 mr-2"
                    />Add Friend
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
// import FormInput from '@/Components/Controls/FormInput';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import Icon from '@/Components/Helpers/Icon';
import { useForm } from '@inertiajs/inertia-vue3';

const form = useForm({});

const requestVerificationCode = () => {};

</script>