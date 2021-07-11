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

        <div v-if="visits.links.length > 3">
            <div class="flex flex-wrap -mb-1 mt-4">
                <template v-for="(link, key) in visits.links">
                    <div
                        v-if="link.url === null"
                        :key="key"
                        class="mr-1 mb-1 px-4 py-3 text-sm leading-4 bg-gray-200 text-gray-400 border rounded cursor-not-allowed"
                        v-html="link.label"
                    />
                    <inertia-link
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