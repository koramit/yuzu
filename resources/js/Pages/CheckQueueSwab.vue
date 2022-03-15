<template>
    <Head>
        <title>ตรวจสอบคิวสวอบ</title>
    </Head>
    <div
        class="flex flex-col justify-center items-center w-full min-h-screen"
        v-if="!busy"
    >
        <div class="mt-4 px-4 py-8 w-80 bg-white rounded shadow">
            <h2 class="font-semibold text-thick-theme-light text-center">
                ตรวจสอบคิวสวอบ วันที่ {{ date }}
            </h2>
            <FormInput
                class="mt-4"
                label="hn"
                name="hn"
                type="tel"
                v-model="form.hn"
                :error="form.errors.hn"
                ref="hn_input"
            />
            <SpinnerButton
                :spin="form.processing"
                class="btn-bitter w-full mt-8"
                @click="form.post(route('check-queue-swab.show'))"
            >
                ตรวจสอบ
            </SpinnerButton>
            <div
                class="mt-8 p-4 bg-dark-theme-light shadow text-center text-2xl text-white font-semibold"
                v-if="result.queue"
            >
                {{ result.queue }}
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
import { ref } from '@vue/reactivity';
import { nextTick, onMounted } from '@vue/runtime-core';

const props = defineProps({
    date: { type: String, required: true },
    result: { type: Object, required: true },
});

useCheckSessionTimeout();
useRemoveLoader();

const form = useForm({hn: props.result.hn});
const hn_input = ref(null);
const busy = ref(true);

onMounted(() => {
    busy.value = false;
    nextTick(() => hn_input.value.focus());
});

const getQueue = () => {

};

</script>