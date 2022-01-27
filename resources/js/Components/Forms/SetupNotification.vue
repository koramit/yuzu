<template>
    <div
        class="bg-white rounded shadow-sm p-4 mb-4 sm:mb-6 md:mb-12"
    >
        <h2 class="font-semibold text-thick-theme-light border-b-2 border-dashed pb-2">
            ตั้งค่าการแจ้งเตือน
        </h2>
        <div>
            <template v-if="!configs.line_verified">
                <p class="mt-2 italic p-2">
                    ๏ ทำการขอรหัสยืนยัน จากนั้นสแกน QR Code หรือคลิก <span class="underline">LINE Add Friend</span> เพื่อเพิ่มเพื่อนแล้วส่งรหัสยืนยันมาในแชทเพื่อระบุตัวตน
                </p>
                <p class="mt-2 italic p-2">
                    ๏ โดยการขอรหัสยืนยันถือว่า <span class="underline">ท่านยินยอมให้ระบบจัดเก็บข้อมูล LINE โปรไฟล์ของท่าน</span> ได้แก่ LINE ID สถานะและรูปโปรไฟล์
                </p>
                <SpinnerButton
                    :spin="busy"
                    class="btn-dark w-full mt-4"
                    @click="requestVerificationCode"
                    v-if="!code"
                >
                    ขอรหัสยืนยัน
                </SpinnerButton>
                <p
                    v-else
                    class="p-2 font-semibold text-center text-white bg-thick-theme-light mt-4"
                >
                    {{ code }}
                </p>
            </template>
            <div class="mt-12 p-8 border-2 border-thick-theme-light rounded">
                <img
                    class="w-40 md:w-60 h-40 md:h-60 mx-auto"
                    :src="configs.line_bot_qrcode"
                    alt="🍊"
                >
                <a
                    :href="configs.line_bot_link_url"
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
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import Icon from '@/Components/Helpers/Icon';
import { ref } from '@vue/reactivity';

defineProps({
    configs: { type: Object, required: true }
});

const busy = ref(false);
const code = ref(null);

const requestVerificationCode = () => {
    busy.value = true;
    window.axios
        .post(window.route('request-verification-code'), {issue: 'line-verification'})
        .then(response => {
            if (!response.data.code) {
                // error
                return;
            }
            code.value = response.data.code;
        })
        .catch(error => {
            console.log(error);
        })
        .finally(() => {
            busy.value = false;
        });
};

</script>