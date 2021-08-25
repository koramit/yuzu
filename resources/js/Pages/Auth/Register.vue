<template>
    <Head>
        <title>Yuzu: ลงทะเบียน</title>
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
            <span class="block font-semibold text-xl text-bitter-theme-light mt-12 text-center">Register</span>
            <div
                class="mt-4"
            />
            <FormInput
                class="mt-2"
                name="name"
                label="นามแสดง"
                v-model="form.name"
                :error="form.errors.name"
                placeholder="ชื่อที่ใช้แสดงในระบบ (ชื่อเล่น นามแฝง)"
                ref="name_input"
            />
            <FormInput
                class="mt-2"
                name="full_name"
                label="ชื่อเต็ม"
                placeholder="title first name last name"
                v-model="form.full_name"
                :error="form.errors.full_name"
                :readonly="profile.org_id !== undefined"
            />
            <FormInput
                class="mt-2"
                type="tel"
                name="tel_no"
                label="หมายเลขโทรศัพท์ที่สามารถติดต่อได้"
                v-model="form.tel_no"
                :error="form.errors.tel_no"
                placeholder="ใช้ติดต่อกรณีจำเป็นเท่านั้น"
            />
            <FormInput
                v-if="profile.is_md"
                class="mt-2"
                type="tel"
                name="pln"
                label="เลข ว."
                v-model="form.pln"
                :error="form.errors.pln"
                placeholder="ใช้พิมพ์เอกสารเวชระเบียน"
            />
            <FormCheckbox
                class="mt-2"
                v-model="form.agreement_accepted"
                label="ยอมรับนโยบายความเป็นส่วนตัวและข้อตกลงการใช้งาน"
                :toggler="true"
            />
            <a
                :href="route('terms')"
                class="mt-2 block text-bitter-theme-light underline"
                target="_blank"
            >อ่านนโยบายและข้อตกลง</a>
            <SpinnerButton
                :spin="form.processing"
                class="btn-bitter w-full mt-4"
                @click="register"
                :disabled="!formComplete"
            >
                ลงทะเบียน
            </SpinnerButton>
        </div>
    </div>
</template>

<script>
import { useForm } from '@inertiajs/inertia-vue3';
import FormCheckbox from '@/Components/Controls/FormCheckbox.vue';
import FormInput from '@/Components/Controls/FormInput';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import { useCheckSessionTimeout } from '@/Functions/useCheckSessionTimeout';
import { useRemoveLoader } from '@/Functions/useRemoveLoader';
import { computed, nextTick, onMounted, ref } from '@vue/runtime-core';
import { Head } from '@inertiajs/inertia-vue3';
export default {
    components: { FormCheckbox, FormInput, SpinnerButton, Head },
    props: {
        profile: { type: Object, required: true }
    },
    setup (props) {
        useCheckSessionTimeout();
        useRemoveLoader();

        const busy = ref(true);
        const name_input = ref();
        onMounted(() => {
            busy.value = false;
            nextTick(() => name_input.value.focus());
        });

        const form = useForm({
            login: props.profile.username,
            full_name: props.profile.name,
            org_id: props.profile.org_id,
            division: props.profile.org_division_name,
            position: props.profile.org_position_title,
            password_expires_in_days: props.profile.password_expires_in_days,
            remark: props.profile.remark,
            name: null,
            tel_no: null,
            pln: null,
            is_md: props.profile.is_md,
            agreement_accepted: false,
            remember: true
        });

        const formComplete = computed(() =>
            (
                form.agreement_accepted &&
                form.name &&
                form.full_name &&
                form.tel_no &&
                (form.pln || !form.is_md)
            ) ? true : false
        );

        function register () {
            form.transform(data => ({
                ...data,
                remember: data.remember ? 'on' : '',
            })).post(window.route('register.store'), {
                onFinish: () => form.processing = false,
            });
        }

        return {
            name_input,
            form,
            formComplete,
            register,
            busy
        };
    },
};
</script>