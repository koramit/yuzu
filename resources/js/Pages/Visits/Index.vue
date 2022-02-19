<template>
    <div>
        <!-- export opd cards -->
        <ExportOPDCards v-if="can.export_opd_cards" />

        <!-- search -->
        <input
            autocomplete="off"
            type="text"
            name="search"
            placeholder="🔍 ด้วย HN หรือ ชื่อ"
            v-model="search"
            class="form-input w-full mb-4"
        >
        <!-- card -->
        <div
            class="rounded bg-white shadow-sm my-1 p-1 flex"
            :class="{'bg-gray-100': visit.status === 'canceled'}"
            v-for="(visit, key) in visits.data"
            :key="key"
        >
            <template v-if="visit.status === 'canceled'">
                <!-- left detail -->
                <div class="w-3/4">
                    <VisitActions
                        v-if="visit.can.view_visit_actions"
                        :slug="visit.slug"
                    />
                    <p class="p-1 text-lg pt-0 text-gray-400">
                        HN {{ visit.hn }} <br class="md:hidden"> {{ visit.patient_name }}
                    </p>
                </div>
                <!-- right menu -->
                <div class="w-1/4 text-sm p-1">
                    <div
                        class="flex text-gray-400 justify-start cursor-not-allowed"
                    >
                        <Icon
                            class="w-4 h-4 mr-1"
                            name="trash-alt"
                        />
                        <span class="block font-normal">ยกเลิกแล้ว</span>
                    </div>
                </div>
            </template>
            <template v-else>
                <!-- left detail -->
                <div class="w-3/4">
                    <VisitActions
                        v-if="visit.can.view_visit_actions"
                        :slug="visit.slug"
                    />
                    <p class="p-1 pb-0 text-thick-theme-light">
                        {{ visit.date_visit }}
                        {{ visit.patient_type }}
                        <span
                            class="text-sm italic px-2 text-bitter-theme-light"
                            v-if="visit.swab"
                        >
                            <Icon
                                class="inline w-4 h-4"
                                name="virus"
                            />
                            Swab
                        </span>
                        <span
                            class="text-sm italic px-2 mr-1 text-alt-theme-light"
                            v-else
                        >
                            <Icon
                                class="inline w-4 h-4"
                                name="stethoscope"
                            />
                            ตรวจ
                        </span>
                    </p>
                    <p
                        class="p-1 pb-0"
                        :class="{
                            'text-red-400': visit.result.toLowerCase() === 'detected',
                            'text-yellow-400': visit.result.toLowerCase() === 'inconclusive',
                        }"
                        v-if="visit.result"
                    >
                        <span
                            v-if="visit.swab"
                            class="mr-1"
                        >{{ visit.swabbed ? visit.result : 'ไม่ได้ sawb' }}</span>
                        <span
                            v-if="visit.note"
                            class="mr-1 italic"
                        >{{ visit.note }}</span>
                    </p>
                    <div class="flex items-baseline">
                        <p class="p-1 text-lg pt-0">
                            HN {{ visit.hn }} <br class="md:hidden"> {{ visit.patient_name }}
                        </p>
                    </div>
                    <p class="px-1 text-xs text-dark-theme-light italic">
                        ปรับปรุงล่าสุด {{ visit.updated_at_for_humans }}
                    </p>
                </div>
                <!-- right menu -->
                <div class="w-1/4 text-sm p-1 grid justify-items-start">
                    <!-- read -->
                    <Link
                        class="inline-flex text-alt-theme-light justify-start"
                        :href="route('visits.show', visit)"
                        v-if="visit.can.view"
                    >
                        <Icon
                            class="w-4 h-4 mr-1"
                            name="readme"
                        />
                        <span class="block font-normal text-thick-theme-light">อ่าน</span>
                    </Link>
                    <button
                        class="inline-flex text-alt-theme-light justify-start"
                        :href="route('visits.show', visit)"
                        v-if="canSwap(visit)"
                        @click="swapSwab(visit)"
                    >
                        <Icon
                            class="w-4 h-4 mr-1"
                            name="eraser"
                        />
                        <span class="block font-normal text-thick-theme-light">{{ visit.swabbed ? 'ไม่ swab' : 'ได้ swab' }}</span>
                    </button>
                </div>
            </template>
        </div>

        <!-- pagination -->
        <div v-if="visits.links.length > 3">
            <div class="flex flex-wrap -mb-1 mt-4">
                <template v-for="(link, key) in visits.links">
                    <div
                        v-if="link.url === null"
                        :key="key"
                        class="mr-1 mb-1 px-4 py-3 text-sm leading-4 bg-gray-200 text-gray-400 border rounded cursor-not-allowed"
                        v-html="link.label"
                    />
                    <Link
                        v-else
                        :key="key+'theLink'"
                        class="mr-1 mb-1 px-4 py-3 text-sm text-dark-theme-light leading-4 border border-alt-theme-light rounded hover:bg-white focus:border-dark-theme-light focus:text-dark-theme-light transition-colors"
                        :class="{ 'font-semibold bg-alt-theme-light cursor-not-allowed hover:bg-alt-theme-light': link.active }"
                        :href="link.url"
                        as="button"
                        :disabled="link.active"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>

        <Visit ref="createVisitForm" />
        <Appointment ref="appointmentForm" />
    </div>
</template>

<script setup>

import Visit from '@/Components/Forms/Visit';
import Appointment from '@/Components/Forms/Appointment';
import ExportOPDCards from '@/Components/Forms/ExportOPDCards';
import VisitActions from '@/Components/Helpers/VisitActions';
import Icon from '@/Components/Helpers/Icon';
import { nextTick, ref, watch } from '@vue/runtime-core';
import { Link, usePage } from '@inertiajs/inertia-vue3';
import throttle from 'lodash/throttle';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    visits: { type: Object, required: true },
    filters: { type: Object, required: true },
    can: { type: Object, required: true },
});

const createVisitForm = ref(null);
const appointmentForm = ref(null);
watch (
    () => usePage().props.value.event.fire,
    (event) => {
        if (! event) {
            return;
        }

        if (usePage().props.value.event.name === 'action-clicked') {
            if (usePage().props.value.event.payload === 'create-visit') {
                nextTick(() => createVisitForm.value.open());
            } else if (usePage().props.value.event.payload === 'create-appointment') {
                nextTick(() => appointmentForm.value.open());
            }
        }
    }
);

const search = ref(props.filters.search);
watch (
    () => search.value,
    throttle(function(val) {
        Inertia.visit(window.route('visits') + '?search=' + val, { preserveState: true });
    }, 450),
);

const canSwap = (visit) => {
    return visit.swab
        && visit.result === 'Pending'
        && visit.can.view_visit_actions;
};

const swapSwab = (visit) => {
    visit.busy = true;
    Inertia.patch(window.route('visits.swap-swab', visit), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<script>
import Layout from '@/Components/Layouts/Layout';
export default { layout: Layout };
</script>