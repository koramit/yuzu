<template>
    <div class="bg-white rounded shadow-sm p-4 mb-4 sm:mb-6 md:mb-12 mx-auto">
        <h2 class="font-semibold text-thick-theme-light border-b-2 border-dashed pb-2">
            รหัสปฏิบัติงาน
        </h2>
        <p class="px-4 py-2 bg-gray-100 text-center mt-4 text-bitter-theme-light font-semibold">
            {{ activeDutyToken }}
        </p>
        <SpinnerButton
            :spin="form.processing"
            class="btn-dark w-full mt-4"
            @click="form.post(route('duty-token.store'))"
        >
            สร้างรหัสใหม่
        </SpinnerButton>
    </div>
</template>

<script setup>
import SpinnerButton from '@/Components/Controls/SpinnerButton';
defineProps({
    activeDutyToken: { type: String, required: true}
});
const form = useForm({});
</script>

<script>
import Layout from '@/Components/Layouts/Layout';
import { useForm } from '@inertiajs/inertia-vue3';
export default { layout: Layout };
</script>