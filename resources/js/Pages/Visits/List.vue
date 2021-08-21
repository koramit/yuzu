<template>
    <div>
        <!-- export -->
        <a
            class="flex items-center text-green-600"
            :href="route('export.visits')"
            v-if="card === 'visit' && ($page.props.user.roles.includes('nurse') || $page.props.user.roles.includes('admin') || $page.props.user.roles.includes('root'))"
        >
            <Icon
                class="w-4 h-4 mr-1"
                name="file-excel"
            />
            <span class="block font-normal text-thick-theme-light">รายงานเคสวันนี้</span>
        </a>

        <!-- search -->
        <div class="mb-2 relative">
            <input
                autocomplete="off"
                type="text"
                name="search"
                :placeholder="card === 'visit' ? '🔍 ด้วย HN หรือ ชื่อ หรือ หมายเลขหลอด' : '🔍 ด้วย HN หรือ ชื่อ'"
                v-model="search"
                class="form-input"
            >
            <button
                class="absolute inset-y-0 right-0 flex items-center px-2 text-thick-theme-light cursor-pointer"
                @click="reload"
            >
                <Icon
                    class="w-4 h-4"
                    name="sync-alt"
                />
            </button>
        </div>

        <Filters
            v-if="cardfilters.length"
            :filters="cardfilters"
            ref="filtersComponent"
            @toggle="filtered"
            @filtered="(name, value) => filters[name] = value"
        />

        <!-- card -->
        <CardScreen
            v-if="card === 'screen'"
            :visits="filteredVisits"
            @cancel="cancel"
        />
        <CardExam
            v-else-if="card === 'exam'"
            :visits="filteredVisits"
            @cancel="cancel"
        />
        <CardSwab
            v-else-if="card === 'swab'"
            :visits="filteredVisits"
        />
        <CardEnqueueSwab
            v-else-if="card === 'enqueue-swab'"
            :visits="filteredVisits"
        />
        <CardMedicalRecord
            v-else-if="card === 'mr'"
            :visits="filteredVisits"
        />
        <CardQueue
            v-else-if="card === 'queue'"
            :visits="filteredVisits"
        />
        <CardVisit
            v-else-if="card === 'visit'"
            :visits="filteredVisits"
            @edit="edit"
        />
        <CardLab
            v-else-if="card === 'lab'"
            :visits="filteredVisits"
        />

        <Visit ref="createVisitForm" />
        <Appointment ref="appointmentForm" />
    </div>
</template>

<script>
import Layout from '@/Components/Layouts/Layout';
import Icon from '@/Components/Helpers/Icon';
import CardScreen from '@/Components/Cards/Screen';
import CardLab from '@/Components/Cards/Lab';
import CardExam from '@/Components/Cards/Exam';
import CardSwab from '@/Components/Cards/Swab';
import CardVisit from '@/Components/Cards/Visit';
import CardEnqueueSwab from '@/Components/Cards/EnqueueSwab';
import CardMedicalRecord from '@/Components/Cards/MedicalRecord';
import CardQueue from '@/Components/Cards/Queue';
import Filters from '@/Components/Cards/Filters';
import Visit from '@/Components/Forms/Visit';
import Appointment from '@/Components/Forms/Appointment';
import { computed, inject, nextTick, onUnmounted, reactive, ref } from '@vue/runtime-core';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/inertia-vue3';

export default {
    layout: Layout,
    components: { Visit, Icon, CardScreen, CardExam, CardSwab, CardMedicalRecord, CardEnqueueSwab, CardQueue, CardLab, CardVisit, Filters, Appointment },
    props: {
        visits: { type: Object, required: true },
        card: { type: String, required: true },
        eventSource: { type: String, default: '' }
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
            currentConfirm.action = 'cancel';
            currentConfirm.resource_id = visit.slug;
            emitter.emit('need-confirm', {
                confirmText: 'ยกเลิกการตรวจ ' + visit.title,
                needReason: true,
            });
        };
        const edit = (visit) => {
            currentConfirm.action = 'edit';
            currentConfirm.resource_id = visit.slug;
            let confirmText = null;
            if (usePage().props.value.user.roles.includes('md')) {
                confirmText  = '<p>แก้ไข ' + visit.title + '</p>';
                confirmText += '<p class="mt-2">๏ หลังแก้ไขเสร็จ ผู้ป่วยจะเปลี่ยนสถานะเป็น swab/จำหน่าย และถูกปรับห้องตามที่ถูกแก้ไข</p>';
                confirmText += '<p class="mt-2">*** แจ้งคุณพยาบาล incharge เพื่อติดต่อเวชระเบียนพิมพ์ OPD card ใหม่แทนใบเดิมด้วย</p>';
                confirmText += '<p class="mt-2">๏ หากแก้ไม่เสร็จสามารถบันทึกไว้ก่อน โดยผู้ป่วยจะย้ายสถานะไปอยู่ห้องตรวจ</p>';
            } else { // nurse
                confirmText  = '<p class="mt-2">แก้ไข ' + visit.title + '</p>';
                confirmText += '<p class="mt-2">๏ หลังแก้ไขเสร็จ ผู้ป่วยจะเปลี่ยนสถานะเป็น ตรวจ/swab/จำหน่าย และถูกปรับห้องตามที่ถูกแก้ไข</p>';
                confirmText += '<p class="mt-2">*** แจ้งคุณพยาบาล incharge เพื่อติดต่อเวชระเบียนพิมพ์ OPD card ใหม่แทนใบเดิมด้วย</p>';
                confirmText += '<p class="mt-2">๏ หากแก้ไม่เสร็จสามารถบันทึกไว้ก่อน โดยผู้ป่วยจะย้ายสถานะไปอยู่ห้องคัดกรอง</p>';
            }
            emitter.emit('need-confirm', {
                confirmText: confirmText,
                needReason: false,
            });
        };
        emitter.on('confirmed', (text) => {
            if (currentConfirm.action === 'cancel') {
                Inertia.delete(window.route('visits.cancel', currentConfirm.resource_id), {
                    data: {reason: text},
                    preserveState: true,
                    preserveScroll: true,
                });
            } else if (currentConfirm.action === 'edit') {
                Inertia.get(window.route('visits.replace', currentConfirm.resource_id), {
                    preserveState: true,
                    preserveScroll: true,
                });
            }
        });

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

        if (props.eventSource) {
            const eventSource = new EventSource(window.route('sse') + '?channel=' + props.eventSource, { withCredentials: true });
            let updatestamp = 0;

            eventSource.onmessage = (e) => {
                let data = JSON.parse(e.data);
                if (data.updatestamp > updatestamp) {
                    if (updatestamp) {
                        Inertia.reload();
                    }
                    updatestamp = data.updatestamp;
                }
            };

            window.addEventListener('beforeunload', () => {
                eventSource.close();
            });

            onUnmounted(() => {
                eventSource.close();
            });
        }

        const search = ref('');
        const filters = reactive({
            exam: false,
            swab: false,
            public: false,
            staff: false,
            walk_in: false,
            appointment: false,
            swab_at_scg: false,
            swab_at_sky_walk: false,
        });
        const filteredVisits = computed(() => {
            if (! search.value) {
                return props.visits
                    .filter(v => filters.exam ? !v.swab : true)
                    .filter(v => filters.swab ? v.swab : true)
                    .filter(v => filters.staff ? v.patient_type === 'เจ้าหน้าที่ศิริราช' : true)
                    .filter(v => filters.public ? v.patient_type === 'บุคคลทั่วไป' : true)
                    .filter(v => filters.walk_in ? v.track === 'Walk-in' : true)
                    .filter(v => filters.appointment ? v.track === 'นัด หรือ staff' : true)
                    .filter(v => filters.swab_at_scg ? v.swab_at === 'SCG' : true)
                    .filter(v => filters.swab_at_sky_walk ? v.swab_at === 'Sky Walk' : true);
            }

            if (props.card === 'visit') {
                return props.visits.filter(v =>
                    v.hn.indexOf(search.value) !== -1
                    || v.patient_name.indexOf(search.value) !== -1
                    || (v.specimen_no + '').indexOf(search.value) !== -1
                );
            }

            return props.visits.filter(v => v.hn.indexOf(search.value) !== -1|| v.patient_name.indexOf(search.value) !== -1);
        });

        const cardfilters = computed(() => {
            if (['queue', 'mr', 'visit'].includes(props.card)) {
                return [
                    { name: 'exam', label: 'ตรวจ', on: false },
                    { name: 'swab', label: 'Swab', on: false },
                    { name: 'staff', label: 'เจ้าหน้าที่ศิริราช', on: false },
                    { name: 'public', label: 'บุคคลทั่วไป', on: false },
                    { name: 'appointment', label: 'นัด หรือ staff', on: false },
                    { name: 'walk_in', label: 'Walk-in', on: false },
                    { name: 'swab_at_scg', label: 'SCG', on: false },
                    { name: 'swab_at_sky_walk', label: 'Sky Walk', on: false },
                ];
            } else if (props.card === 'enqueue-swab' || props.card === 'swab') {
                return [
                    { name: 'swab_at_scg', label: 'SCG', on: false },
                    { name: 'swab_at_sky_walk', label: 'Sky Walk', on: false },
                ];
            } else {
                return [];
            }
        });
        const filtered = (name) => {
            filters[name] = !filters[name];
        };

        const filtersComponent = ref(null);

        const reload = () => {
            search.value = '';
            filters.exam = false;
            filters.swab = false;
            filters.staff = false;
            filters.public = false;
            filters.walk_in = false;
            filters.appointment = false;
            filters.swab_at_scg = false;
            filters.swab_at_sky_walk = false;

            if (cardfilters.value.length) {
                filtersComponent.value.reset();
            }
            Inertia.reload();
        };

        return {
            createVisitForm,
            appointmentForm,
            cancel,
            edit,
            search,
            filteredVisits,
            reload,
            filters,
            cardfilters,
            filtered,
            filtersComponent
        };
    },
};
</script>