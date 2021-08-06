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
                    <div v-if="visit.authorized">
                        <button
                            class="inline-flex text-bitter-theme-light justify-start disabled:cursor-not-allowed"
                            disabled
                        >
                            <Icon
                                class="w-4 h-4 mr-1"
                                name="check-circle"
                            />
                            <span class="block font-normal text-thick-theme-light">เปิด Visit แล้ว</span>
                        </button>
                    </div>
                    <div v-else>
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
                    <div v-if="visit.attached">
                        <button
                            class="inline-flex text-bitter-theme-light justify-start disabled:cursor-not-allowed"
                            disabled
                        >
                            <Icon
                                class="w-4 h-4 mr-1"
                                name="check-circle"
                            />
                            <span class="block font-normal text-thick-theme-light">พิมพ์แล้ว</span>
                        </button>
                    </div>
                    <div v-else>
                        <Link
                            class="inline-flex text-alt-theme-light justify-start disabled:cursor-not-allowed"
                            :href="route('visits.attach-opd-card.store', visit)"
                            as="button"
                            method="post"
                            preserve-state
                            preserve-scroll
                            :disabled="!visit.can.attach_opd_card"
                        >
                            <Icon
                                class="w-4 h-4 mr-1"
                                name="sync-alt"
                            />
                            <span class="block font-normal text-thick-theme-light">พิมพ์</span>
                        </Link>
                    </div>
                    <div v-if="visit.can.attach_opd_card">
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
                    <div v-if="visit.can.print_opd_card">
                        <Link
                            class="inline-flex text-alt-theme-light justify-start"
                            :href="route('visits.show', visit)"
                        >
                            <Icon
                                class="w-4 h-4 mr-1"
                                name="readme"
                            />
                            <span class="block font-normal text-thick-theme-light">อ่าน</span>
                        </Link>
                    </div>
                    <div v-if="visit.can.replace">
                        <Link
                            class="inline-flex text-alt-theme-light justify-start"
                            :href="route('visits.replace', visit)"
                        >
                            <Icon
                                class="w-4 h-4 mr-1"
                                name="eraser"
                            />
                            <span class="block font-normal text-thick-theme-light">แก้ไข</span>
                        </Link>
                    </div>
                </template>
            </div>
        </template>
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