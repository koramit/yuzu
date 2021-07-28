<template>
    <div>
        <a
            class="flex items-center text-green-600"
            :href="route('export.visits')"
            v-if="card === 'mr' && $page.props.user.roles.includes('nurse')"
        >
            <Icon
                class="w-4 h-4 mr-1"
                name="file-excel"
            />
            <span class="block font-normal text-thick-theme-light">รายงาน</span>
        </a>
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

        <Visit ref="createVisitForm" />
        <Appointment ref="appointmentForm" />
    </div>
</template>

<script>
import Layout from '@/Components/Layouts/Layout';
import Icon from '@/Components/Helpers/Icon';
import CardScreen from '@/Components/Cards/Screen';
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
    components: { Visit, Icon, CardScreen, CardExam, CardSwab, CardMedicalRecord, Queue, Appointment },
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
        const filteredVisits = computed(() => {
            if (! search.value) {
                return props.visits;
            }

            return props.visits.filter(v => v.hn.indexOf(search.value) !== -1 || v.patient_name.indexOf(search.value) !== -1);
        });
        const reload = () => {
            search.value = '';
            Inertia.reload();
        };

        return {
            createVisitForm,
            appointmentForm,
            cancel,
            search,
            filteredVisits,
            reload
        };
    },
};
</script>