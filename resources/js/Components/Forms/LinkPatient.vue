<template>
    <div class="bg-white rounded shadow-sm p-4 mb-4 sm:mb-6 md:mb-12">
        <h2 class="font-semibold text-thick-theme-light border-b-2 border-dashed pb-2">
            {{ configs.patient_id ? `HN ${configs.hn} ยืนยันแล้ว` : 'ยืนยัน HN' }}
        </h2>
        <template v-if="!configs.patient_id">
            <FormInput
                class="mt-8"
                label="hn"
                name="hn"
                v-model="form.hn"
                :error="form.errors.hn"
            />
            <SpinnerButton
                :spin="form.processing"
                class="btn-dark w-full mt-4"
                @click="linkPatient"
                :disabled="!form.hn"
            >
                ตรวจสอบ
            </SpinnerButton>
        </template>
    </div>
</template>

<script setup>
import FormInput from '@/Components/Controls/FormInput';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import { Inertia } from '@inertiajs/inertia';
import { reactive } from '@vue/reactivity';

const props = defineProps({
    configs: { type: Object, required: true }
});

const linkPatient = () => {
    form.processing = true;
    window.axios
        .post(window.route('link-patient'), form)
        .then(response => {
            if (response.data.errors !== undefined) {
                form.errors = response.data.errors;
                return;
            }
            form.errors = {};
            Inertia.reload();
        })
        .catch(error => console.log(error))
        .finally(() => {
            form.processing = false;
        });
};

const form = reactive({
    hn: null,
    sap_mode: props.configs.sap_mode,
    processing: false,
    errors: {}
});
</script>
