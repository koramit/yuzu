<template>
    <div>
        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                ข้อมูลเบื้องต้น
            </h2>
            <FormInput
                class="mt-2"
                name="hn"
                type="tel"
                label="HN"
                v-model="form.patient.hn"
                :error="form.errors.hn"
                :readonly="visit.has_patient"
                @autosave="autosave('patient.hn')"
            />
            <FormInput
                class="mt-2"
                name="name"
                label="ชื่อผู้ป่วย"
                v-model="form.patient.name"
                :error="form.errors.name"
                @autosave="autosave('patient.name')"
            />
            <FormDatetime
                class="mt-2"
                label="วันที่ตรวจ"
                v-model="form.visit.date_visit"
                name="date_latest_expose"
                :disabled="true"
            />
            <div class="mt-2">
                <label class="form-label">ประเภทผู้ป่วย</label>
                <FormRadio
                    class="md:grid grid-cols-2 gap-x-2"
                    v-model="form.visit.patient_type"
                    :error="form.errors.patient_type"
                    name="patient_type"
                    :options="configs.patient_types"
                    @autosave="autosave('patient_type', false)"
                />
            </div>
            <div>
                <label class="form-label">ประเภทการตรวจ</label>
                <FormRadio
                    class="md:grid grid-cols-2 gap-x-2"
                    v-model="form.visit.screen_type"
                    :error="form.errors.screen_type"
                    name="screen_type"
                    :options="configs.screen_types"
                    @autosave="autosave('screen_type', false)"
                />
            </div>
            <FormReveal
                v-if="visit.has_patient"
                class="mt-2"
                name="patient_document_id"
                label="เลขประจำตัวประชาชน"
                :secret="visit.patient_document_id"
            />
            <FormSelect
                class="mt-2"
                label="สิทธิ์การรักษา"
                v-model="form.patient.insurance"
                :error="form.errors.insurance"
                name="insurance"
                :options="configs.insurances"
                :allow-other="true"
                ref="insurance"
                :disabled="isEmployee"
                @autosave="autosave('patient.insurance')"
            />
            <FormInput
                class="mt-2"
                name="tel_no"
                type="tel"
                label="หมายเลขโทรศัพท์ของผู้ป่วย"
                v-model="form.patient.tel_no"
                :error="form.errors.tel_no"
                @autosave="autosave('patient.tel_no')"
            />
            <FormInput
                class="mt-2"
                name="tel_no_alt"
                type="tel"
                label="หมายเลขโทรศัพท์ของผู้ป่วย (สำรอง)"
                v-model="form.patient.tel_no_alt"
                :error="form.errors.tel_no_alt"
                @autosave="autosave('patient.tel_no_alt')"
            />
        </div>
        <div
            class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12"
            v-if="isEmployee"
        >
            <h2 class="font-semibold text-thick-theme-light">
                เจ้าหน้าที่ศิริราช
            </h2>
            <FormCheckbox
                class="mt-2"
                v-model="form.patient.no_sap_id"
                label="ไม่มี ID คณะแพทย์ศิริราช"
                :toggler="true"
                @autosave="autosave('patient.no_sap_id')"
            />
            <template v-if="form.patient.no_sap_id">
                <FormSelect
                    class="mt-2"
                    name="position"
                    label="ปฏิบัติงาน"
                    v-model="form.patient.position"
                    :error="form.errors.position"
                    :options="configs.positions"
                    :allow-other="true"
                    ref="position"
                    @autosave="autosave('patient.position')"
                />
            </template>
            <template v-else>
                <FormInput
                    class="mt-2"
                    name="sap_id"
                    type="tel"
                    label="sap id หรือรหัสนักศึกษาแพทย์"
                    v-model="form.patient.sap_id"
                    :error="form.errors.sap_id"
                    @autosave="sapIdUpdated"
                />
                <FormInput
                    class="mt-2"
                    name="position"
                    label="ปฏิบัติงาน"
                    v-model="form.patient.position"
                    :error="form.errors.position"
                    :readonly="true"
                />
                <FormInput
                    class="mt-2"
                    name="division"
                    label="สังกัด"
                    v-model="form.patient.division"
                    :error="form.errors.division"
                    :readonly="true"
                />
            </template>
            <FormSelect
                class="mt-2"
                label="ความเสี่ยง"
                v-model="form.patient.risk"
                :error="form.errors.risk"
                name="risk"
                :options="configs.risk_levels"
                @autosave="autosave('patient.risk')"
            />
        </div>
        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                อาการ
            </h2>
            <FormInput
                class="mt-2"
                label="อุณหภูมิ (℃)"
                type="number"
                name="temperature_celsius"
                v-model="form.patient.temperature_celsius"
                :error="form.errors.temperature_celsius"
                @autosave="autosave('patient.temperature_celsius')"
            />
            <small
                class="my-2 text-red-700 text-sm"
                v-if="form.errors.symptoms"
            >{{ form.errors.symptoms }}</small>
            <FormCheckbox
                class="mt-2"
                v-model="form.symptoms.asymptomatic_symptom"
                label="ไม่มีอาการ"
                :toggler="true"
                @autosave="autosave('symptoms.asymptomatic_symptom')"
            />
            <div v-if="!form.symptoms.asymptomatic_symptom">
                <FormCheckbox
                    v-for="(symptom, key) in configs.symptoms"
                    :key="key"
                    class="mt-2"
                    v-model="form.symptoms[symptom.name]"
                    :label="symptom.label"
                    @autosave="autosave('symptoms.' + symptom.name)"
                />
                <FormInput
                    class="mt-2"
                    label="O₂ sat (% RA)"
                    type="tel"
                    name="o2_sat"
                    v-model="form.symptoms.o2_sat"
                    :error="form.errors.o2_sat"
                    @autosave="autosave('symptoms.o2_sat')"
                    v-if="form.symptoms.fatigue || form.symptoms.cough"
                />
                <FormInput
                    class="mt-2"
                    placeholder="อาการอื่นๆ คือ"
                    name="other_symptoms"
                    v-model="form.symptoms.other_symptoms"
                    @autosave="autosave('symptoms.other_symptoms')"
                />
            </div>
        </div>

        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                ประวัติเสี่ยง
            </h2>
            <FormSelect
                class="mt-2"
                :label="form.visit.screen_type"
                v-model="form.exposure.evaluation"
                :error="form.errors.evaluation"
                name="evaluation"
                :options="isAppointment ? configs.appointment_evaluations : configs.evaluations"
                @autosave="autosave('exposure.evaluation')"
            />
            <template v-if="showExposureForm">
                <FormDatetime
                    class="mt-2"
                    label="วันสุดท้ายที่สัมผัส"
                    v-model="form.exposure.date_latest_expose"
                    :error="form.errors.date_latest_expose"
                    name="date_latest_expose"
                    @autosave="autosave('exposure.date_latest_expose')"
                />
                <FormCheckbox
                    class="mt-2"
                    v-model="form.exposure.contact"
                    label="สัมผัสผู้ติดเชื้อยืนยัน"
                    @autosave="autosave('exposure.contact')"
                />
                <template v-if="form.exposure.contact">
                    <div class="ml-3 pl-2 border-l-2 border-bitter-theme-light">
                        <FormInput
                            label="ผู้ติดเชื้อ"
                            placeholder="ชื่อ สกุล"
                            name="exposure_contact_name"
                            v-model="form.exposure.contact_name"
                            @autosave="autosave('exposure.contact_name')"
                        />
                        <FormSelect
                            class="mt-2"
                            label="ลักษณะการสัมผัส"
                            v-model="form.exposure.contact_type"
                            :error="form.errors.contact_type"
                            name="exposure_contact_type"
                            :options="configs.contact_types"
                            @autosave="autosave('exposure.contact_type')"
                        />
                    </div>
                </template>
                <FormCheckbox
                    class="mt-4"
                    v-model="form.exposure.hot_spot"
                    label="ไปพื้นที่เสี่ยงตามประกาศของศิริราช"
                    @autosave="autosave('exposure.hot_spot')"
                />
                <template v-if="form.exposure.hot_spot">
                    <div class="ml-3 pl-2 pt-2 border-l-2 border-bitter-theme-light">
                        <FormTextarea
                            placeholder="ระบุพื้นที่เสี่ยงอื่นๆ"
                            name="exposure_hot_spot_detail"
                            v-model="form.exposure.hot_spot_detail"
                            @autosave="autosave('exposure.hot_spot_detail')"
                        />
                    </div>
                </template>
            </template>
            <template v-if="form.exposure.evaluation === 'อื่นๆ'">
                <FormTextarea
                    class="mt-2"
                    placeholder="ระบุความเสี่ยงอื่นๆ"
                    name="exposure_other_detail"
                    v-model="form.exposure.other_detail"
                    @autosave="autosave('exposure.other_detail')"
                />
            </template>
        </div>

        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                โรคประจำตัว
            </h2>
            <small
                class="my-2 text-red-700 text-sm"
                v-if="form.errors.comorbids"
            >{{ form.errors.comorbids }}</small>
            <FormCheckbox
                class="mt-2"
                v-model="form.comorbids.no_comorbids"
                label="ไม่มี"
                :toggler="true"
                @autosave="autosave('comorbids.no_comorbids')"
            />
            <div v-if="!form.comorbids.no_comorbids">
                <FormCheckbox
                    class="mt-2"
                    v-model="form.comorbids.dm"
                    label="เบาหวาน"
                    @autosave="autosave('comorbids.dm')"
                />
                <FormCheckbox
                    class="mt-2"
                    v-model="form.comorbids.ht"
                    label="ความดันโลหิตสูง"
                    @autosave="autosave('comorbids.ht')"
                />
                <FormCheckbox
                    class="mt-2"
                    v-model="form.comorbids.dlp"
                    label="ไขมันในเลือดสูง"
                    @autosave="autosave('comorbids.dlp')"
                />
                <FormCheckbox
                    class="mt-2"
                    v-model="form.comorbids.obesity"
                    label="ภาวะอ้วน"
                    @autosave="autosave('comorbids.obesity')"
                />
                <template v-if="form.comorbids.obesity">
                    <FormInput
                        class="mt-2"
                        label="น้ำหนัก (kg.)"
                        type="number"
                        name="weight"
                        v-model="form.patient.weight"
                        :error="form.errors.weight"
                        @autosave="autosave('patient.weight')"
                    />
                    <FormInput
                        class="mt-2"
                        label="ส่วนสูง (cm.)"
                        type="number"
                        name="height"
                        v-model="form.patient.height"
                        :error="form.errors.height"
                        @autosave="autosave('patient.height')"
                    />
                    <FormInput
                        class="mt-2"
                        label="BMI (kg/m²)"
                        type="number"
                        name="BMI"
                        v-model="BMI"
                        :readonly="true"
                        @autosave="autosave('patient.height')"
                    />
                </template>
                <FormInput
                    class="mt-2"
                    placeholder="ระบุโรคประจำตัวอื่นๆ"
                    name="other_comorbids"
                    v-model="form.comorbids.other_comorbids"
                    @autosave="autosave('comorbids.other_comorbids')"
                />
            </div>
        </div>

        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                ประวัติการฉีดวัคซีน COVID-19
            </h2>
            <small
                class="my-2 text-red-700 text-sm"
                v-if="form.errors.vaccination"
            >{{ form.errors.vaccination }}</small>
            <FormCheckbox
                class="mt-2"
                v-model="form.vaccination.vaccinated"
                label="ไม่เคยฉีด"
                :toggler="true"
                @autosave="autosave('vaccination.vaccinated')"
            />
            <div v-if="!form.vaccination.vaccinated">
                <FormSelect
                    class="mt-2"
                    label="วัคซีน"
                    v-model="form.vaccination.brand"
                    :error="form.errors.brand"
                    name="vaccination_brand"
                    :options="configs.vaccines"
                    @autosave="autosave('vaccination.brand')"
                />
                <div class="mt-2">
                    <label class="form-label">จำนวนเข็มที่ฉีดแล้ว</label>
                    <FormRadio
                        class="md:grid grid-cols-2 gap-x-2"
                        v-model="form.vaccination.doses"
                        :error="form.errors.doses"
                        name="vaccination_doses"
                        :options="configs.vaccination_doses"
                        :allow-reset="true"
                        @autosave="autosave('vaccination.doses')"
                    />
                </div>
                <FormDatetime
                    class="mt-2"
                    label="วันที่ฉีดล่าสุด"
                    v-model="form.vaccination.date_latest_vacciniated"
                    :error="form.errors.date_latest_vacciniated"
                    name="date_latest_vacciniated"
                    @autosave="autosave('vaccination.date_latest_vacciniated')"
                />
            </div>
        </div>

        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                วินิจฉัย
            </h2>
            <small
                class="my-2 text-red-700 text-sm"
                v-if="form.errors.diagnosis"
            >{{ form.errors.diagnosis }}</small>
            <FormCheckbox
                class="mt-2"
                v-model="form.diagnosis.no_symptom"
                label="ไม่มีอาการ"
                :toggler="true"
                @autosave="autosave('diagnosis.no_symptom')"
            />
            <div v-if="!form.diagnosis.no_symptom">
                <FormCheckbox
                    class="mt-2"
                    v-model="form.diagnosis.suspected_covid_19"
                    label="Suspected COVID-19 infection"
                    @autosave="autosave('diagnosis.suspected_covid_19')"
                />
                <FormCheckbox
                    class="mt-2"
                    v-model="form.diagnosis.uri"
                    label="Upper respiratory tract infection (URI)"
                    @autosave="autosave('diagnosis.uri')"
                />
                <FormCheckbox
                    class="mt-2"
                    v-model="form.diagnosis.suspected_pneumonia"
                    label="Suspected pneumonia"
                    @autosave="autosave('diagnosis.suspected_pneumonia')"
                />
                <FormInput
                    class="mt-2"
                    name="other_diagnosis"
                    placeholder="วินิจฉัยอื่นๆ"
                    v-model="form.diagnosis.other_diagnosis"
                    @autosave="autosave('diagnosis.other_diagnosis')"
                />
            </div>
        </div>

        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                การจัดการ
            </h2>
            <FormCheckbox
                class="mt-2"
                v-model="form.management.np_swab"
                label="NP swab for PCR test of SARS-CoV-2"
                @autosave="autosave('management.np_swab')"
            />
            <FormTextarea
                class="mt-2"
                label="ส่งตรวจอื่นๆ"
                name="management_other_tests"
                v-model="form.management.other_tests"
                @autosave="autosave('management.other_tests')"
            />
            <FormTextarea
                class="mt-2"
                label="Home medication"
                name="home_medication"
                v-model="form.management.home_medication"
                @autosave="autosave('management.home_medication')"
            />
        </div>

        <div
            class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12"
            v-if="form.visit.patient_type"
        >
            <h2 class="font-semibold text-thick-theme-light">
                คำแนะนำสำหรับ{{ isEmployee ? 'เจ้าหน้าที่ศิริราช' : 'ผู้ป่วย' }}
            </h2>
            <small
                v-if="isEmployee"
                class="text-bitter-theme-light italic"
            >
                ๏ อ้างอิงจากคำแนะนำของ staff center หรือจากการประเมินผ่าน incident manager
            </small>
            <FormRadio
                class="mt-2"
                v-model="form.recommendation.choice"
                :error="form.errors.recommendation_choice"
                name="recommendation_choice"
                :options="isEmployee ? configs.employee_recommendations : configs.public_recommendations"
                @autosave="autosave('recommendation.choice')"
            />

            <template v-if="form.recommendation.choice == 13">
                <FormDatetime
                    label="กักตัวถึงวันที่"
                    v-model="form.recommendation.date_isolation_end"
                    :error="form.errors.date_isolation_end"
                    name="date_isolation_end"
                    ref="dateIsolationEndInput"
                    @autosave="autosave('recommendation.date_isolation_end')"
                />
                <button
                    @click="addDays(form.exposure.date_latest_expose, dateIsolationEndInput, 14)"
                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!(form.exposure.date_latest_expose && true)"
                >
                    14 วัน
                </button>
                <FormDatetime
                    class="mt-2"
                    label="นัดทำ Reswab"
                    v-model="form.recommendation.date_reswab"
                    :error="form.errors.date_reswab"
                    name="date_reswab"
                    @autosave="autosave('recommendation.date_reswab')"
                    ref="dateReswabInput"
                />
                <button
                    @click="addDays(form.exposure.date_latest_expose, dateReswabInput, 7)"
                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!(form.exposure.date_latest_expose && true)"
                >
                    7 วัน
                </button>
                <button
                    @click="addDays(form.exposure.date_latest_expose, dateReswabInput, 14)"
                    class="ml-2 text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!(form.exposure.date_latest_expose && true)"
                >
                    14 วัน
                </button>
                <FormDatetime
                    class="mt-2"
                    label="นัดทำ Reswab ครั้งที่ 2 (ถ้ามี)"
                    v-model="form.recommendation.date_reswab_next"
                    :error="form.errors.date_reswab_next"
                    name="date_reswab_next"
                    @autosave="autosave('recommendation.date_reswab_next')"
                    ref="dateReswabNextInput"
                    :disabled="!(form.recommendation.date_reswab && true)"
                />
                <button
                    @click="addDays(form.recommendation.date_reswab, dateReswabNextInput, 7)"
                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!(form.recommendation.date_reswab && true)"
                >
                    7 วัน
                </button>
                <button
                    @click="addDays(form.recommendation.date_reswab, dateReswabNextInput, 14)"
                    class="ml-2 text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!(form.recommendation.date_reswab && true)"
                >
                    14 วัน
                </button>
            </template>
        </div>

        <FormSelectOther
            :placeholder="selectOther.placeholder"
            ref="selectOtherInput"
            @closed="selectOtherClosed"
        />
    </div>
</template>

<script>
import FormRadio from '@/Components/Controls/FormRadio';
import FormReveal from '@/Components/Controls/FormReveal';
import FormCheckbox from '@/Components/Controls/FormCheckbox';
import FormDatetime from '@/Components/Controls/FormDatetime';
import FormInput from '@/Components/Controls/FormInput';
import FormSelect from '@/Components/Controls/FormSelect';
import FormSelectOther from '@/Components/Controls/FormSelectOther';
import FormTextarea from '@/Components/Controls/FormTextarea';
import { useForm } from '@inertiajs/inertia-vue3';
import Layout from '@/Components/Layouts/Layout';
import { reactive, ref } from '@vue/reactivity';
import { computed, nextTick, watch } from '@vue/runtime-core';
import lodashGet from 'lodash/get';
export default {
    layout: Layout,
    components: {
        FormRadio,
        FormReveal,
        FormCheckbox,
        FormDatetime,
        FormInput,
        FormSelect,
        FormSelectOther,
        FormTextarea,
    },
    props: {
        visit: { type: Object, required: true },
        formConfigs: { type: Object, required: true },
    },
    setup(props) {
        const configs = reactive({
            ...props.formConfigs,
        });
        const form = useForm({
            ...props.visit.form,
            visit: {
                screen_type: props.visit.screen_type,
                patient_type: props.visit.patient_type,
                date_visit: props.visit.date_visit,
            }
        });

        const isEmployee = computed(() => {
            return form.visit.patient_type === 'เจ้าหน้าที่ศิริราช';
        });
        watch (
            () => form.visit.patient_type,
            () => {
                form.recommendation.choice = null;
                form.recommendation.date_isolation_end = null;
                form.recommendation.date_reswab = null;
                form.recommendation.date_reswab_next = null;
                autosave('recommendation');

                form.patient.no_sap_id = false;
                form.patient.sap_id = null;
                form.patient.position = null;
                form.patient.division = null;
                form.patient.division = null;

                if (isEmployee) {
                    form.patient.insurance = 'ประกันสังคม';
                }

                autosave('patient');
            }
        );
        watch (
            () => form.patient.no_sap_id,
            () => {
                form.patient.sap_id = null;
                form.patient.position = null;
                form.patient.division = null;
                autosave('patient');
            }
        );

        const isAppointment = computed(() => {
            return form.visit.screen_type !== 'เริ่มตรวจใหม่';
        });
        const showExposureForm = computed(() => {
            return form.exposure.evaluation && form.exposure.evaluation.indexOf('มี') === 0;
        });
        watch (
            () => form.visit.screen_type,
            (val, old) => {
                if (val === 'เริ่มตรวจใหม่' || old === 'เริ่มตรวจใหม่') {
                    form.exposure.evaluation = null;
                    form.exposure.date_latest_expose = null;
                    form.exposure.contact = false;
                    form.exposure.contact_type = null;
                    form.exposure.contact_name = null;
                    form.exposure.hot_spot = false;
                    form.exposure.hot_spot_detail = null;
                    form.exposure.other_detail = null;
                    autosave('exposure');
                }
            }
        );

        const addDaysAvailable = computed(() => {
            return form.exposure.date_latest_expose && true;
        });
        const addDays = (ref, target, days) => {
            let thatDay = new Date(ref);
            target.setDate(thatDay.setDate(thatDay.getDate() + days));
        };

        const autosave = (field, saveForm = true) => {
            nextTick(() => {
                let payload = {};

                if (saveForm) {
                    payload['form->' + (field.split('.').join('->'))] = lodashGet(form, field);
                } else {
                    payload[field] = form.visit[field];
                }
                window.axios.patch(configs.patchEndpoint, payload);
            });
        };

        const selectOtherInput = ref(null);
        const selectOther = reactive({
            placeholder: '',
            configs: '',
            input: '',
        });
        const selectOtherClosed = (val) => {
            if (! val) {
                selectOther.input.setOther('');
                return;
            }

            configs[selectOther.configs].push(val);
            selectOther.input.setOther(val);
        };

        const insurance = ref(null);
        watch (
            () => form.patient.insurance,
            (val) => {
                if (val !== 'other') {
                    return;
                }

                selectOther.placeholder = 'ระบุสิทธิ์อื่นๆ';
                selectOther.configs = 'insurances';
                selectOther.input = insurance.value;
                selectOtherInput.value.open();
            }
        );
        if (form.patient.insurance && !configs.insurances.includes(form.patient.insurance)) {
            configs.insurances.push(form.patient.insurance);
        }

        const position = ref(null);
        watch (
            () => form.patient.position,
            (val) => {
                if (val !== 'other') {
                    return;
                }

                selectOther.placeholder = 'ระบุอื่นๆ';
                selectOther.configs = 'positions';
                selectOther.input = position.value;
                selectOtherInput.value.open();
            }
        );
        if (form.patient.position && !configs.positions.includes(form.patient.position)) {
            configs.positions.push(form.patient.position);
        }

        const BMI = computed(() => {
            if (form.patient.weight && form.patient.height) {
                return (form.patient.weight / form.patient.height / form.patient.height * 10000).toFixed(2);
            }
            return null;
        });

        watch (
            () => form.recommendation.choice,
            () => {
                form.recommendation.date_isolation_end = null;
                form.recommendation.date_reswab = null;
                form.recommendation.date_reswab_next = null;
                autosave('recommendation');
            }
        );

        const sapIdUpdated = () => {
            autosave('patient.sap_id');
            if (!((form.patient.sap_id.length === 8 || form.patient.sap_id.length === 7) && Number.isInteger(parseInt(form.patient.sap_id)))) {
                return;
            }

            window.axios
                .get(window.route('resources.api.employees.show', form.patient.sap_id))
                .then(response => {
                    if (response.data['found']) {
                        form.patient.position = response.data['position'];
                        form.patient.division = response.data['division'];
                        autosave('patient');
                    }
                }).catch(error => console.log(error));
        };

        const dateIsolationEndInput = ref(null);
        const dateReswabInput = ref(null);
        const dateReswabNextInput = ref(null);

        return {
            form,
            autosave,
            configs,
            selectOther,
            selectOtherInput,
            selectOtherClosed,
            insurance,
            position,
            sapIdUpdated,
            isAppointment,
            showExposureForm,
            dateIsolationEndInput,
            dateReswabInput,
            dateReswabNextInput,
            isEmployee,
            addDays,
            addDaysAvailable,
            BMI,
        };
    },
};
</script>