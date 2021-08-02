<template>
    <!-- card -->
    <div
        class="rounded bg-white shadow-sm my-1 p-1 flex"
        :class="{'bg-gray-100': visit.status === 'canceled'}"
        v-for="(visit, key) in visits"
        :key="key"
    >
        <template v-if="visit.status === 'canceled'">
            <!-- left detail -->
            <div class="w-3/4">
                <p class="p-1 text-lg pt-0 text-gray-400">
                    HN {{ visit.hn }} <br class="md:hidden"> {{ visit.patient_name }}
                </p>
            </div>
            <!-- right menu -->
            <div class="w-1/4 text-sm p-1">
                <div
                    class="flex text-gray-400 justify-start cursor-not-allowed"
                >
                    <Icon
                        class="w-4 h-4 mr-1"
                        name="trash-alt"
                    />
                    <span class="block font-normal">ยกเลิกแล้ว</span>
                </div>
            </div>
        </template>
        <template v-else>
            <!-- left detail -->
            <div class="w-3/4">
                <p class="p-1 pb-0 text-thick-theme-light">
                    {{ visit.patient_type ?? 'ยังไม่ระบุประเภท' }}
                </p>
                <div class="flex items-baseline">
                    <p
                        class="p-1 text-lg pt-0"
                    >
                        HN {{ visit.hn ?? ' ยังไม่มี HN ' }} <br class="md:hidden"> {{ visit.patient_name }}
                    </p>
                </div>
                <p class="px-1 text-xs text-dark-theme-light italic">
                    เริ่มคัดกรองเมื่อ {{ visit.enlisted_screen_at_for_humans }}
                </p>
            </div>
            <!-- right menu -->
            <div class="w-1/4 text-sm p-1 grid justify-items-center">
                <!-- queue -->
                <div>
                    <Link
                        class="inline-flex text-alt-theme-light justify-start"
                        :href="route('visits.queue.store', visit)"
                        as="button"
                        method="post"
                        preserve-state
                        preserve-scroll
                        v-if="visit.can.queue"
                    >
                        <Icon
                            class="w-4 h-4 mr-1"
                            name="sync-alt"
                        />
                        <span class="block font-normal text-thick-theme-light">SI Flow</span>
                    </Link>
                </div>
                <!-- fill hn -->
                <div>
                    <button
                        class="inline-flex text-alt-theme-light justify-start"
                        v-if="visit.can.fill_hn"
                        @click="fillHn(visit)"
                    >
                        <Icon
                            class="w-4 h-4 mr-1"
                            name="sync-alt"
                        />
                        <span class="block font-normal text-thick-theme-light">บันทึก HN</span>
                    </button>
                </div>
            </div>
        </template>
    </div>
    <FillHn
        ref="fillHnModal"
    />
</template>

<script>
import Icon from '@/Components/Helpers/Icon';
import { Link } from '@inertiajs/inertia-vue3';
import FillHn from '@/Components/Forms/FillHn';
import { ref } from '@vue/reactivity';
export default {
    components: { Icon, Link, FillHn },
    props: {
        visits: { type: Array, required: true }
    },
    setup() {
        const fillHnModal = ref(null);
        const fillHn = (visit) => {
            fillHnModal.value.open(visit);
        };

        return {
            fillHn,
            fillHnModal,
        };
    },
};
</script>