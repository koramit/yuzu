<template>
    <div
        class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12"
        v-if="availablePages.length"
    >
        <h2 class="font-semibold text-thick-theme-light border-b-2 border-dashed pb-2">
            ตั้งค่าหน้าแรก
        </h2>
        <FormSelect
            class="mt-2"
            label="หน้าที่ต้องการไปหลัง login สำเร็จ"
            name="homepage"
            v-model="homepage"
            :options="availablePages"
            @autosave="autosave"
        />
    </div>
</template>

<script  setup>
import FormSelect from '@/Components/Controls/FormSelect';
import { Inertia } from '@inertiajs/inertia';
import { computed, ref } from '@vue/reactivity';

const props = defineProps({
    configs: { type: Object, required: true }
});
const availablePages = computed(() => props.configs.pages.filter(p => p.can).map(p => p.label));
const homepage = ref(props.configs.currentHomePage);

const autosave = () => {
    if (availablePages.value.indexOf(homepage.value) === -1) {
        return;
    }
    window.axios
        .patch(window.route('preferences.update'), { home_page: homepage.value })
        .catch(error => {
            if (error.response.status == 401) {
                Inertia.reload();
            }
        });
};
</script>