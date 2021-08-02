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
                ส่งตรวจเมื่อ {{ visit.enlisted_exam_at_for_humans }}
            </p>
        </div>
        <!-- right menu -->
        <div class="w-1/4 text-sm p-1 grid justify-items-start">
            <!-- update -->
            <div v-if="visit.can.update">
                <Link
                    class="inline-flex text-yellow-200 justify-start"
                    :href="route('visits.edit', visit)"
                >
                    <Icon
                        class="w-4 h-4 mr-1"
                        name="edit"
                    />
                    <span class="block font-normal text-thick-theme-light">เขียน</span>
                </Link>
            </div>
            <!-- cancel -->
            <div v-if="visit.can.cancel">
                <button
                    class="inline-flex text-red-200 justify-start"
                    :href="route('visits.edit', visit)"
                    @click="$emit('cancel', visit)"
                >
                    <Icon
                        class="w-4 h-4 mr-1"
                        name="trash-alt"
                    />
                    <span class="block font-normal text-thick-theme-light">ยกเลิก</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import Icon from '@/Components/Helpers/Icon';
import { Link } from '@inertiajs/inertia-vue3';
export default {
    emits: ['cancel'],
    components: { Icon, Link },
    props: {
        visits: { type: Array, required: true }
    },
    setup() {

    },
};
</script>