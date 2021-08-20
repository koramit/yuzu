<template>
    <div>
        <div class="grid grid-cols-3 gap-x-2 mb-4 text-center">
            <a
                :href="route('export.opd_cards')"
                class="btn btn-bitter"
                v-if="can.export_opd_cards"
            >
                Export OPD cards
            </a>
            <!-- <SpinnerButton
                @click="importColab.click()"
                class="btn btn-dark"
                :spin="colabUploader.processing"
            >
                Import Colab
            </SpinnerButton>
            <button
                class="btn btn-bitter"
                disabled
            >
                Import Visits
            </button> -->
        </div>
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
            v-for="(visit, key) in visits.data"
            :key="key"
        >
            <!-- left detail -->
            <div class="w-3/4">
                <p class="p-1 pb-0 text-thick-theme-light">
                    {{ visit.patient_type }}
                </p>
                <div class="flex items-baseline">
                    <p class="p-1 text-lg pt-0">
                        {{ visit.date_visit }} <br class="md:hidden"> {{ visit.patient_name }}
                    </p>
                </div>
                <p class="px-1 text-xs text-dark-theme-light italic">
                    ปรับปรุงล่าสุด {{ visit.updated_at_for_humans }}
                </p>
            </div>
            <!-- right menu -->
            <div class="w-1/4 text-sm p-1 grid justify-items-start">
                <!-- read -->
                <div>
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
                </div>
            </div>
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

        <input
            class="hidden"
            type="file"
            ref="importColab"
            @input="colabSelected"
            accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
        >

        <Visit ref="createVisitForm" />
        <Appointment ref="appointmentForm" />
    </div>
</template>

<script>
import Layout from '@/Components/Layouts/Layout';
import Icon from '@/Components/Helpers/Icon';
import Visit from '@/Components/Forms/Visit';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import { inject, nextTick, reactive, ref, watch } from '@vue/runtime-core';
import { Link, useForm } from '@inertiajs/inertia-vue3';
import Appointment from '@/Components/Forms/Appointment';
import throttle from 'lodash/throttle';
import { Inertia } from '@inertiajs/inertia';

export default {
    layout: Layout,
    components: { Visit, Icon, Link, SpinnerButton, Appointment },
    props: {
        visits: { type: Object, required: true },
        filters: { type: Object, required: true },
        can: { type: Object, required: true },
    },
    setup (props) {
        const createVisitForm = ref(null);
        const appointmentForm = ref(null);
        const emitter = inject('emitter');

        const currentConfirm = reactive({
            action: null,
            resource_id: null,
        });
        const cancel = (visit) => {
            currentConfirm.action = 'cancel',
            currentConfirm.resource_id = visit.slug,
            emitter.emit('need-confirm', {
                cofirmText: 'ยกเลิกการตรวจ ' + visit.title,
                needReason: true,
            });
        };

        emitter.on('action-clicked', (action) => {
            // please expect console log error in case of revisit this page
            // maybe new vue fragment lazy loading template so it not
            // ready to use and need some kind of "activate"
            if (action === 'create-visit') {
                nextTick(() => createVisitForm.value.open());
            } else if (action === 'create-appointment') {
                nextTick(() => appointmentForm.value.open());
            }
        });

        const importColab = ref(null);
        const colabUploader = useForm({file: null});
        const colabSelected = (event) => {
            colabUploader.file = event.target.files[0];
            colabUploader.post(window.route('import.colab'));
        };

        const search = ref(props.filters.search);
        watch (
            () => search.value,
            throttle(function(val) {
                Inertia.visit(window.route('visits') + '?search=' + val, { preserveState: true });
            }, 450),
        );

        return {
            createVisitForm,
            appointmentForm,
            cancel,
            importColab,
            colabSelected,
            colabUploader,
            search,
        };
    },
};
</script>