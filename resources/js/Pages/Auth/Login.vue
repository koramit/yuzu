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
            <span class="block text-xl text-bitter-theme-light mt-12 text-center">⚗️ ARI Clinic Inhaler 😌</span>
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

        const pageLoadingIndicator = document.getElementById('page-loading-indicator');
        if (pageLoadingIndicator) {
            useRemoveLoader();
        }

        const busy = ref(true);
        const login_input = ref(null);

        onMounted(() => {
            setTimeout(() => {
                busy.value = false;
                nextTick(() => login_input.value.focus());
            }, pageLoadingIndicator ? 1600:0);
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
            })).post(route('login.store'), {
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