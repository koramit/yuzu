<template>
    <Head>
        <title>วันนี้เป็นอย่างไรบ้าง ?</title>
    </Head>
    <div class="flex flex-col justify-center items-center w-full min-h-screen">
        <div class="mt-4 px-4 py-8 w-80 bg-white rounded shadow">
            <Link
                class="btn btn-bitter block text-center"
                :href="configs.on_duty_link"
                v-text="'ปฏิบัติงานใน ARI ประจำวันนี้'"
            />
            <Link
                class="mt-8 btn btn-bitter block text-center"
                :href="configs.on_exam_link"
                v-text="'รับการตรวจ'"
            />
        </div>
    </div>
</template>

<script setup>
import { useCheckSessionTimeout } from '@/Functions/useCheckSessionTimeout';
import { useRemoveLoader } from '@/Functions/useRemoveLoader';
import { Head, Link } from '@inertiajs/inertia-vue3';

defineProps({
    configs: { type: Object, required: true }
});

useCheckSessionTimeout();
useRemoveLoader();
</script>