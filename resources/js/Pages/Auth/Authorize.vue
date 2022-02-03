<template>
    <Head>
        <title>ใส่รหัสเพื่อปฏิบัติงาน</title>
    </Head>
    <div class="flex flex-col justify-center items-center w-full min-h-screen">
        <div class="bg-white rounded shadow-sm p-4 mb-4 sm:mb-6 md:mb-12">
            <h2 class="font-semibold text-thick-theme-light border-b-2 border-dashed pb-2">
                กรุณาใส่รหัสปฏิบัติงาน
            </h2>
            <FormInput
                class="mt-8"
                type="password"
                name="token"
                v-model="form.token"
                :error="form.errors.token"
            />
            <SpinnerButton
                :spin="form.processing"
                class="btn-dark w-full mt-4"
                @click="form.post(route('duty-token-user-authorization.store'))"
                :disabled="!form.token"
            >
                ตรวจสอบ
            </SpinnerButton>

            <div class="flex justify-between mt-12">
                <Link
                    :href="route('preferences')"
                    class="text-blue-400 underline hover:text-blue-600"
                >
                    ตั่งค่า
                </Link>
                <Link
                    :href="route('logout')"
                    method="delete"
                    as="button"
                    type="button"
                    class="text-blue-400 underline hover:text-blue-600"
                >
                    ออกจากระบบ
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import FormInput from '@/Components/Controls/FormInput';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import { useCheckSessionTimeout } from '@/Functions/useCheckSessionTimeout';
import { useRemoveLoader } from '@/Functions/useRemoveLoader';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
// import { reactive } from '@vue/reactivity';

useCheckSessionTimeout();
useRemoveLoader();

const form = useForm({
    token: null
});
</script>