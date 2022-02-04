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

        <!-- compare JK -->
        <div
            class="mb-2"
            v-if="($page.props.user.roles.includes('admin') || $page.props.user.roles.includes('root')) && card === 'lab'"
        >
            <FormSelect
                label="กลุ่มเปรียบเทียบ"
                name="jkCompareMode"
                v-model="jkCompareMode"
                :options="['ทั้งหมด', 'บุคคลทั่วไป', 'เจ้าหน้าที่ศิริราช']"
            />
            <div class="grid grid-cols-3">
                <FormTextarea
                    name="jk_hn"
                    v-model="jk_hn"
                    placeholder="Copy HN จาก excel มา paste ที่นี่"
                />
                <FormTextarea
                    name="jk_hn"
                    v-model="yuzuNotInJk"
                    placeholder="HN Yuzu ที่ไม่มีใน JK"
                    :readonly="true"
                    ref="yuzuNotInJkTextarea"
                />
                <FormTextarea
                    name="jk_hn"
                    v-model="jkNotInYuzu"
                    placeholder="HN JK ที่ไม่มีใน Yuzu"
                    :readonly="true"
                    ref="jkNotInYuzuTextarea"
                />
            </div>
        </div>

        <!-- search -->
        <div class="mb-2 relative">
            <input
                autocomplete="off"
                type="text"
                name="search"
                :placeholder="card === 'visit' ? '🔍 ด้วย HN หรือ ชื่อ หรือ หมายเลขหลอด' : '🔍 ด้วย HN หรือ ชื่อ'"
                v-model="search"
                @focus="reload(false)"
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
        <CardSwabNotification
            v-else-if="card === 'swab-notification'"
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
import CardSwabNotification from '@/Components/Cards/SwabNotification';
import Filters from '@/Components/Cards/Filters';
import Visit from '@/Components/Forms/Visit';
import Appointment from '@/Components/Forms/Appointment';
import FormSelect from '@/Components/Controls/FormSelect';
import FormTextarea from '@/Components/Controls/FormTextarea';
import { computed, nextTick, onUnmounted, reactive, ref, watch } from '@vue/runtime-core';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/inertia-vue3';

export default {
    layout: Layout,
    components: { Visit, Icon, CardScreen, CardExam, CardSwab, CardMedicalRecord, CardEnqueueSwab, CardQueue, CardLab, CardVisit, CardSwabNotification, Filters, Appointment, FormTextarea, FormSelect },
    props: {
        visits: { type: Object, required: true },
        card: { type: String, required: true },
        eventSource: { type: String, default: '' }
    },
    setup (props) {
        const createVisitForm = ref(null);
        const appointmentForm = ref(null);
        const currentConfirm = reactive({
            action: null,
            resource_id: null,
        });
        const cancel = (visit) => {
            currentConfirm.action = 'cancel';
            currentConfirm.resource_id = visit.slug;

            usePage().props.value.event.payload = { confirmText: 'ยกเลิกการตรวจ ' + visit.title, needReason: true };
            usePage().props.value.event.name = 'need-confirm';
            usePage().props.value.event.fire = + new Date();
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

            usePage().props.value.event.payload = { confirmText: confirmText, needReason: false };
            usePage().props.value.event.name = 'need-confirm';
            usePage().props.value.event.fire = + new Date();
        };

        watch (
            () => usePage().props.value.event.fire,
            (event) => {
                if (! event) {
                    return;
                }

                if (usePage().props.value.event.name === 'confirmed') {
                    if (currentConfirm.action === 'cancel') {
                        Inertia.delete(window.route('visits.cancel', currentConfirm.resource_id), {
                            data: {reason: usePage().props.value.event.payload},
                            preserveState: true,
                            preserveScroll: true,
                        });
                    } else if (currentConfirm.action === 'edit') {
                        Inertia.get(window.route('visits.replace', currentConfirm.resource_id), {
                            preserveState: true,
                            preserveScroll: true,
                        });
                    }
                } else if (usePage().props.value.event.name === 'action-clicked') {
                    if (usePage().props.value.event.payload === 'create-visit') {
                        nextTick(() => createVisitForm.value.open());
                    } else if (usePage().props.value.event.payload === 'create-appointment') {
                        nextTick(() => appointmentForm.value.open());
                    }
                }
            }
        );

        if (props.eventSource) {
            const eventSource = new EventSource(window.route('sse') + '?channel=' + props.eventSource, { withCredentials: true });
            let updatestamp = 0;

            eventSource.onmessage = (e) => {
                let data = JSON.parse(e.data);
                if (data.updatestamp > updatestamp) {
                    Inertia.reload();
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
                    .filter(v => filters.appointment ? v.track === 'นัด หรือ staff หรือ ญาติเจ้าหน้าที่' : true)
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
                    { name: 'appointment', label: 'นัด หรือ staff หรือ ญาติเจ้าหน้าที่', on: false },
                    { name: 'walk_in', label: 'Walk-in', on: false },
                    { name: 'swab_at_scg', label: 'SCG', on: false },
                    { name: 'swab_at_sky_walk', label: 'Sky Walk', on: false },
                ];
            } else if (['enqueue-swab', 'swab', 'swab-notification'].includes(props.card)) {
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

        const reload = (reset = true) => {
            if (!reset) {
                Inertia.reload();
                return;
            }
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

        const jk_hn = ref('');
        const jkCompareMode = ref('เจ้าหน้าที่ศิริราช');
        const jk = computed(() => {
            return jk_hn.value.split('\n').map(h => h.replace('-', '')).filter(h => h && h.length === 8);
        });
        const yuzu = computed(() => {
            if (jkCompareMode.value === 'ทั้งหมด') {
                return props.visits.map(v => v.hn);
            } else {
                return props.visits.filter(v => v.patient_type === jkCompareMode.value).map(v => v.hn);
            }
        });
        const yuzuNotInJk = computed(() => {
            if (! jk_hn.value) {
                return '';
            }
            return yuzu.value.filter(y => !jk.value.includes(y)).join('\n');
        });
        const jkNotInYuzu = computed(() => {
            return jk.value.filter(y => !yuzu.value.includes(y)).join('\n');
        });
        const yuzuNotInJkTextarea = ref(null);
        const jkNotInYuzuTextarea = ref(null);
        const resizeCompareTextarea = () => {
            nextTick(() => {
                yuzuNotInJkTextarea.value.resize();
                jkNotInYuzuTextarea.value.resize();
            });
        };

        watch(
            () => jk_hn.value,
            () => resizeCompareTextarea()
        );

        watch(
            () => jkCompareMode.value,
            () => resizeCompareTextarea()
        );

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
            filtersComponent,
            jk_hn,
            yuzuNotInJk,
            jkNotInYuzu,
            jkCompareMode,
            yuzuNotInJkTextarea,
            jkNotInYuzuTextarea
        };
    },
};
</script>