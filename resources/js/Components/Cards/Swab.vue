<template>
    <!-- on holde visits -->
    <div
        class="rounded-lg bg-alt-theme-light p-4 mb-6"
        v-if="onHoldVisits.length"
    >
        <div class="flex items-center justify-between text-white mb-2">
            <div class="flex items-center">
                <Icon
                    class="ml-2 mr-1 w-4 h-4"
                    name="pause-circle"
                />
                <span class="mr-1 text-xl font-semibold">
                    เคสรอ
                </span>
            </div>
        </div>

        <!-- card -->
        <div
            class="rounded bg-white shadow-sm my-1 p-1 flex"
            v-for="(patient, key) in onHoldVisits"
            :key="key"
        >
            <!-- left detail -->
            <div class="w-3/4">
                <div class="flex items-center">
                    <p class="p-1 pb-0 text-thick-theme-light">
                        {{ patient.patient_type }}
                    </p>
                    <Icon
                        class="ml-2 mr-1 w-4 h-4 text-bitter-theme-light"
                        name="vial"
                    />
                    <p class="mr-1 text-xl font-semibold text-thick-theme-light">
                        # {{ patient.specimen_no }}
                    </p>
                </div>

                <div class="flex items-baseline">
                    <p class="p-1 text-lg pt-0">
                        HN {{ patient.hn }} <br class="md:hidden"> {{ patient.patient_name }}
                    </p>
                </div>
                <p class="px-1 text-xs text-dark-theme-light italic">
                    ส่ง swab เมื่อ {{ patient.enlisted_swab_at_for_humans }}
                </p>
            </div>
            <!-- right menu -->
            <div class="w-1/4 text-sm p-1 grid justify-items-start">
                <Dropdown
                    class="w-full"
                    :dropleft="true"
                >
                    <template #default>
                        <div class="flex items-center cursor-pointer select-none group text-bitter-theme-light">
                            <Icon
                                class="w-4 h-4 mr-1 group-hover:text-dark-theme-light focus:text-dark-theme-light"
                                name="share-square"
                            />
                            <div class="group-hover:text-dark-theme-light focus:text-dark-theme-light mr-1 whitespace-nowrap">
                                จำหน่าย
                            </div>
                        </div>
                    </template>
                    <template #dropdown>
                        <div class="bg-thick-theme-light rounded-lg space-y-4 py-2">
                            <button
                                class="flex w-full justify-start items-center hover:bg-dark-theme-light py-1 px-2 whitespace-nowrap"
                                v-if="patient.can.discharge && !patient.busy"
                                @click="discharge(patient, true)"
                            >
                                <Icon
                                    class="w-4 h-4 mr-1 text-bitter-theme-light"
                                    name="check-circle"
                                />
                                <span class="block font-normal text-white">ได้ทำ Swab</span>
                            </button>
                            <button
                                class="flex w-full justify-start items-center hover:bg-dark-theme-light py-1 px-2 whitespace-nowrap"
                                v-if="patient.can.discharge && !patient.busy"
                                @click="discharge(patient)"
                            >
                                <Icon
                                    class="w-4 h-4 mr-1 text-red-400"
                                    name="times-circle"
                                />
                                <span class="block font-normal text-white">ไม่ทำ Swab</span>
                            </button>
                        </div>
                    </template>
                </Dropdown>
                <Dropdown
                    class="w-full"
                    :dropleft="true"
                >
                    <template #default>
                        <div class="flex items-center cursor-pointer select-none group text-dark-theme-light">
                            <Icon
                                class="w-4 h-4 mr-1 group-hover:text-bitter-theme-light focus:text-bitter-theme-light"
                                name="box"
                            />
                            <div class="group-hover:text-bitter-theme-light focus:text-bitter-theme-light mr-1 whitespace-nowrap">
                                ใส่กระติก
                            </div>
                        </div>
                    </template>
                    <template #dropdown>
                        <div class="grid bg-thick-theme-light rounded-lg py-2">
                            <button
                                v-for="no in containers.filter(c => c.swab_at === patient.swab_at).map(n => n.no)"
                                :key="no"
                                class="block w-full text-left hover:bg-dark-theme-light text-white py-1 px-4"
                                @click="putIn(patient, no)"
                            >
                                # {{ no }}
                            </button>
                            <button
                                v-for="unit in swabUnits.filter(u => u !== patient.swab_at)"
                                :key="unit"
                                class="block w-full text-left hover:bg-dark-theme-light text-white py-1 px-4"
                                @click="putIn(patient, unit)"
                            >
                                ส่ง {{ unit }}
                            </button>
                            <button
                                v-if="!containers.filter(c => c.swab_at === patient.swab_at).map(n => n.no).length"
                                class="block w-full text-left hover:bg-dark-theme-light text-white py-1 px-4"
                                @click="putIn(patient, 'new')"
                            >
                                กระติกใหม่
                            </button>
                        </div>
                    </template>
                </Dropdown>
            </div>
        </div>
    </div>

    <!-- container -->
    <div
        v-for="container in containers"
        :key="container.no"
        class="rounded-lg bg-thick-theme-light p-4 mb-6"
    >
        <div class="flex items-center justify-between text-white mb-2">
            <div class="flex items-center">
                <Icon
                    class="ml-2 mr-1 w-4 h-4"
                    name="box"
                />
                <span class="mr-1 text-xl font-semibold">
                    # {{ container.no }}
                </span>
            </div>
            <Dropdown
                class="w-1/3 text-right"
            >
                <template #default>
                    <div class="flex items-center cursor-pointer select-none group text-white">
                        <Icon
                            class="w-4 h-4 mr-1 group-hover:text-bitter-theme-light focus:text-bitter-theme-light"
                            name="share-square"
                        />
                        <div class="group-hover:text-bitter-theme-light focus:text-bitter-theme-light mr-1 whitespace-nowrap">
                            ย้ายห้อง
                        </div>
                    </div>
                </template>
                <template #dropdown>
                    <div class="grid bg-bitter-theme-light rounded-lg py-2">
                        <button
                            v-for="unit in swabUnits.filter(u => u !== container.patients[0].swab_at)"
                            :key="unit"
                            class="block w-full text-left text-white hover:bg-white hover:text-thick-theme-light py-1 px-4"
                            @click="moveTo(unit, container.patients.map(p => p.id))"
                        >
                            ส่ง {{ unit }}
                        </button>
                    </div>
                </template>
            </Dropdown>
        </div>

        <!-- card -->
        <div
            class="rounded bg-white shadow-sm my-1 p-1 flex"
            v-for="(patient, key) in container.patients"
            :key="key"
        >
            <!-- left detail -->
            <div class="w-3/4">
                <div class="flex items-center">
                    <p class="p-1 pb-0 text-thick-theme-light">
                        {{ patient.patient_type }}
                    </p>
                    <Icon
                        class="ml-2 mr-1 w-4 h-4 text-bitter-theme-light"
                        name="vial"
                    />
                    <p class="mr-1 text-xl font-semibold text-thick-theme-light">
                        # {{ patient.specimen_no }}
                    </p>
                </div>

                <div class="flex items-baseline">
                    <p class="p-1 text-lg pt-0">
                        HN {{ patient.hn }} <br class="md:hidden"> {{ patient.patient_name }}
                    </p>
                </div>
                <p class="px-1 text-xs text-dark-theme-light italic">
                    ส่ง swab เมื่อ {{ patient.enlisted_swab_at_for_humans }}
                </p>
            </div>
            <!-- right menu -->
            <div class="w-1/4 text-sm p-1 grid justify-items-start">
                <!-- authorize -->
                <template v-if="patient.queued">
                    <div v-if="!patient.authorized">
                        <Link
                            class="inline-flex text-alt-theme-light justify-start disabled:cursor-not-allowed"
                            :href="route('visits.authorize.store', patient)"
                            as="button"
                            method="post"
                            preserve-state
                            preserve-scroll
                            :disabled="!patient.can.authorize_visit"
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
                <template v-if="patient.ready_to_print">
                    <div v-if="!patient.attached">
                        <a
                            class="inline-flex text-alt-theme-light justify-start"
                            :href="route('print-opd-card', patient)"
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
                <!-- <div>
                    <button
                        class="inline-flex justify-start items-center"
                        v-if="patient.can.discharge && !patient.busy"
                        @click="discharge(patient)"
                    >
                        <Icon
                            class="w-4 h-4 mr-1 text-bitter-theme-light"
                            name="share-square"
                        />
                        <span class="block font-normal text-thick-theme-light">จำหน่าย</span>
                    </button>
                </div> -->
                <Dropdown
                    class="w-full"
                    :dropleft="true"
                    v-if="patient.can.discharge && !patient.busy"
                >
                    <template #default>
                        <div class="flex items-center cursor-pointer select-none group text-bitter-theme-light">
                            <Icon
                                class="w-4 h-4 mr-1 group-hover:text-dark-theme-light focus:text-dark-theme-light"
                                name="share-square"
                            />
                            <div class="group-hover:text-dark-theme-light focus:text-dark-theme-light mr-1 whitespace-nowrap">
                                จำหน่าย
                            </div>
                        </div>
                    </template>
                    <template #dropdown>
                        <div class="bg-thick-theme-light rounded-lg space-y-4 py-2">
                            <button
                                class="flex w-full justify-start items-center hover:bg-dark-theme-light py-1 px-2 whitespace-nowrap"
                                @click="discharge(patient, true)"
                            >
                                <Icon
                                    class="w-4 h-4 mr-1 text-bitter-theme-light"
                                    name="check-circle"
                                />
                                <span class="block font-normal text-white">ได้ทำ Swab</span>
                            </button>
                            <button
                                class="flex w-full justify-start items-center hover:bg-dark-theme-light py-1 px-2 whitespace-nowrap"
                                @click="discharge(patient)"
                            >
                                <Icon
                                    class="w-4 h-4 mr-1 text-red-400"
                                    name="times-circle"
                                />
                                <span class="block font-normal text-white">ไม่ทำ Swab</span>
                            </button>
                        </div>
                    </template>
                </Dropdown>
                <div>
                    <button
                        v-if="patient.can.hold"
                        class="inline-flex justify-start items-center"
                        @click="hold(patient)"
                    >
                        <Icon
                            class="w-4 h-4 mr-1 text-dark-theme-light"
                            name="pause-circle"
                        />
                        <span class="block font-normal text-thick-theme-light">รอ</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Icon from '@/Components/Helpers/Icon';
import Dropdown from '@/Components/Helpers/Dropdown';
import { Link, useForm } from '@inertiajs/inertia-vue3';
import { Inertia } from '@inertiajs/inertia';
import { computed, ref } from '@vue/reactivity';
export default {
    components: { Icon, Link, Dropdown },
    props: {
        visits: { type: Array, required: true }
    },
    setup(props) {

        const discharge = (visit, swabOnHold = false) => {
            visit.busy = true;
            Inertia.patch(window.route('visits.discharge-list.update', visit), { swabbed: swabOnHold }, {
                preserveState: true,
                preserveScroll: true,
            });
        };

        const hold = (visit) => {
            window.axios
                .patch(window.route('visits.enqueue-swab-list.update', visit), { on_hold: true })
                .then(() => visit.on_hold = true)
                .catch(error => console.log(error));
        };

        const putIn = (visit, no) => {
            window.axios
                .patch(window.route('visits.enqueue-swab-list.update', visit), { on_hold: false, container_no: no })
                .then((response) => {
                    console.log(response.data);
                    if (response.data.move_to !== undefined) {
                        visit.swab_at = response.data.move_to;
                        return;
                    }
                    visit.on_hold = false;
                    visit.container_no = response.data.container_no;
                })
                .catch(error => console.log(error));
        };

        const containers = computed(() => {
            let containerNo = [...new Set(props.visits.map(v => v.container_no))].sort((a, b) => a > b);
            let transformContainers = [];
            containerNo.forEach(no => {
                let patients = props.visits.filter(v => v.container_no === no && !v.on_hold);
                transformContainers.push({
                    no: no,
                    swab_at: patients.length ? patients[0].swab_at : null,
                    patients: patients,
                });
            });

            return transformContainers.filter(c => c.patients.length);
        });

        const onHoldVisits = computed(() => {
            return props.visits.filter(v => v.on_hold);
        });

        const swabUnits = ref(['SCG', 'Sky Walk']);

        const moveTo = (swabAt, ids) => {
            let form = useForm({
                swab_at: swabAt,
                ids: ids,
                move: true,
            });
            form.post(window.route('visits.enqueue-swab-list.store'));
        };

        return {
            discharge,
            hold,
            putIn,
            containers,
            onHoldVisits,
            swabUnits,
            moveTo
        };
    },
};
</script>