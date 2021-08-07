<template>
    <div>
        <!-- export -->
        <a
            class="flex items-center text-green-600"
            :href="route('export.visits')"
            v-if="card === 'mr' && ($page.props.user.roles.includes('nurse') || $page.props.user.roles.includes('admin') || $page.props.user.roles.includes('root'))"
        >
            <Icon
                class="w-4 h-4 mr-1"
                name="file-excel"
            />
            <span class="block font-normal text-thick-theme-light">รายงาน</span>
        </a>

        <!-- search -->
        <div class="mb-2 relative">
            <input
                autocomplete="off"
                type="text"
                name="search"
                placeholder="🔍 ด้วย HN หรือ ชื่อ"
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

        <!-- filter  -->
        <div
            class="mb-2"
            v-if="card === 'mr'"
        >
            <button
                class="text-sm shadow-sm italic px-2 py-1 rounded-xl mr-2 bg-bitter-theme-light "
                :class="{
                    'border-2 border-white text-white': filters.exam,
                    'text-soft-theme-light': !filters.exam,
                }"
                @click="filters.exam = !filters.exam"
            >
                <Icon
                    class="ml-1 inline w-2 h-2"
                    name="filter"
                    v-if="filters.exam"
                />
                ตรวจ
            </button>
            <button
                class="text-sm shadow-sm italic px-2 py-1 rounded-xl mr-2 bg-bitter-theme-light "
                :class="{
                    'border-2 border-white text-white': filters.swab,
                    'text-soft-theme-light': !filters.swab,
                }"
                @click="filters.swab = !filters.swab"
            >
                <Icon
                    class="ml-1 inline w-2 h-2"
                    name="filter"
                    v-if="filters.swab"
                />
                Swab
            </button>
            <button
                class="text-sm shadow-sm italic px-2 py-1 rounded-xl mr-2 bg-bitter-theme-light "
                :class="{
                    'border-2 border-white text-white': filters.staff,
                    'text-soft-theme-light': !filters.staff,
                }"
                @click="filters.staff = !filters.staff"
            >
                <Icon
                    class="ml-1 inline w-2 h-2"
                    name="filter"
                    v-if="filters.staff"
                />
                เจ้าหน้าที่ศิริราช
            </button>
            <button
                class="text-sm shadow-sm italic px-2 py-1 rounded-xl mr-2 bg-bitter-theme-light "
                :class="{
                    'border-2 border-white text-white': filters.public,
                    'text-soft-theme-light': !filters.public,
                }"
                @click="filters.public = !filters.public"
            >
                <Icon
                    class="ml-1 inline w-2 h-2"
                    name="filter"
                    v-if="filters.public"
                />
                บุคคลทั่วไป
            </button>
            <button
                class="text-sm shadow-sm italic px-2 py-1 rounded-xl mr-2 bg-bitter-theme-light "
                :class="{
                    'border-2 border-white text-white': filters.walk_in,
                    'text-soft-theme-light': !filters.walk_in,
                }"
                @click="filters.walk_in = !filters.walk_in"
            >
                <Icon
                    class="ml-1 inline w-2 h-2"
                    name="filter"
                    v-if="filters.walk_in"
                />
                Walk-in
            </button>
            <button
                class="text-sm shadow-sm italic px-2 py-1 rounded-xl mr-2 bg-bitter-theme-light "
                :class="{
                    'border-2 border-white text-white': filters.appointment,
                    'text-soft-theme-light': !filters.appointment,
                }"
                @click="filters.appointment = !filters.appointment"
            >
                <Icon
                    class="ml-1 inline w-2 h-2"
                    name="filter"
                    v-if="filters.appointment"
                />
                นัด-staff
            </button>
        </div>

        <!-- lab summary -->
        <template
            class="mb-2"
            v-if="card === 'lab'"
        >
            <div class="p-2 rounded-md bg-white my-2">
                <p class="text-md font-semibold text-thick-theme-light underline">
                    รวม
                </p>
                <div class="flex flex-wrap ">
                    <span
                        v-for="(result, key) in ['Not detected', 'Detected', 'Inconclusive', 'Not found']"
                        :key="key"
                        class="inline-block my-1 whitespace-nowrap text-sm shadow-sm italic px-2 py-1 rounded-xl mr-2 text-white"
                        :class="{
                            'bg-green-400': result === 'Not detected',
                            'bg-yellow-400': result === 'Detected',
                            'bg-red-400': result === 'Inconclusive',
                            'bg-gray-600': result === 'Not found',
                        }"
                    >
                        {{ result }} {{ visits.filter(v => v.result.toLowerCase() === result.toLowerCase() ).length }}
                    </span>
                </div>
            </div>
            <div
                class="p-2 rounded-md bg-white my-2"
                v-for="type in ['บุคคลทั่วไป', 'เจ้าหน้าที่ศิริราช']"
                :key="type"
            >
                <p class="text-md font-semibold text-thick-theme-light underline">
                    {{ type }}
                </p>
                <div class="flex flex-wrap ">
                    <span
                        v-for="(result, key) in ['Not detected', 'Detected', 'Inconclusive', 'Not found']"
                        :key="key"
                        class="inline-block my-1 whitespace-nowrap text-sm shadow-sm italic px-2 py-1 rounded-xl mr-2 text-white"
                        :class="{
                            'bg-green-400': result === 'Not detected',
                            'bg-yellow-400': result === 'Detected',
                            'bg-red-400': result === 'Inconclusive',
                            'bg-gray-600': result === 'Not found',
                        }"
                    >
                        {{ result }} {{ visits.filter(v => v.patient_type === type && v.result.toLowerCase() === result.toLowerCase() ).length }}
                    </span>
                </div>
            </div>
        </template>

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
        <CardMedicalRecord
            v-else-if="card === 'mr'"
            :visits="filteredVisits"
        />
        <Queue
            v-else-if="card === 'queue'"
            :visits="filteredVisits"
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
import CardMedicalRecord from '@/Components/Cards/MedicalRecord';
import Queue from '@/Components/Cards/Queue';
import Visit from '@/Components/Forms/Visit';
import Appointment from '@/Components/Forms/Appointment';
import { computed, inject, nextTick, onUnmounted, reactive, ref } from '@vue/runtime-core';
import { Inertia } from '@inertiajs/inertia';

export default {
    layout: Layout,
    components: { Visit, Icon, CardScreen, CardExam, CardSwab, CardMedicalRecord, Queue, CardLab, Appointment },
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
            currentConfirm.action = 'cancel',
            currentConfirm.resource_id = visit.slug,
            emitter.emit('need-confirm', {
                confirmText: 'ยกเลิกการตรวจ ' + visit.title,
                needReason: true,
            });
        };
        emitter.on('confirmed', (text) => {
            if (currentConfirm.action === 'cancel') {
                Inertia.delete(window.route('visits.cancel', currentConfirm.resource_id), {
                    data: {reason: text},
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
        });
        const filteredVisits = computed(() => {
            if (! search.value) {
                return props.visits
                    .filter(v => filters.exam ? !v.swab : true)
                    .filter(v => filters.swab ? v.swab : true)
                    .filter(v => filters.staff ? v.patient_type === 'เจ้าหน้าที่ศิริราช' : true)
                    .filter(v => filters.public ? v.patient_type === 'บุคคลทั่วไป' : true)
                    .filter(v => filters.walk_in ? v.group === 'walk-in' : true)
                    .filter(v => filters.appointment ? v.group === 'นัด-staff' : true);
            }

            return props.visits.filter(v => v.hn.indexOf(search.value) !== -1 || v.patient_name.indexOf(search.value) !== -1);
        });

        const reload = () => {
            search.value = '';
            filters.exam = false;
            filters.swab = false;
            filters.staff = false;
            filters.public = false;
            filters.walk_in = false;
            filters.appointment = false;

            Inertia.reload();
        };

        return {
            createVisitForm,
            appointmentForm,
            cancel,
            search,
            filteredVisits,
            reload,
            filters,
        };
    },
};
</script>