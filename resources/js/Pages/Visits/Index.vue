<template>
    <div>
        <h1>index</h1>
        <InertiaLink
            v-for="(visit, key) in visits.data"
            :key="key"
            :href="route('visits.edit', visit)"
            class="block underline my-4"
        >
            {{ visit.date_visit }} - {{ visit.hn }} - {{ visit.patient_name }} - {{ visit.patient_type }}
        </InertiaLink>
        <Visit ref="createVisitForm" />
    </div>
</template>

<script>
import Layout from '@/Components/Layouts/Layout';
import Visit from '@/Components/Forms/Visit';
import { inject, nextTick, ref } from '@vue/runtime-core';
export default {
    layout: Layout,
    components: { Visit },
    props: {
        visits: { type: Object, required: true }
    },
    setup() {
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

        return {
            createVisitForm,
        };
    },
};
</script>