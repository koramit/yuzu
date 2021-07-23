<template>
    <div>
        <!-- card -->
        <div
            class="rounded bg-white shadow-sm my-1 p-1 flex"
            v-for="(visit, key) in visits.data"
            :key="key"
        >
            <!-- left detail -->
            <div class="w-3/4">
                <p class="p-1 pb-0 text-thick-theme-light">
                    {{ visit.patient_type }} filterable
                </p>
                <div class="flex items-baseline">
                    <p class="p-1 text-lg pt-0">
                        {{ visit.date_visit }} <br class="md:hidden"> {{ visit.patient_name }}
                    </p>
                </div>
                <p class="px-1 text-xs text-dark-theme-light italic">
                    ปรับปรุงล่าสุด {{ visit.updated_at_for_humans }} sortable
                </p>
            </div>
            <!-- right menu -->
            <div class="w-1/4 text-sm p-1 grid justify-items-center ">
                <!-- write -->
                <!-- v-if="userCan('write', referCase)" -->
                <!-- <Link
                    class="w-full flex text-yellow-200 justify-start"
                    :href="route('visits.edit', visit)"
                >
                    <Icon
                        class="w-4 h-4 mr-1"
                        name="edit"
                    />
                    <span class="block font-normal text-thick-theme-light">เขียนต่อ</span>
                </Link> -->
                <!-- edit -->
                <!-- v-if="userCan('edit', referCase)" -->
                <!-- <Link
                    class="w-full flex text-alt-theme-light justify-start"
                    :href="route('visits.edit', visit)"
                >
                    <Icon
                        class="w-4 h-4 mr-1"
                        name="eraser"
                    />
                    <span class="block font-normal text-thick-theme-light">แก้ไข</span>
                </Link> -->
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

        <Visit ref="createVisitForm" />
    </div>
</template>

<script>
import Layout from '@/Components/Layouts/Layout';
import Icon from '@/Components/Helpers/Icon';
import Visit from '@/Components/Forms/Visit';
import { inject, nextTick, ref } from '@vue/runtime-core';
import { Link } from '@inertiajs/inertia-vue3';

export default {
    layout: Layout,
    components: { Visit, Icon, Link },
    props: {
        visits: { type: Object, required: true },
    },
    setup () {
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