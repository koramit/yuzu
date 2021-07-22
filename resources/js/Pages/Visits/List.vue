<template>
    <div>
        <!-- card -->
        <CardScreen
            v-if="card === 'screen'"
            :visits="visits"
        />
        <CardExam
            v-else-if="card === 'exam'"
            :visits="visits"
        />
        <CardSwab
            v-else-if="card === 'swab'"
            :visits="visits"
        />
        <CardMedicalRecord
            v-else-if="card === 'mr'"
            :visits="visits"
        />

        <Visit ref="createVisitForm" />
    </div>
</template>

<script>
import Layout from '@/Components/Layouts/Layout';
import Icon from '@/Components/Helpers/Icon';
import CardScreen from '@/Components/Cards/Screen';
import CardExam from '@/Components/Cards/Exam';
import CardSwab from '@/Components/Cards/Swab';
import CardMedicalRecord from '@/Components/Cards/MedicalRecord';
import Visit from '@/Components/Forms/Visit';
import { inject, nextTick, onUnmounted, ref } from '@vue/runtime-core';
import { Link } from '@inertiajs/inertia-vue3';
import { Inertia } from '@inertiajs/inertia';

export default {
    layout: Layout,
    components: { Visit, Icon, Link, CardScreen, CardExam, CardSwab, CardMedicalRecord },
    props: {
        visits: { type: Object, required: true },
        card: { type: String, required: true },
        eventSource: { type: String, default: '' }
    },
    setup (props) {
        const createVisitForm = ref(null);
        const emitter = inject('emitter');

        emitter.on('action-clicked', (action) => {
            // please expect console log error in case of revisit this page
            // maybe new vue fragment lazy loading template so it not
            // ready to use and need some kind of "activate"
            if (action === 'create-visit') {
                nextTick(() => createVisitForm.value.open());
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

        return {
            createVisitForm,
        };
    },
};
</script>