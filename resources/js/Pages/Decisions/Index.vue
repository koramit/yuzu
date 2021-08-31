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
    <div class="hidden md:block rounded-md shadow overflow-x-auto overflow-y-scroll max-h-90">
        <table class="w-full whitespace-nowrap relative bg-white">
            <tr class="text-left font-semibold">
                <th
                    class="px-3 pt-4 pb-2 sticky top-0 text-white bg-thick-theme-light"
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
                            :name="positive.refer_to ? 'check-circle' : 'hourglass-half'"
                            class="w-4 h-4 mr-1"
                            :class="{
                                'text-bitter-theme-light': positive.refer_to,
                                'text-thick-theme-light': !positive.refer_to,
                            }"
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
                        class="inline-flex items-center text-blue-300 px-3 py-2"
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
                    :class="{'text-bitter-theme-light': ud(positive) === 'no'}"
                >
                    {{ ud(positive) }}
                </td>
                <td
                    class="border-t px-3 py-2 whitespace-normal"
                    :class="{'text-bitter-theme-light': symptom(positive) === 'asymptomatic'}"
                >
                    {{ symptom(positive) }}
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
                    {{ positive.decision_remark }}
                </td>
                <td
                    class="border-t px-3 py-2"
                    :class="{'text-bitter-theme-light': positive.refer_to}"
                >
                    {{ positive.refer_to ?? 'ยังไม่ตัดสินใจ' }}
                </td>
                <td class="border-t px-3 py-2">
                    <Dropdown>
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
                                    @click="positive.refer_to = referTo"
                                    class="block w-full px-4 py-2 text-left hover:text-bitter-theme-light hover:bg-white transition-colors duration-200 ease-in-out"
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
                        class="inline-flex items-center text-blue-300 mr-2"
                        @click="callPositive(positive)"
                    >
                        <Icon
                            name="phone-square"
                            class="w-4 h-4"
                        />
                        {{ positive.tel_no }}
                    </button>
                    <span class="italic mr-2">{{ positive.patient_type }}</span>
                </p>
                <Dropdown>
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
                                @click="positive.refer_to = referTo"
                                class="block w-full px-4 py-2 text-left hover:text-bitter-theme-light hover:bg-white transition-colors duration-200 ease-in-out"
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
                            :name="positive.refer_to ? 'check-circle' : 'hourglass-half'"
                            class="w-4 h-4 mr-1"
                            :class="{
                                'text-bitter-theme-light': positive.refer_to,
                                'text-thick-theme-light': !positive.refer_to,
                            }"
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
                        <span :class="{'text-bitter-theme-light font-medium': positive.comorbids === 'ไม่มี'}">{{ ud(positive) }}</span>
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
                        <span :class="{'text-bitter-theme-light font-medium': !positive.onset}">{{ symptom(positive) }}</span>
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
            <div class="overflow-y-scroll max-h-96 md:max-h-full">
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
                            <span :class="{'text-bitter-theme-light font-medium': selectedPositive.comorbids === 'ไม่มี'}">{{ ud(selectedPositive) }}</span>
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
                            <span :class="{'text-bitter-theme-light font-medium': !selectedPositive.onset}">{{ symptom(selectedPositive) }}</span>
                        </p>
                    </div>
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
                <FormTextarea
                    class="mt-4"
                    label="remark"
                    v-model="remark"
                    name="remark"
                />
                <div class="mt-2">
                    <label class="form-label">decision</label>
                    <FormRadio
                        class="md:grid grid-cols-2 gap-x-2"
                        v-model="decision"
                        name="decision"
                        :options="referToOptions"
                    />
                </div>
            </div>
        </template>
        <template #footer>
            <button
                class="mt-2 w-full btn btn-bitter"
                @click="makeDecision"
            >
                บันทึก
            </button>
        </template>
    </Modal>
</template>

<script setup>
import FormDatetime from '@/Components/Controls/FormDatetime';
import FormInput from '@/Components/Controls/FormInput';
import FormTextarea from '@/Components/Controls/FormTextarea';
import FormRadio from '@/Components/Controls/FormRadio';
import Icon from '@/Components/Helpers/Icon';
import Modal from '@/Components/Helpers/Modal';
import Dropdown from '@/Components/Helpers/Dropdown';

const props = defineProps({
    positiveCases: { type: Array, required: true },
    dateVisit: { type: String, required: true },
    referToOptions: { type: Array, required: true }
});

const headrows = ref(['Name','Age','HN','Tel','Type','Insurance','U/D','Symptom','Onset','Weight','Remark','Decision']);

const formDateVisit = ref(props.dateVisit);

const search = ref('');

const positives = computed(() => {
    return props.positiveCases.filter(p => p.hn.indexOf(search.value) !== -1 || p.patient_name.indexOf(search.value) !== -1);
});

const symptom = (visit) => {
    if (visit.asymptomatic_symptom) {
        return 'asymptomatic';
    }
    let text = '';
    if (visit.fever
        || visit.cough
        || visit.sore_throat
        || visit.rhinorrhoea
        || visit.sputum
        || visit.fatigue
        || visit.anosmia
        || visit.loss_of_taste
        || visit.myalgia) {
        text = 'URI ';
    }
    if (visit.diarrhea) {
        text += 'Gastroenteritis ';
    }
    if (visit.other_symptoms) {
        text += visit.other_symptoms;
    }

    return text;
};

const ud = (visit) => {
    let text = '';
    if (visit.no_comorbids) {
        return 'no';
    }

    text = ['dm', 'ht', 'dlp', 'obesity'].filter(d => visit[d]).join(' ');

    if (visit.other_comorbids) {
        text += (' ' + visit.other_comorbids);
    }

    return text.trim();
};

const decisionModal = ref(null);
const selectedPositive = ref(null);
const remark = ref(null);
const decision = ref(null);
const callPositive = (positive) => {
    selectedPositive.value = positive;
    remark.value = positive.decision_remark;
    decision.value = positive.refer_to;
    nextTick(() => decisionModal.value.open());
};
const makeDecision = () => {
    selectedPositive.value.refer_to = decision.value;
    selectedPositive.value.decision_remark = remark.value;
    nextTick(() => decisionModal.value.close());
};
</script>

<script>
import Layout from '@/Components/Layouts/Layout';
import { computed, ref } from '@vue/reactivity';
import { nextTick } from '@vue/runtime-core';
export default { layout: Layout };
</script>

<style scoped>
.max-h-90 {
    max-height: 90%;
}
</style>