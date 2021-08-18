<template>
    <div
        class="-mt-8 flex justify-end"
        v-if="selectedVisits.length"
    >
        <Dropdown
            class="w-1/2 text-right"
            :auto-close="false"
        >
            <template #default>
                <div class="flex items-center cursor-pointer select-none group text-dark-theme-light">
                    <div class="group-hover:text-bitter-theme-light focus:text-bitter-theme-light mr-1 whitespace-no-wrap">
                        ({{ selectedVisits.length }}) ส่งกระติก
                    </div>
                    <Icon
                        class="w-4 h-4 group-hover:text-bitter-theme-light focus:text-bitter-theme-light"
                        name="share-square"
                    />
                </div>
            </template>
            <template #dropdown>
                <div class="rounded-lg p-2 bg-thick-theme-light space-y-2">
                    <div
                        class="rounded bg-white shadow-sm p-1 text-sm text-left"
                        v-for="(visit, key) in selectedVisits"
                        :key="key"
                    >
                        <div class="text-thick-theme-light">
                            <span class="font-semibold mr-1"># {{ visit.specimen_no }}</span>
                            {{ visit.patient_type }}
                            <span class="underline ml-1">{{ visit.swab_at }}</span>
                        </div>
                        <div>
                            HN {{ visit.hn }}
                            {{ visit.patient_name }}
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <SpinnerButton
                            @click="enqueue('SCG')"
                            class="btn btn-bitter text-sm w-full"
                            :spin="form.processing && form.swab_at === 'SCG'"
                        >
                            ส่ง SCG
                        </SpinnerButton>
                        <SpinnerButton
                            @click="enqueue('Sky Walk')"
                            class="btn btn-dark text-sm w-full"
                            :spin="form.processing && form.swab_at === 'Sky Walk'"
                        >
                            ส่ง Sky Walk
                        </SpinnerButton>
                    </div>
                </div>
            </template>
        </Dropdown>
    </div>

    <!-- card -->
    <transition-group
        name="flip-list"
        tag="div"
    >
        <div
            class="rounded bg-white shadow-sm my-1 p-1 flex"
            v-for="(visit, key) in [...visits].sort((a, b) => a.specimen_no > b.specimen_no )"
            :key="key"
        >
            <!-- left detail -->
            <div class="w-3/4">
                <p class="p-1 pb-0 text-thick-theme-light">
                    {{ visit.patient_type }}
                    <span class="mr-1 underline">{{ visit.swab_at }}</span>
                </p>
                <div class="flex items-baseline">
                    <p class="p-1 text-lg pt-0">
                        HN {{ visit.hn }} <br class="md:hidden"> {{ visit.patient_name }}
                    </p>
                </div>
                <p class="px-1 text-xs text-dark-theme-light italic">
                    ส่ง swab เมื่อ {{ visit.enlisted_swab_at_for_humans }}
                </p>
            </div>
            <!-- right menu -->
            <div class="w-1/4 text-sm p-1 grid justify-items-start">
                <div class="flex items-center">
                    <FormCheckbox
                        v-model="visit.selected"
                        @autosave="saveSelected"
                    />
                    <Icon
                        class="w-4 h-4 text-bitter-theme-light"
                        name="vial"
                    />
                    <p class="text-xl font-semibold text-thick-theme-light">
                        # {{ visit.specimen_no }}
                    </p>
                </div>
            </div>
        </div>
    </transition-group>
</template>

<script>
import Dropdown from '@/Components/Helpers/Dropdown';
import Icon from '@/Components/Helpers/Icon';
import FormCheckbox from '@/Components/Controls/FormCheckbox';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import { useForm } from '@inertiajs/inertia-vue3';
import { Inertia } from '@inertiajs/inertia';
import { computed, ref } from '@vue/reactivity';
export default {
    components: { Icon, Dropdown, FormCheckbox, SpinnerButton },
    props: {
        visits: { type: Array, required: true }
    },
    setup(props) {
        const localVisits = computed(() => {
            return props.visits.map(v => {
                v.selected = storedSelected.value.includes(v.id);
                return v;
            });
        });

        const discharge = (visit) => {
            Inertia.patch(window.route('visits.discharge-list.update', visit), {
                preserveState: true,
                preserveScroll: true,
            });
        };

        const selectedVisits = computed(() => {
            return props.visits.filter(v => v.selected).sort((a, b) => a.specimen_no > b.specimen_no);
        });

        const form = useForm({
            ids: [],
            swab_at: null
        });

        const enqueue = (swabAt) => {
            form.swab_at = swabAt;
            form.ids = selectedVisits.value.map(v => v.id);
            form.post(window.route('visits.enqueue-swab-list.store'), {
                onFinish: () => {
                    form.processing = false;
                    storedSelected.value = [];
                }
            });
        };

        const storedSelected = ref([]);
        const saveSelected = () => {
            storedSelected.value = [...props.visits.filter(v => v.selected).map(v => v.id)];
        };

        return {
            discharge,
            selectedVisits,
            form,
            enqueue,
            saveSelected,
            storedSelected,
            localVisits
        };
    },
};
</script>