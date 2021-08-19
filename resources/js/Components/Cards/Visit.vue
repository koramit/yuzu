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
                        <span class="underline">
                            {{ visit.group }}
                        </span>
                        <template v-if="visit.swab">
                            <span class="text-sm italic px-2 text-bitter-theme-light">
                                <Icon
                                    class="inline w-4 h-4"
                                    name="virus"
                                />
                                Swab
                            </span>
                            <span
                                v-if="visit.specimen_no"
                                class="text-sm italic px-2 text-bitter-theme-light"
                            >
                                <Icon
                                    class="inline w-4 h-4"
                                    name="vial"
                                />
                                # {{ visit.specimen_no }}
                            </span>
                        </template>
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
                    <button
                        class="inline-flex  justify-start disabled:cursor-not-allowed"
                        disabled
                    >
                        <Icon
                            class="w-4 h-4 mr-1"
                            :class="{
                                'text-bitter-theme-light': visit.authorized,
                                'text-red-400': !visit.authorized,
                            }"
                            :name="visit.authorized ? 'check-circle':'times-circle'"
                        />
                        <span class="block font-normal text-thick-theme-light">เปิด Visit แล้ว</span>
                    </button>
                    <!-- OPD card attached -->
                    <button
                        class="inline-flex  justify-start disabled:cursor-not-allowed"
                        disabled
                    >
                        <Icon
                            class="w-4 h-4 mr-1"
                            :class="{
                                'text-bitter-theme-light': visit.attached,
                                'text-red-400': !visit.attached,
                            }"
                            :name="visit.attached ? 'check-circle':'times-circle'"
                        />
                        <span class="block font-normal text-thick-theme-light">พิมพ์แล้ว</span>
                    </button>
                    <template v-if="visit.ready_to_print">
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