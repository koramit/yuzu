<template>
    <div
        class="bg-white rounded shadow-sm p-4 mb-4 sm:mb-6 md:mb-12"
        v-if="form.length"
    >
        <h2 class="font-semibold text-thick-theme-light border-b-2 border-dashed pb-2">
            จัดการการแจ้งเตือน
        </h2>
        <FormCheckbox
            class="my-4"
            v-for="notify in form"
            :key="notify.value"
            :name="notify.label"
            :label="notify.label"
            v-model="notify.set"
            :toggler="true"
            @autosave="autosave(notify)"
        />
    </div>
</template>

<script setup>
import FormCheckbox from '@/Components/Controls/FormCheckbox';
import { ref } from '@vue/reactivity';

const props = defineProps({
    configs: { type: Object, required: true }
});

const form = ref(props.configs.notifications.filter(n => n.can).map(n => {
    if (props.configs.subscriptions.includes(n.value)) {
        n.set = true;
    }
    return n;
}));

const autosave = (notify) => {
    window.axios
        .patch(window.route('preferences.update'), { notification_event_id: notify.value, subscribe: notify.set })
        .catch(error => console.log(error));
};
</script>