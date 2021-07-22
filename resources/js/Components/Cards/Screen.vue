<template>
    <!-- card -->
    <div
        class="rounded bg-white shadow-sm my-1 p-1 flex"
        v-for="(visit, key) in visits"
        :key="key"
    >
        <!-- left detail -->
        <div class="w-3/4">
            <p class="p-1 pb-0 text-thick-theme-light">
                {{ visit.patient_type }} filterable
            </p>
            <div class="flex items-baseline">
                <p class="p-1 text-lg pt-0">
                    HN {{ visit.hn }} <br class="md:hidden"> {{ visit.patient_name }}
                </p>
            </div>
            <p class="px-1 text-xs text-dark-theme-light italic">
                เริ่มคัดกรองเมื่อ {{ visit.enlisted_screen_at_for_humans }} sortable
            </p>
        </div>
        <!-- right menu -->
        <div class="w-1/4 text-sm p-1 grid justify-items-center ">
            <!-- update -->
            <Link
                class="w-full flex text-yellow-200 justify-start"
                :href="route('visits.edit', visit)"
                v-if="visit.can.update"
            >
                <Icon
                    class="w-4 h-4 mr-1"
                    name="edit"
                />
                <span class="block font-normal text-thick-theme-light">เขียน</span>
            </Link>
        </div>
    </div>
</template>

<script>
import Icon from '@/Components/Helpers/Icon';
import { Link } from '@inertiajs/inertia-vue3';
export default {
    components: { Icon, Link },
    props: {
        visits: { type: Array, required: true }
    },
    setup() {

    },
};
</script>