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
                {{ visit.patient_type }}
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
        <div class="w-1/4 text-sm p-1 grid justify-items-center ">
            <!-- discharge -->
            <!-- <Link
                class="w-full flex text-bitter-theme-light justify-start"
                :href="route('visits.discharge', visit)"
                method="patch"
                v-if="visit.can.discharge"
                as="button"
                type="button"
            >
                <Icon
                    class="w-4 h-4 mr-1"
                    name="share-square"
                />
                <span class="block font-normal text-thick-theme-light">จำหน่าย</span>
            </Link> -->
            <button
                class="w-full flex text-bitter-theme-light justify-start"
                v-if="visit.can.discharge"
                @click="discharge(visit)"
            >
                <Icon
                    class="w-4 h-4 mr-1"
                    name="share-square"
                />
                <span class="block font-normal text-thick-theme-light">จำหน่าย</span>
            </button>
        </div>
    </div>
</template>

<script>
import Icon from '@/Components/Helpers/Icon';
import { Link } from '@inertiajs/inertia-vue3';
import { Inertia } from '@inertiajs/inertia';
export default {
    components: { Icon, Link },
    props: {
        visits: { type: Array, required: true }
    },
    setup() {
        const discharge = (visit) => {
            Inertia.patch(window.route('visits.discharge-list.update', visit), {
                preserveState: true,
                preserveScroll: true,
            });
        };

        return {
            discharge,
        };
    },
};
</script>