<template>
    <div class="my-2 md:my-4">
        <label
            for=""
            class="form-label text-base"
        >เรียกคิวเป็นชุด หากคิวไหนเรียกไม่ได้ระบบจะข้ามให้</label>
        <div class="grid grid-cols-3 gap-2 items-center">
            <FormInput
                v-model="minNo"
                type="telno"
                name="minNo"
                placeholder="ตั้งแต่คิวที่"
            />
            <FormInput
                v-model="maxNo"
                type="telno"
                name="maxNo"
                placeholder="ถึงคิวที่"
            />
            <SpinnerButton
                :spin="busy"
                class="btn-bitter"
                @click="notifySwabQueueLot"
                :disabled="!notifyLot.length"
            >
                เรียกคิว {{ notifyLot.length ? `(${notifyLot.length})`:'' }}
            </SpinnerButton>
        </div>
    </div>
    <div
        class="my-2 md-my4"
        v-if="notifyLotErrors.length"
    >
        <ul class="list-disc">
            <li
                v-for="notification in notifyLotErrors"
                :key="notification.id"
                class="text-red-400"
            >
                {{ notification.label }} แจ้งเตือนไม่สำเร็จ กรุณาลองใหม่
            </li>
        </ul>
    </div>
    <div
        class="rounded bg-white shadow-sm my-1 p-1 flex"
        v-for="(visit, key) in [...visits].sort((a, b) => a.specimen_no > b.specimen_no )"
        :key="key"
    >
        <!-- left detail -->
        <div class="w-3/4">
            <div class="flex items-center">
                <p class="p-1 pb-0 text-thick-theme-light">
                    {{ visit.patient_type }}
                    <span class="mr-1 underline">{{ visit.swab_at }}</span>
                </p>
                <Icon
                    class="ml-2 mr-1 w-4 h-4 text-bitter-theme-light"
                    name="vial"
                />
                <p class="mr-1 text-xl font-semibold text-thick-theme-light">
                    # {{ visit.specimen_no }}
                </p>
            </div>
            <div class="flex items-baseline">
                <p class="p-1 text-lg pt-0">
                    HN {{ visit.hn }} <br class="md:hidden"> {{ visit.patient_name }}
                </p>
            </div>
            <p class="px-1 text-xs text-dark-theme-light italic">
                ส่ง swab เมื่อ {{ visit.enlisted_swab_at_for_humans }}
            </p>
        </div>
        <!-- right menu -->
        <div class="w-1/4 text-sm p-1 grid justify-items-start">
            <div v-if="true">
                <button
                    class="inline-flex justify-start"
                    :class="{
                        'text-bitter-theme-light' : visit.notification_active && !visit.notification_count && !visit.calling,
                        'text-alt-theme-light': visit.notification_active && visit.notification_count && !visit.calling,
                        'text-gray-400 cursor-not-allowed': !visit.notification_active || visit.calling
                    }"
                    :disabled="!visit.notification_active || visit.calling"
                    @click="notifySwabQueue(visit)"
                >
                    <Icon
                        class="w-4 h-4 mr-1"
                        :class="{
                            'animate-spin': visit.calling,
                        }"
                        :name="visit.calling ? 'sync-alt' :'bullhorn'"
                    />
                    <span class="block font-normal text-thick-theme-light">
                        <template v-if="!visit.notification_active">
                            เรียกคิวไม่ได้
                        </template>
                        <template v-else-if="visit.notification_count">
                            เรียกซ้ำ (#{{ visit.notification_count+1 }})
                        </template>
                        <template v-else>
                            เรียกคิว
                        </template>
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import Icon from '@/Components/Helpers/Icon';
import FormInput from '@/Components/Controls/FormInput';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import { ref } from '@vue/reactivity';
import { computed } from '@vue/runtime-core';
const props = defineProps({
    visits: { type: Array, required: true }
});

const notifySwabQueue = (visit) => {
    visit.calling = true;
    window.axios
        .post(window.route('visits.swab-notification-list.store'), { userIds: [visit.notification_user_id] })
        .then((response) => {
            if (response.data.errors.length) {
                return;
            }
            visit.notification_count = visit.notification_count + 1;
            if (!notifyLotErrors.value.length) {
                return;
            }
            let index = notifyLotErrors.value.findIndex(n => n.id === visit.notification_user_id);
            if (index !== -1) {
                notifyLotErrors.value.splice(index, 1);
            }
        })
        .catch(error => {
            console.log(error);
        })
        .finally(() => visit.calling = false);
};

const minNo = ref(null);
const maxNo = ref(null);
const busy = ref(false);
const notifyLotErrors = ref([]);

const notifyLot = computed(() => {
    if (!minNo.value || !maxNo.value || (minNo.value >= maxNo.value)) {
        return [];
    }

    return props.visits.filter(v => (v.specimen_no >= minNo.value)
        && (v.specimen_no <= maxNo.value)
        && (v.notification_active)
    );
});

const notifySwabQueueLot = () => {
    busy.value = true;
    notifyLotErrors.value = [];
    window.axios
        .post(window.route('visits.swab-notification-list.store'), { userIds: notifyLot.value.map(n => n.notification_user_id) })
        .then((response) => {

            if (response.data.errors.length) {
                notifyLotErrors.value = props.visits
                    .filter(v => response.data.errors.includes(v.notification_user_id))
                    .map(v => {
                        return { id: v.notification_user_id, label: (v.hn + ' ' + v.patient_name) };
                    });
            }

            notifyLot.value.forEach(n => {
                if (!response.data.errors.includes(n.notification_user_id)) {
                    n.notification_count = n.notification_count + 1;
                }
            });

            minNo.value = null;
            maxNo.value = null;
        })
        .catch(error => {
            console.log(error);
        })
        .finally(() => busy.value = false);
};
</script>