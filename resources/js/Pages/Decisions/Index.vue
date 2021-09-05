<template>
    <div class="md:flex md:space-x-2">
        <FormDatetime
            class="mb-2 md:w-2/5 xl:w-1/4"
            name="date_visit"
            label="วันที่ตรวจพบเชื้อ"
            v-model="formDateVisit"
            @autosave="$inertia.visit(route('decisions') + '?date_visit=' + formDateVisit)"
        />
        <FormInput
            class="mb-2 md:w-3/5 xl:w-3/4"
            name="date_visit"
            label="ค้นหาด้วยชื่อหรือ HN"
            v-model="search"
        />
    </div>

    <!-- table  -->
    <div
        class="hidden md:block rounded-md shadow overflow-x-auto overflow-y-scroll"
        style="max-height: 90%;"
    >
        <table class="w-full whitespace-nowrap relative bg-white">
            <tr class="text-left font-semibold">
                <th
                    class="px-3 pt-4 pb-2 sticky top-0 text-white bg-thick-theme-light"
                    :class="{'z-20': column === 'Decision'}"
                    v-for="column in headrows"
                    :key="column"
                    :colspan="column === 'Decision' ? 2:1"
                    v-text="column"
                />
            </tr>
            <tr
                v-for="(positive, key) in positives"
                :key="key"
                class="hover:bg-gray-100 focus-within:bg-gray-100"
            >
                <td class="border-t">
                    <p class="inline-flex px-3 py-2 items-center">
                        <Icon
                            v-if="!positive.refer_to && !positive.linked"
                            name="hourglass-half"
                            class="w-4 h-4 mr-1 text-thick-theme-light"
                        />
                        <button
                            v-else-if="positive.refer_to && !positive.linked"
                            @click="makeDecisionFromDropdown(positive, positive.refer_to)"
                        >
                            <Icon
                                name="sync-alt"
                                class="w-4 h-4 mr-1 text-dark-theme-light"
                            />
                        </button>
                        <Icon
                            v-else-if="positive.refer_to && positive.linked"
                            name="check-circle"
                            class="w-4 h-4 mr-1 text-bitter-theme-light"
                        />
                        {{ positive.patient_name }}
                    </p>
                </td>
                <td
                    class="border-t px-3 py-2"
                    :class="{'text-red-400': positive.age > 80}"
                >
                    {{ positive.age }}
                </td>
                <td class="border-t">
                    <span class="px-3 py-2">{{ positive.hn }}</span>
                </td>
                <td class="border-t">
                    <button
                        @click="callPositive(positive)"
                        :disabled="!positive.can.refer"
                        class="inline-flex items-center text-blue-300 px-3 py-2 disabled:cursor-not-allowed"
                    >
                        <Icon
                            name="phone-square"
                            class="w-4 h-4"
                        />
                        {{ positive.tel_no }}
                    </button>
                </td>
                <td class="border-t px-3 py-2">
                    {{ positive.patient_type }}
                </td>
                <td class="border-t px-3 py-2">
                    {{ positive.insuranceShow }}
                </td>
                <td
                    class="border-t px-3 py-2 whitespace-normal"
                    :class="{'text-bitter-theme-light': positive.ud === 'no'}"
                >
                    {{ positive.ud }}
                </td>
                <td
                    class="border-t px-3 py-2 whitespace-normal"
                    :class="{'text-bitter-theme-light': positive.symptom === 'asymptomatic'}"
                >
                    {{ positive.symptom }}
                </td>
                <td class="border-t px-3 py-2">
                    {{ positive.onset }}
                </td>
                <td
                    class="border-t px-3 py-2"
                    :class="{'text-red-400': positive.weight > 90}"
                >
                    {{ positive.weight }}
                </td>
                <td class="border-t px-3 py-2">
                    {{ positive.remark }}
                </td>
                <td
                    class="border-t px-3 py-2"
                    :class="{'text-bitter-theme-light': positive.refer_to}"
                >
                    {{ positive.refer_to ?? 'ยังไม่ตัดสินใจ' }}
                </td>
                <td class="border-t px-3 py-2">
                    <Dropdown v-if="positive.can.refer">
                        <template #default>
                            <button class="inline-flex items-center text-dark-theme-light">
                                <Icon
                                    class="w-4 h-4 mr-1"
                                    name="share-square"
                                />
                                <span class="block">เลือก</span>
                            </button>
                        </template>
                        <template #dropdown>
                            <div class="rounded shadow bg-bitter-theme-light text-white py-2">
                                <button
                                    v-for="referTo in referToOptions"
                                    :key="referTo"
                                    @click="makeDecisionFromDropdown(positive, referTo)"
                                    class="block w-full px-4 py-1 text-left hover:text-bitter-theme-light hover:bg-white transition-colors duration-200 ease-in-out"
                                    v-text="referTo"
                                />
                            </div>
                        </template>
                    </Dropdown>
                </td>
            </tr>
            <tr v-if="positives.length === 0">
                <td
                    class="border-t px-6 py-4"
                    colspan="11"
                >
                    {{ search ? 'ไม่มีเคสตามคำค้น':'ยังไม่มีเคสบวกสำหรับวันนี้' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- card  -->
    <div class="md:hidden">
        <div
            class="rounded shadow bg-white mb-2 p-4"
            v-for="(positive, key) in positives"
            :key="key"
        >
            <div class="flex justify-between items-center text-sm">
                <p class="flex items-center">
                    <button
                        class="inline-flex items-center text-blue-300 mr-2 disabled:cursor-not-allowed"
                        @click="callPositive(positive)"
                        :disabled="!positive.can.refer"
                    >
                        <Icon
                            name="phone-square"
                            class="w-4 h-4"
                        />
                        {{ positive.tel_no }}
                    </button>
                    <span class="italic mr-2">{{ positive.patient_type }}</span>
                </p>
                <Dropdown v-if="positive.can.refer">
                    <template #default>
                        <button class="inline-flex items-center">
                            <span
                                class="block mr-1 font-light whitespace-nowrap"
                                :class="{'text-bitter-theme-light': positive.refer_to}"
                            >{{ positive.refer_to ?? 'ยังไม่ตัดสินใจ' }}</span>
                            <Icon
                                class="w-4 h-4 mr-1 text-dark-theme-light"
                                name="share-square"
                            />
                            <span class="block text-dark-theme-light">เลือก</span>
                        </button>
                    </template>
                    <template #dropdown>
                        <div class="rounded shadow bg-bitter-theme-light text-white py-2">
                            <button
                                v-for="referTo in referToOptions"
                                :key="referTo"
                                @click="makeDecisionFromDropdown(positive, referTo)"
                                class="block w-full px-4 py-1 text-left hover:text-bitter-theme-light hover:bg-white transition-colors duration-200 ease-in-out"
                                v-text="referTo"
                            />
                        </div>
                    </template>
                </Dropdown>
            </div>
            <div class="mt-2 text-lg text-thick-theme-light font-medium">
                <p>
                    <span class="inline-flex items-center mr-1">
                        <Icon
                            v-if="!positive.refer_to && !positive.linked"
                            name="hourglass-half"
                            class="w-4 h-4 mr-1 text-thick-theme-light"
                        />
                        <button
                            v-else-if="positive.refer_to && !positive.linked"
                            @click="makeDecisionFromDropdown(positive, positive.refer_to)"
                        >
                            <Icon
                                name="sync-alt"
                                class="w-4 h-4 mr-1 text-dark-theme-light"
                            />
                        </button>
                        <Icon
                            v-else-if="positive.refer_to && positive.linked"
                            name="check-circle"
                            class="w-4 h-4 mr-1 text-bitter-theme-light"
                        />
                        {{ positive.hn }}
                        {{ positive.insuranceShow }}
                    </span>
                </p>
                <p>
                    <span class="mr-1">{{ positive.patient_name }}</span>
                    <span
                        class="mr-1"
                        :class="{'text-red-400 font-semibold': positive.age > 80}"
                    >{{ positive.age }}</span> ปี
                </p>
            </div>
            <div class="mt-2 flex space-x-2 text-sm">
                <div class="w-1/2 rounded-md shadow-sm bg-gray-100 p-2">
                    <p>
                        <span class="italic">UD: </span>
                        <span :class="{'text-bitter-theme-light font-medium': positive.comorbids === 'ไม่มี'}">{{ positive.ud }}</span>
                    </p>
                    <p v-if="positive.weight">
                        <span class="italic">Weight: </span>
                        <span :class="{'text-red-400 font-semibold': positive.weight > 90}">{{ positive.weight }}</span>
                    </p>
                </div>
                <div class="w-1/2 rounded-md shadow-sm bg-gray-100 p-2">
                    <p v-if="positive.onset">
                        <span class="italic">Onset: </span>
                        {{ positive.onset }}
                    </p>
                    <p>
                        <span class="italic">Symptom: </span>
                        <span :class="{'text-bitter-theme-light font-medium': !positive.onset}">{{ positive.symptom }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- decision -->
    <Modal
        ref="decisionModal"
        width-mode="form-cols-1"
    >
        <template #header>
            <div class="font-semibold text-dark-theme-light">
                <p>HN {{ selectedPositive.hn }} {{ selectedPositive.patient_name }} <span :class="{'text-red-400': selectedPositive.age > 80}">{{ selectedPositive.age }}</span> ปี</p>
                <p>โทร<span class="text-blue-300 underline mx-1">{{ selectedPositive.tel_no }}</span><span class="text-blue-300 underline">{{ selectedPositive.tel_no_alt }}</span></p>
            </div>
        </template>
        <template #body>
            <div style="max-height: 70vh; overflow-y: scroll;">
                <!-- type and insurance  -->
                <div class="mt-2 flex space-x-2">
                    <div class="w-1/2 rounded-md shadow-sm bg-gray-100 p-2">
                        <p>
                            <span class="italic">Type: </span>
                            {{ selectedPositive.patient_type }}
                        </p>
                    </div>
                    <div class="w-1/2 rounded-md shadow-sm bg-gray-100 p-2">
                        <p>
                            <span class="italic">Insurance: </span>
                            {{ selectedPositive.insuranceShow }}
                        </p>
                    </div>
                </div>
                <!-- ud and symptom -->
                <div class="mt-2 flex space-x-2">
                    <div class="w-1/2 rounded-md shadow-sm bg-gray-100 p-2">
                        <p>
                            <span class="italic">UD: </span>
                            <span :class="{'text-bitter-theme-light font-medium': selectedPositive.comorbids === 'ไม่มี'}">{{ selectedPositive.ud }}</span>
                        </p>
                        <p v-if="selectedPositive.weight">
                            <span class="italic">Weight: </span>
                            <span :class="{'text-red-400 font-semibold': selectedPositive.weight > 90}">{{ selectedPositive.weight }}</span>
                        </p>
                    </div>
                    <div class="w-1/2 rounded-md shadow-sm bg-gray-100 p-2">
                        <p v-if="selectedPositive.onset">
                            <span class="italic">Onset: </span>
                            {{ selectedPositive.onset }}
                        </p>
                        <p>
                            <span class="italic">Symptom: </span>
                            <span :class="{'text-bitter-theme-light font-medium': !selectedPositive.onset}">{{ selectedPositive.symptom }}</span>
                        </p>
                    </div>
                </div>
                <!-- CT -->
                <div
                    class="mt-2 rounded-md shadow-sm bg-gray-100 p-2"
                    v-if="selectedPositive.lab_remark"
                >
                    <p
                        class="italic text-red-400"
                        v-html="selectedPositive.lab_remark.replaceAll(' | ', '<br>')"
                    />
                </div>
                <!-- note -->
                <div
                    class="mt-2 rounded-md shadow-sm bg-gray-100 p-2"
                    v-if="selectedPositive.note"
                >
                    <p>
                        <span class="italic">Note: </span>
                        {{ selectedPositive.note }}
                    </p>
                </div>
                <FormDatetime
                    class="mt-4"
                    label="วันที่ตรวจพบเชื้อ"
                    name="date_covid_infected"
                    :disabled="true"
                    v-model="form.date_covid_infected"
                />
                <FormDatetime
                    class="mt-2"
                    label="วันที่ส่งตัว"
                    name="date_refer"
                    v-model="form.date_refer"
                />
                <FormTextarea
                    class="mt-2"
                    label="remark"
                    name="remark"
                    v-model="form.remark"
                />
                <div class="mt-2">
                    <label class="form-label">decision</label>
                    <FormRadio
                        class="md:grid grid-cols-2 gap-x-2"
                        name="refer_to"
                        :options="referToOptions"
                        v-model="form.refer_to"
                    />
                </div>
            </div>
        </template>
        <template #footer>
            <button
                class="mt-2 w-full btn btn-bitter"
                @click="makeDecision"
                :disabled="!form.refer_to || !form.date_refer"
            >
                บันทึก
            </button>
        </template>
    </Modal>
    <Visit ref="createVisitForm" />
    <Appointment ref="appointmentForm" />
</template>

<script setup>
import FormDatetime from '@/Components/Controls/FormDatetime';
import FormInput from '@/Components/Controls/FormInput';
import FormTextarea from '@/Components/Controls/FormTextarea';
import FormRadio from '@/Components/Controls/FormRadio';
import Icon from '@/Components/Helpers/Icon';
import Modal from '@/Components/Helpers/Modal';
import Dropdown from '@/Components/Helpers/Dropdown';
import Visit from '@/Components/Forms/Visit';
import Appointment from '@/Components/Forms/Appointment';

const props = defineProps({
    positiveCases: { type: Array, required: true },
    dateVisit: { type: String, required: true },
    referToOptions: { type: Array, required: true }
});

const createVisitForm = ref(null);
const appointmentForm = ref(null);
const emitter = inject('emitter');

emitter.on('action-clicked', (action) => {
    // please expect console log error in case of revisit this page
    // maybe new vue fragment lazy loading template so it not
    // ready to use and need some kind of "activate"
    if (action === 'create-visit') {
        nextTick(() => createVisitForm.value.open());
    } else if (action === 'create-appointment') {
        nextTick(() => appointmentForm.value.open());
    }
});

const headrows = ref(['Name','Age','HN','Tel','Type','Insurance','U/D','Symptom','Onset','Weight','Remark','Decision']);
const formDateVisit = ref(props.dateVisit);
const search = ref('');
const positives = computed(() => {
    return props.positiveCases.filter(p => p.hn.indexOf(search.value) !== -1 || p.patient_name.indexOf(search.value) !== -1);
});

const decisionModal = ref(null);
const selectedPositive = ref(null);
const form = reactive({});
const callPositive = (positive) => {
    selectedPositive.value = positive;
    form.remark = positive.remark;
    form.refer_to = positive.refer_to;
    form.date_covid_infected = positive.date_covid_infected;
    form.date_refer = positive.date_refer;
    nextTick(() => decisionModal.value.open());
};
const makeDecision = () => {
    nextTick(() => decisionModal.value.close());
    postDecision(form);
};
const makeDecisionFromDropdown = (positive, decision) => {
    selectedPositive.value = positive;
    postDecision({ refer_to: decision });
};
const postDecision = (decision) => {
    let formData = {...selectedPositive.value};
    formData.refer_to = decision.refer_to;
    formData.remark = decision.remark ?? null;
    formData.date_refer = decision.date_refer ?? formData.date_refer;
    window.axios
        .patch(window.route('decisions.update', selectedPositive.value.slug), formData)
        .then(response => {
            selectedPositive.value.refer_to = decision.refer_to;
            selectedPositive.value.date_refer = decision.date_refer;
            selectedPositive.value.remarl = decision.remarl;
            selectedPositive.value.linked = response.data.linked;
        });
};
</script>

<script>
import Layout from '@/Components/Layouts/Layout';
import { computed, reactive, ref } from '@vue/reactivity';
import { inject, nextTick } from '@vue/runtime-core';
export default { layout: Layout };
</script>