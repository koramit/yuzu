<template>
    <div class="md:flex md:space-x-2">
        <FormDatetime
            class="mb-2 md:w-2/5 xl:w-1/4"
            name="date_visit"
            :label="labelCounter"
            v-model="formDateVisit"
            @autosave="$inertia.visit(route('certifications') + '?date_visit=' + formDateVisit)"
        />
        <FormInput
            class="mb-2 md:w-3/5 xl:w-3/4"
            name="date_visit"
            label="ค้นหาด้วยชื่อ HN Result หรือ ใบรับรองแพทย์"
            v-model="search"
        />
    </div>

    <!-- table  -->
    <div
        class="hidden md:block rounded-md bg-white shadow overflow-x-auto overflow-y-scroll"
        style="max-height: 90%; min-height: 30%;"
    >
        <table class="w-full relative bg-white table-auto">
            <tr class="text-left font-semibold">
                <template
                    v-for="column in headrows"
                    :key="column"
                >
                    <th
                        v-if="column === 'Risk'"
                        class="px-3 pt-4 pb-2 sticky top-0 text-white bg-thick-theme-light"
                    >
                        <Dropdown>
                            <button class="inline-flex items-center">
                                {{ column }}
                                <Icon
                                    class="w-3 h-3 mr-1 "
                                    :class="{'text-dark-theme-light': riskType}"
                                    name="filter"
                                />
                            </button>
                            <template #dropdown>
                                <div class="rounded shadow bg-bitter-theme-light text-white py-2">
                                    <button
                                        v-for="risk_type in ['All', ...riskTypes]"
                                        :key="risk_type"
                                        v-text="risk_type"
                                        class="block whitespace-nowrap w-full px-2 py-1 text-left text-sm hover:text-bitter-theme-light hover:bg-white transition-colors duration-200 ease-in-out"
                                        @click="riskType = risk_type === 'All' ? null : risk_type"
                                    />
                                </div>
                            </template>
                        </Dropdown>
                    </th>
                    <th
                        v-else-if="column === 'Select all'"
                        class="pt-4 pb-2 sticky top-0 text-white bg-thick-theme-light"
                    >
                        <button @click="filteredCertificates.forEach(c => c.checked = ! c.checked)">
                            <Icon
                                name="check-circle"
                                class="w-4 h-4"
                            />
                        </button>
                    </th>
                    <th
                        v-else
                        class="px-3 pt-4 pb-2 sticky top-0 text-white bg-thick-theme-light whitespace-nowrap"
                        :class="{'z-20': column === 'ใบรับรองแพทย์'}"
                        :colspan="column === 'ใบรับรองแพทย์' ? 2:1"
                        v-text="column"
                    />
                </template>
            </tr>
            <tr
                v-for="(certificate, key) in filteredCertificates"
                :key="key"
                class="hover:bg-gray-100 focus-within:bg-gray-100"
            >
                <td class="border-t whitespace-nowrap px-3 py-2">
                    {{ certificate.patient_name }}
                </td>
                <td class="border-t whitespace-nowrap">
                    <button
                        :disabled="!can.certify"
                        @click="certify(certificate)"
                        class="inline-flex items-center text-blue-300 px-3 py-2 disabled:cursor-not-allowed"
                        :class="{
                            'text-alt-theme-light': certificate.result === 'Inconclusive',
                            'text-thick-theme-light': certificate.result === 'Not detected',
                        }"
                    >
                        <Icon
                            name="certificate"
                            class="w-4 h-4 mr-1"
                        />
                        {{ certificate.result }}
                    </button>
                </td>
                <td class="border-t whitespace-nowrap px-3 py-2">
                    {{ certificate.risk }}
                </td>
                <td class="border-t whitespace-nowrap px-3 py-2">
                    {{ certificate.last_exposure_label }}
                </td>
                <td class="border-t">
                    <button
                        class="py-2"
                        @click="certificate.checked = !certificate.checked"
                    >
                        <Icon
                            :name="certificate.checked ? 'check-circle' : 'circle'"
                            class="w-4 h-4"
                            :class="{'text-dark-theme-light': certificate.checked}"
                        />
                    </button>
                </td>
                <td
                    class="border-t px-3 py-2"
                    v-html="highlightDetail(certificate.detail)"
                />
                <td class="border-t px-3 py-2">
                    {{ certificate.note }}
                </td>
                <td
                    class="border-t pl-3 py-2 whitespace-nowrap"
                    :class="{'text-bitter-theme-light': certificate.recommendation}"
                >
                    {{ certificate.recommendation ?? 'ยังไม่มี' }}
                </td>
                <td class="border-t px-3 py-2">
                    <Dropdown v-if="can.certify">
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
                                    v-for="recommend in recommendations"
                                    :key="recommend"
                                    class="block w-full whitespace-nowrap px-4 py-1 text-left text-sm hover:text-bitter-theme-light hover:bg-white transition-colors duration-200 ease-in-out"
                                    v-text="recommend"
                                    @click="recommendFromDropdown(certificate, recommend)"
                                />
                            </div>
                        </template>
                    </Dropdown>
                </td>
                <td
                    class="border-t whitespace-nowrap px-3 py-2"
                    v-text="certificate.date_quarantine_end_label"
                />
                <td
                    class="border-t whitespace-nowrap px-3 py-2"
                    v-text="certificate.date_reswab_label"
                />
            </tr>
            <tr v-if="filteredCertificates.length === 0">
                <td
                    class="border-t px-6 py-4"
                    colspan="10"
                >
                    {{ search ? 'ไม่มีเคสตามคำค้น':'ยังไม่มีเคสสำหรับวันนี้' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- card  -->
    <div class="md:hidden">
        <div
            class="rounded shadow bg-white mb-2 p-4"
            v-for="(certificate, key) in filteredCertificates"
            :key="key"
        >
            <div class="flex justify-between items-center text-sm">
                <p class="flex items-center">
                    <button
                        class="inline-flex items-center text-blue-300 mr-2 disabled:cursor-not-allowed"
                        @click="certify(certificate)"
                        :disabled="!can.certify"
                        :class="{
                            'text-alt-theme-light': certificate.result === 'Inconclusive',
                            'text-thick-theme-light': certificate.result === 'Not detected',
                        }"
                    >
                        <Icon
                            name="certificate"
                            class="w-4 h-4 mr-1"
                        />
                        {{ certificate.result }}
                    </button>
                </p>
                <Dropdown v-if="can.certify">
                    <template #default>
                        <button class="inline-flex items-center">
                            <span
                                class="block mr-1 font-light whitespace-nowrap"
                                :class="{'text-bitter-theme-light': certificate.recommendation}"
                            >{{ certificate.recommendation ?? 'ยังไม่มี' }}</span>
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
                                v-for="recommendation in recommendations"
                                :key="recommendation"
                                @click="recommendFromDropdown(certificate, recommendation)"
                                class="block w-full px-4 py-1 text-left whitespace-nowrap hover:text-bitter-theme-light hover:bg-white transition-colors duration-200 ease-in-out"
                                v-text="recommendation"
                            />
                        </div>
                    </template>
                </Dropdown>
            </div>
            <div class="mt-2 text-lg text-thick-theme-light font-medium">
                <button
                    class="inline-flex items-center py-2"
                    @click="certificate.checked = !certificate.checked"
                >
                    <Icon
                        :name="certificate.checked ? 'check-circle' : 'circle'"
                        class="w-4 h-4 mr-2"
                        :class="{'text-dark-theme-light': certificate.checked}"
                    />
                    {{ certificate.patient_name }}
                </button>
            </div>
            <div class="mt-2 flex space-x-2 text-sm">
                <div class="w-1/2 rounded-md shadow-sm bg-gray-100 p-2">
                    <p
                        class="italic"
                        v-text="'Risk: '"
                    />
                    <button
                        class="flex items-center"
                        @click="riskType = (riskType === certificate.risk) ? null : certificate.risk"
                    >
                        <span class="underline text-blue-400">{{ certificate.risk }}</span>
                        <Icon
                            v-if="riskType === certificate.risk"
                            class="w-3 h-3 text-dark-theme-light"
                            name="filter"
                        />
                    </button>
                </div>
                <div class="w-1/2 rounded-md shadow-sm bg-gray-100 p-2">
                    <p
                        class="italic"
                        v-text="'Last exposure: '"
                    />
                    <span>{{ certificate.last_exposure_label }}</span>
                </div>
            </div>
            <!-- detail -->
            <div
                class="mt-2 rounded-md shadow-sm bg-gray-100 p-2"
                v-if="certificate.detail"
            >
                <div>
                    <span class="italic">Detail: </span>
                    <span v-html="highlightDetail(certificate.detail)" />
                </div>
            </div>
            <!-- note -->
            <div
                class="mt-2 rounded-md shadow-sm bg-gray-100 p-2"
                v-if="certificate.note"
            >
                <p>
                    <span class="italic">Note: </span>
                    {{ certificate.note }}
                </p>
            </div>
        </div>
    </div>

    <!-- certify -->
    <Modal
        ref="certificateModal"
        width-mode="form-cols-1"
    >
        <template #header>
            <div class="font-semibold text-dark-theme-light">
                <p>{{ selectedCertificate.patient_name }}</p>
            </div>
        </template>
        <template #body>
            <div style="max-height: 70vh; overflow-y: scroll;">
                <!-- Risk and last_exposure -->
                <div class="mt-2 flex space-x-2">
                    <div class="w-1/2 rounded-md shadow-sm bg-gray-100 p-2">
                        <p>
                            <span class="italic">Risk: </span>
                            {{ selectedCertificate.risk }}
                        </p>
                    </div>
                    <div class="w-1/2 rounded-md shadow-sm bg-gray-100 p-2">
                        <p>
                            <span class="italic">Last exposure: </span>
                            {{ selectedCertificate.last_exposure_label }}
                        </p>
                    </div>
                </div>
                <!-- detail -->
                <div
                    class="mt-2 rounded-md shadow-sm bg-gray-100 p-2"
                    v-if="selectedCertificate.detail"
                >
                    <div>
                        <span class="italic">Detail: </span>
                        <span v-html="highlightDetail(selectedCertificate.detail)" />
                    </div>
                </div>
                <!-- note -->
                <div
                    class="mt-2 rounded-md shadow-sm bg-gray-100 p-2"
                    v-if="selectedCertificate.note"
                >
                    <p>
                        <span class="italic">Note: </span>
                        {{ selectedCertificate.note }}
                    </p>
                </div>
                <FormDatetime
                    class="mt-4"
                    label="วันที่ตรวจ"
                    name="date_visit"
                    :disabled="true"
                    v-model="formDateVisit"
                />
                <FormDatetime
                    class="mt-2"
                    label="กักตัวถึง"
                    name="date_quarantine_end"
                    v-model="form.date_quarantine_end"
                />
                <FormDatetime
                    class="mt-2"
                    label="นัดสวอบซ้ำ"
                    name="date_reswab"
                    v-model="form.date_reswab"
                />
                <div class="mt-2">
                    <label class="form-label">ใบรับรองแพทย์</label>
                    <FormRadio
                        class="md:grid grid-cols-2 gap-x-2"
                        name="recommendation"
                        :options="recommendations"
                        v-model="form.recommendation"
                    />
                </div>
                <template
                    class="form-label"
                    v-if="selectedCertificate.screen_type !== 'เริ่มตรวจใหม่'"
                >
                    <template v-if="selectedCertificate.medical_records.length">
                        <p
                            class="form-label"
                        >
                            ประวัติภายใน 14 วัน
                        </p>
                        <div
                            v-for="(record, key) in selectedCertificate.medical_records"
                            :key="key"
                        >
                            <p class="text-thick-theme-light underline">
                                วันที่ตรวจ: {{ record.date_visit }} {{ record.result ? ` ผล: ${record.result}`:' ไม่ได้ swab' }}
                            </p>
                            <!-- Risk and last_exposure -->
                            <div class="mt-2 flex space-x-2">
                                <div class="w-1/2 rounded-md shadow-sm bg-gray-100 p-2">
                                    <p>
                                        <span class="italic">Risk: </span>
                                        {{ record.risk }}
                                    </p>
                                </div>
                                <div class="w-1/2 rounded-md shadow-sm bg-gray-100 p-2">
                                    <p>
                                        <span class="italic">Last exposure: </span>
                                        {{ record.last_exposure_label }}
                                    </p>
                                </div>
                            </div>
                            <!-- detail -->
                            <div
                                class="mt-2 rounded-md shadow-sm bg-gray-100 p-2"
                                v-if="record.detail"
                            >
                                <div>
                                    <span class="italic">Detail: </span>
                                    <span v-html="highlightDetail(record.detail)" />
                                </div>
                            </div>
                            <!-- note -->
                            <div
                                class="mt-2 rounded-md shadow-sm bg-gray-100 p-2"
                                v-if="record.note"
                            >
                                <p>
                                    <span class="italic">Note: </span>
                                    {{ record.note }}
                                </p>
                            </div>
                        </div>
                    </template>
                    <p
                        v-else
                        class="form-label"
                    >
                        ไม่มีประวัติใน Yuzu ภายใน 14 วัน
                    </p>
                </template>
            </div>
        </template>
        <template #footer>
            <button
                class="mt-2 w-full btn btn-bitter"
                @click="recommended"
                :disabled="!form.recommendation"
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
import FormRadio from '@/Components/Controls/FormRadio';
import Visit from '@/Components/Forms/Visit';
import Appointment from '@/Components/Forms/Appointment';
import Modal from '@/Components/Helpers/Modal';
import Icon from '@/Components/Helpers/Icon';
import Dropdown from '@/Components/Helpers/Dropdown';

import { computed, nextTick, reactive, ref, watch } from '@vue/runtime-core';
import { usePage } from '@inertiajs/inertia-vue3';

const props = defineProps({
    certificates: { type: Array, required: true },
    dateVisit: { type: String, required: true },
    can: { type: Object, required: true },
});

const createVisitForm = ref(null);
const appointmentForm = ref(null);

const labelCounter = computed(() => {
    return `วันที่ตรวจพบเชื้อ (${props.certificates.filter(p => p.recommendation).length}/${props.certificates.length})`;
});

watch (
    () => usePage().props.value.event.fire,
    (event) => {
        if (! event) {
            return;
        }

        if (usePage().props.value.event.name === 'action-clicked') {
            if (usePage().props.value.event.payload === 'create-visit') {
                nextTick(() => createVisitForm.value.open());
            } else if (usePage().props.value.event.payload === 'create-appointment') {
                nextTick(() => appointmentForm.value.open());
            }
        }
    }
);

const headrows = ref(['Name', 'Result', 'Risk', 'Last exposure', 'Select all','Detail', 'Note', 'ใบรับรองแพทย์', 'กักตัวถึง', 'นัดสวอบซ้ำ']);
const formDateVisit = ref(props.dateVisit);
const search = ref('');
const filteredCertificates = computed(() => {
    return props.certificates.filter(p =>
        p.result.toLowerCase().startsWith(search.value.toLowerCase())
        || (p.recommendation ?? 'ยังไม่มี').toLowerCase().startsWith(search.value.toLowerCase())
        || p.patient_name.toLowerCase().indexOf(search.value) !== -1
    ).filter(p => riskType.value ? (p.risk === riskType.value) : true);
});
const riskType = ref(null);
const riskTypes = computed(() => {
    return Array.from(new Set(props.certificates.map(c => c.risk)));
});
const highlightDetail = (detail) => {
    if (detail.startsWith('สัมผัสเป็นเวลาสั้นๆ')) {
        return detail.replace('สัมผัสเป็นเวลาสั้นๆ', '<span class="italic text-dark-theme-light">สัมผัสเป็นเวลาสั้นๆ</span>');
    } else if (detail.startsWith('สัมผัสใกล้ชิด หรือ household contact')) {
        return detail.replace('สัมผัสใกล้ชิด หรือ household contact', '<span class="italic text-red-400">สัมผัสใกล้ชิด หรือ household contact</span>');
    } else {
        return detail;
    }
};

const recommendFromDropdown = (certificate, recommendation) => {
    let data = [];
    let certificateForm = {};
    props.certificates.forEach(c => {
        if (!(c.checked || c.slug === certificate.slug)) {
            return;
        }
        c.recommendation = recommendation;
        c.checked = false;
        if (recommendation.startsWith('กักตัว')) {
            c.date_quarantine_end = c.date_quarantine_end ?? c.config.date_quarantine_end;
            c.date_quarantine_end_label = c.date_quarantine_end_label ?? c.config.date_quarantine_end_label;
            if (recommendation === 'กักตัวนัดสวอบซ้ำ') {
                c.date_reswab = c.date_reswab ?? c.config.date_reswab;
                c.date_reswab_label = c.date_reswab_label ?? c.config.date_reswab_label;
            } else {
                c.date_reswab = null;
                c.date_reswab_label = null;
            }
        } else if (recommendation === 'ไปทำงานได้') {
            c.date_quarantine_end = null;
            c.date_quarantine_end_label = null;
            c.date_reswab = null;
            c.date_reswab_label = null;
        }
        certificateForm.slug = c.slug;
        certificateForm.recommendation = c.recommendation;
        certificateForm.date_quarantine_end = c.date_quarantine_end;
        certificateForm.date_reswab = c.date_reswab;
        data.push({...certificateForm});
    });

    window.axios
        .patch(window.route('certifications.update'), { certificates: data});
};

const recommendations = ref(['ไปทำงานได้','กักตัว','กักตัวนัดสวอบซ้ำ']);
const certificateModal = ref(null);
const selectedCertificate = ref(null);
const form = reactive({});
const certify = (certificate) => {
    form.recommendation = certificate.recommendation;
    form.date_quarantine_end = certificate.date_quarantine_end;
    form.date_reswab = certificate.date_reswab;
    selectedCertificate.value = certificate;
    certificateModal.value.open();
};
const recommended = () => {
    let ymd = null;
    let mos = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    selectedCertificate.value.recommendation = form.recommendation;

    if (form.recommendation.startsWith('กักตัว')) {
        selectedCertificate.value.date_quarantine_end = form.date_quarantine_end ?? selectedCertificate.value.config.date_quarantine_end;
        ymd = selectedCertificate.value.date_quarantine_end.split('-');
        selectedCertificate.value.date_quarantine_end_label = `${mos[parseInt(ymd[1])]} ${ymd[2]}`;
        if (form.recommendation === 'กักตัวนัดสวอบซ้ำ') {
            selectedCertificate.value.date_reswab = form.date_reswab ?? selectedCertificate.value.config.date_reswab;
            ymd = selectedCertificate.value.date_reswab.split('-');
            selectedCertificate.value.date_reswab_label = `${mos[parseInt(ymd[1])]} ${ymd[2]}`;
        } else {
            selectedCertificate.value.date_reswab = null;
            selectedCertificate.value.date_reswab_label = null;
        }
    } else if (form.recommendation === 'ไปทำงานได้') {
        selectedCertificate.value.date_quarantine_end = null;
        selectedCertificate.value.date_quarantine_end_label = null;
        selectedCertificate.value.date_reswab = null;
        selectedCertificate.value.date_reswab_label = null;
    }
    certificateModal.value.close();

    window.axios
        .patch(window.route('certifications.update'), { certificates: [{...selectedCertificate.value}] });
};
</script>

<script>
import Layout from '@/Components/Layouts/Layout';
export default { layout: Layout };
</script>