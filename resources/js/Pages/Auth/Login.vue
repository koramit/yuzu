<template>
    <Head>
        <title>Yuzu: ลงชื่อเข้าใช้งาน</title>
    </Head>
    <div
        class="flex flex-col justify-center items-center w-full min-h-screen"
        v-if="!busy"
    >
        <div class="w-40 h-40 z-10 rounded-full transform translate-y-12 -translate-x-3">
            <img
                :src="route('home') + '/image/logo.png'"
                alt="🍊"
            >
        </div>
        <div class="mt-4 px-4 py-8 w-80 bg-white rounded shadow transform -translate-y-12">
            <!-- ⚗️😌 🔥😭-->
            <span class="block text-xl text-bitter-theme-light mt-12 text-center">⚗️ ARI Clinic 😌</span>
            <FormInput
                class="mt-8"
                label="ชื่อบัญชี"
                name="login"
                v-model="form.login"
                :error="form.errors.login"
                ref="login_input"
            />
            <FormInput
                class="mt-2"
                type="password"
                label="รหัสผ่าน"
                name="password"
                v-model="form.password"
                :error="form.errors.password"
                @keydown.enter="login"
            />
            <SpinnerButton
                :spin="form.processing"
                class="btn-bitter w-full mt-8"
                @click="login"
            >
                เข้าใช้งาน
            </SpinnerButton>

            <!-- annoucement -->
            <!-- <div class="mt-4 rounded-lg shadow p-4 bg-thick-theme-light text-white font-semibold">
                <p>ประกาศ</p>
                <div class="mt-4">
                    <p class="mt-2 italic text-xs">
                        วันที่ 20 พ.ย. 2564 เวลา 15:30 - 16:30 น. โดยประมาณ หน่วยเวชสารสนเทศ ภาควิชาอายุรศาสตร์ จะทำการย้ายเครื่องแม่ข่ายอันเนื่องมาจากเหตุเพลิงไหม้เมื่อวันที่ 18 ต.ค. 2564
                    </p>
                    <p class="mt-2 text-xs">
                        ซึ่งจะทำให้ไม่สามารถใช้งานระบบได้ หากมีผู้ป่วยยังไม่ได้จำหน่ายค้างในระบบ สามารถดำเนินการต่อได้หลังช่วงเวลาดังกล่าว ขออภัยในความไม่สะดวก 🙏🙏🙏
                    </p>
                </div>
                <div class="mt-4">
                    <p class="mt-2 italic text-xs">
                        วันที่ 20 พ.ย. 2564 เวลา 22:00 - 23:00 น. ฝ่ายสารสนเทศ คณะฯ จะทำการบำรุงรักษาเครื่องแม่ข่าย ทำให้การใช้งานดังต่อไปนี้ไม่สามารถทำได้ในช่วงเวลาดังกล่าว
                    </p>
                    <p class="mt-2 text-xs">
                        ๏ Login เข้าใช้งาน
                    </p>
                    <p class="mt-2 text-xs">
                        ๏ สร้างเคสใหม่และสร้างเคสล่วงหน้า
                    </p>
                    <p class="mt-2 text-xs">
                        หาก Login ไว้แล้ว สามารถอ่านข้อมูลและ Export ข้อมูลได้ตามปรกติ ขออภัยในความไม่สะดวก 🙏
                    </p>
                </div>
            </div> -->
            <a
                v-if="form.errors.login"
                class="mt-4 block text-xs text-red-400"
                href="https://si-eservice.mahidol.ac.th/myaccount/"
                target="_blank"
            >เปลี่ยนรหัสผ่านได้ที่นี่</a>
        </div>
    </div>
</template>

<script>
import { useForm } from '@inertiajs/inertia-vue3';
import FormInput from '@/Components/Controls/FormInput';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import { useCheckSessionTimeout } from '@/Functions/useCheckSessionTimeout';
import { useRemoveLoader } from '@/Functions/useRemoveLoader';
import { nextTick, onMounted, ref } from '@vue/runtime-core';
import { Head } from '@inertiajs/inertia-vue3';
export default {
    components: { FormInput, SpinnerButton, Head },
    setup () {
        useCheckSessionTimeout();
        useRemoveLoader();

        const busy = ref(true);
        const login_input = ref(null);

        onMounted(() => {
            busy.value = false;
            nextTick(() => login_input.value.focus());
        });

        const form = useForm({
            login: null,
            password: null,
            remember: true
        });

        function login() {
            form.transform(data => ({
                login: data.login.toLowerCase(),
                password: data.password,
                remember: data.remember ? 'on' : '',
            })).post(window.route('login.store'), {
                replace: true,
                onFinish: () => form.processing = false,
            });
        }

        return {
            login_input,
            form,
            login,
            busy,
        };
    }
};
</script>

<style scoped>
    .bg-line {
        background-color: #00b900;
    }
    .bg-telegram {
        background-color: #54a9eb;
    }
</style>