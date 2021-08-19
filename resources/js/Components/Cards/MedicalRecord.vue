<template>
    <!-- card -->
    <transition-group
        name="flip-list"
        tag="div"
    >
        <div
            class="rounded bg-white shadow-sm my-1 p-1 flex"
            :class="{'bg-gray-100': visit.status === 'canceled'}"
            v-for="(visit, key) in visits"
            :key="key"
        >
            <!-- left detail -->
            <div class="w-3/4">
                <p class="p-1 pb-0 text-thick-theme-light">
                    <span
                        class="font-semibold mr-1"
                    >
                        {{ visit.patient_type ?? 'ยังไม่ระบุประเภท' }}
                    </span>
                    <span
                        class="underline mr-1"
                    >
                        {{ visit.group }}
                    </span>
                    <span
                        class="text-sm italic px-2 mr-1 text-bitter-theme-light"
                        v-if="visit.swab"
                    >
                        <Icon
                            class="inline w-4 h-4"
                            name="virus"
                        />
                        Swab
                    </span>
                    <span
                        class="text-sm italic px-2 mr-1 text-alt-theme-light"
                        v-else
                    >
                        <Icon
                            class="inline w-4 h-4"
                            name="stethoscope"
                        />
                        ตรวจ
                    </span>
                </p>
                <div class="flex items-baseline">
                    <p
                        class="p-1 text-lg pt-0"
                    >
                        HN {{ visit.hn }} <br class="md:hidden"> {{ visit.patient_name }}
                    </p>
                </div>
                <p class="px-1 text-xs text-dark-theme-light italic">
                    เริ่มคัดกรองเมื่อ {{ visit.enlisted_screen_at_for_humans }}
                </p>
            </div>
            <!-- right menu -->
            <div class="w-1/4 text-sm p-1 grid justify-items-start">
                <!-- authorize -->
                <template v-if="visit.queued">
                    <div v-if="!visit.authorized">
                        <Link
                            class="inline-flex text-alt-theme-light justify-start disabled:cursor-not-allowed"
                            :href="route('visits.authorize.store', visit)"
                            as="button"
                            method="post"
                            preserve-state
                            preserve-scroll
                            :disabled="!visit.can.authorize_visit"
                        >
                            <Icon
                                class="w-4 h-4 mr-1"
                                name="sync-alt"
                            />
                            <span class="block font-normal text-thick-theme-light">เปิด Visit</span>
                        </Link>
                    </div>
                </template>
                <div v-else>
                    <button
                        disabled
                        class="inline-flex text-thick-theme-light justify-start disabled:cursor-not-allowed"
                    >
                        <Icon
                            class="w-4 h-4 mr-1"
                            name="hourglass-half"
                        />
                        <span class="block font-normal text-thick-theme-light">ยังไม่มีคิว</span>
                    </button>
                </div>
                <!-- OPD card attached -->
                <template v-if="visit.ready_to_print">
                    <div v-if="!visit.attached">
                        <a
                            class="inline-flex text-alt-theme-light justify-start"
                            :href="route('print-opd-card', visit)"
                            target="_blank"
                        >
                            <Icon
                                class="w-4 h-4 mr-1"
                                name="print"
                            />
                            <span class="block font-normal text-thick-theme-light">พิมพ์</span>
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </transition-group>
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