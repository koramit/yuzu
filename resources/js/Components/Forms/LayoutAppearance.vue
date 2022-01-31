<template>
    <div
        class="bg-white rounded shadow-sm p-4 mb-4 sm:mb-6 md:mb-12"
        v-if="$page.props.user.roles.length"
    >
        <h2 class="font-semibold text-thick-theme-light border-b-2 border-dashed pb-2">
            ตั้งค่าการแสดงผล (มีผลเมื่อใช้งานบน tablet/desktop เท่านั้น)
        </h2>
        <FormCheckbox
            class="mt-4"
            label="ตั้ง mode ส้มเป็นค่าเริ่มต้น"
            name="zenMode"
            v-model="zenMode"
            :toggler="true"
            @autosave="autosave"
        />
        <FormSelect
            class="mt-2"
            label="ขนาดตัวอักษร"
            name="homepage"
            v-model="fontScaleIndex"
            :options="fontScales"
            @autosave="autosave"
        />
    </div>
</template>

<script  setup>
import FormSelect from '@/Components/Controls/FormSelect';
import FormCheckbox from '@/Components/Controls/FormCheckbox';
import { Inertia } from '@inertiajs/inertia';
import { ref } from '@vue/reactivity';
import debounce from 'lodash/debounce';
import { watch } from '@vue/runtime-core';

const props = defineProps({
    configs: { type: Object, required: true }
});
const zenMode = ref(props.configs.zenMode);
const fontScales = ref([
    { value: 0, label: '67%' },
    { value: 1, label: '80%' },
    { value: 2, label: '90%' },
    { value: 3, label: '100%' },
]);
const fontScaleIndex = ref(props.configs.fontScaleIndex);
watch(
    () => fontScaleIndex.value,
    (val) => {
        let option = fontScales.value.find(i => i.value == val);
        let vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
        if (option !== undefined && vw >= 768) { // md breakpoint
            document.querySelector('html').style.fontSize = option.label;
        }
    }
);

const autosave = debounce(() => {
    window.axios
        .patch(window.route('preferences.update'), { appearance: { zenMode: zenMode.value, fontScaleIndex: fontScaleIndex.value ?? props.configs.fontScaleIndex } })
        .catch(error => {
            if (error.response.status == 401) {
                Inertia.reload();
            }
        });
}, 2000);
</script>