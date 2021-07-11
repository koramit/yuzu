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
                :readonly="visit.patient !== null"
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
            <FormSelect
                class="mt-2"
                label="สิทธิ์การรักษา"
                v-model="form.patient.insurance"
                :error="form.errors.insurance"
                name="insurance"
                :options="configs.insurances"
                :allow-other="true"
                ref="insurance"
                :disabled="visit.patient_type === 'เจ้าหน้าที่ศิริราช'"
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
            v-if="visit.patient_type === 'เจ้าหน้าที่ศิริราช'"
        >
            <h2 class="font-semibold text-thick-theme-light">
                เจ้าหน้าที่ศิริราช
            </h2>
            <FormInput
                class="mt-2"
                name="sap_id"
                type="tel"
                label="sap id"
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
            <small
                class="my-t text-red-700 text-sm"
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
                    v-if="form.symptoms.fatigue"
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
                :label="visit.screen_type"
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
                class="my-t text-red-700 text-sm"
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
                class="my-t text-red-700 text-sm"
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
                <FormSelect
                    class="mt-2"
                    label="จำนวนเข็มที่ฉีดแล้ว"
                    v-model="form.vaccination.doses"
                    :error="form.errors.doses"
                    name="vaccination_doses"
                    :options="[1, 2, 3]"
                    @autosave="autosave('vaccination.doses')"
                />
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
                class="my-t text-red-700 text-sm"
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

        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                คำแนะนำสำหรับผู้ป่วย
            </h2>
            <template v-if="visit.patient_type === 'บุคคลทั่วไป'">
                <div class="flex my-4">
                    <p class="text-bitter-theme-light">
                        <input
                            class="form-radio"
                            type="radio"
                            name="recommendation_choice"
                            value="11"
                            v-model="form.recommendation.choice"
                        >
                    </p>
                    <p
                        class="px-2 tracking-wide leading-5 italic cursor-pointer"
                        @click="form.recommendation.choice = 11"
                    >
                        ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา
                    </p>
                </div>
                <div class="flex my-4">
                    <p class="text-bitter-theme-light">
                        <input
                            class="form-radio"
                            type="radio"
                            name="recommendation_choice"
                            value="12"
                            v-model="form.recommendation.choice"
                        >
                    </p>
                    <p
                        class="px-2 tracking-wide leading-5 italic cursor-pointer"
                        @click="form.recommendation.choice = 12"
                    >
                        ลางาน 1 - 2 วัน หากอาการดีขึ้น ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา
                    </p>
                </div>
                <div class="flex my-4">
                    <p class="text-bitter-theme-light">
                        <input
                            class="form-radio"
                            type="radio"
                            name="recommendation_choice"
                            value="13"
                            v-model="form.recommendation.choice"
                        >
                    </p>
                    <div class="w-full">
                        <p
                            class="px-2 tracking-wide leading-5 italic cursor-pointer"
                            @click="form.recommendation.choice = 13"
                        >
                            ลางาน กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ 14 วัน
                        </p>
                        <template v-if="form.recommendation.choice == 13">
                            <div class="ml-3 pl-2 border-l-2 border-bitter-theme-light">
                                <FormDatetime
                                    label="กักตัวถึงวันที่"
                                    v-model="form.recommendation.date_isolation_end"
                                    :error="form.errors.date_isolation_end"
                                    name="date_isolation_end"
                                    ref="dateIsolationEndInput"
                                    @autosave="autosave('recommendation.date_isolation_end')"
                                />
                                <button
                                    @click="
                                        $refs.dateIsolationEndInput.setDate(configs.next_14_days);
                                        form.recommendation.date_isolation_end = configs.next_14_days;
                                        autosave('recommendation.date_isolation_end')
                                    "
                                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    14 วันถัดไป
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
                                    @click="
                                        $refs.dateReswabInput.setDate(configs.next_7_days);
                                        form.recommendation.date_reswab = configs.next_7_days;
                                        autosave('recommendation.date_reswab')
                                    "
                                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    7 วันถัดไป
                                </button>
                                <button
                                    @click="
                                        $refs.dateReswabInput.setDate(configs.next_14_days);
                                        form.recommendation.date_reswab = configs.next_14_days;
                                        autosave('recommendation.date_reswab')
                                    "
                                    class="ml-2 text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    14 วันถัดไป
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="flex my-4">
                    <p class="text-bitter-theme-light">
                        <input
                            class="form-radio"
                            type="radio"
                            name="recommendation_choice"
                            value="21"
                            v-model="form.recommendation.choice"
                        >
                    </p>
                    <p
                        class="px-2 tracking-wide leading-5 italic cursor-pointer"
                        @click="form.recommendation.choice = 21"
                    >
                        ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา
                    </p>
                </div>
                <div class="flex my-4">
                    <p class="text-bitter-theme-light">
                        <input
                            class="form-radio"
                            type="radio"
                            name="recommendation_choice"
                            value="22"
                            v-model="form.recommendation.choice"
                        >
                    </p>
                    <div class="w-full">
                        <p
                            class="px-2 tracking-wide leading-5 italic cursor-pointer"
                            @click="form.recommendation.choice = 22"
                        >
                            ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา และ นัด swab ซ้ำ
                        </p>
                        <template v-if="form.recommendation.choice == 22">
                            <div class="ml-3 pl-2 border-l-2 border-bitter-theme-light">
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
                                    @click="
                                        $refs.dateReswabInput.setDate(configs.next_7_days);
                                        form.recommendation.date_reswab = configs.next_7_days;
                                        autosave('recommendation.date_reswab')
                                    "
                                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    7 วันถัดไป
                                </button>
                                <button
                                    @click="
                                        $refs.dateReswabInput.setDate(configs.next_14_days);
                                        form.recommendation.date_reswab = configs.next_14_days;
                                        autosave('recommendation.date_reswab')
                                    "
                                    class="ml-2 text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    14 วันถัดไป
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="flex my-4">
                    <p class="text-bitter-theme-light">
                        <input
                            class="form-radio"
                            type="radio"
                            name="recommendation_choice"
                            value="23"
                            v-model="form.recommendation.choice"
                        >
                    </p>
                    <p
                        class="px-2 tracking-wide leading-5 italic cursor-pointer"
                        @click="form.recommendation.choice = 23"
                    >
                        ลางาน 1 - 2 วัน หากอาการดีขึ้น ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา
                    </p>
                </div>
                <div class="flex my-4">
                    <p class="text-bitter-theme-light">
                        <input
                            class="form-radio"
                            type="radio"
                            name="recommendation_choice"
                            value="24"
                            v-model="form.recommendation.choice"
                        >
                    </p>
                    <div class="w-full">
                        <p
                            class="px-2 tracking-wide leading-5 italic cursor-pointer"
                            @click="form.recommendation.choice = 24"
                        >
                            ลางาน กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ 7 วัน
                        </p>
                        <template v-if="form.recommendation.choice == 24">
                            <div class="ml-3 pl-2 border-l-2 border-bitter-theme-light">
                                <FormDatetime
                                    label="กักตัวถึงวันที่"
                                    v-model="form.recommendation.date_isolation_end"
                                    :error="form.errors.date_isolation_end"
                                    name="date_isolation_end"
                                    ref="dateIsolationEndInput"
                                    @autosave="autosave('recommendation.date_isolation_end')"
                                />
                                <button
                                    @click="
                                        $refs.dateIsolationEndInput.setDate(configs.next_7_days);
                                        form.recommendation.date_isolation_end = configs.next_7_days;
                                        autosave('recommendation.date_isolation_end')
                                    "
                                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    7 วันถัดไป
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
                                    @click="
                                        $refs.dateReswabInput.setDate(configs.next_7_days);
                                        form.recommendation.date_reswab = configs.next_7_days;
                                        autosave('recommendation.date_reswab')
                                    "
                                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    7 วันถัดไป
                                </button>
                                <button
                                    @click="
                                        $refs.dateReswabInput.setDate(configs.next_14_days);
                                        form.recommendation.date_reswab = configs.next_14_days;
                                        autosave('recommendation.date_reswab')
                                    "
                                    class="ml-2 text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    14 วันถัดไป
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="flex my-4">
                    <p class="text-bitter-theme-light">
                        <input
                            class="form-radio"
                            type="radio"
                            name="recommendation_choice"
                            value="25"
                            v-model="form.recommendation.choice"
                        >
                    </p>
                    <div class="w-full">
                        <p
                            class="px-2 tracking-wide leading-5 italic cursor-pointer"
                            @click="form.recommendation.choice = 25"
                        >
                            ลางาน กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นเป็นเวลา 7 - 14 วัน หากผลตรวจที่วันที่ 7 หลังสัมผัสโรคไม่พบเชื้อ ผู้บังคับบัญชาอาจพิจารณาอนุญาตให้กลับมาทำงานได้
                        </p>
                        <template v-if="form.recommendation.choice == 25">
                            <div class="ml-3 pl-2 border-l-2 border-bitter-theme-light">
                                <FormDatetime
                                    label="กักตัวถึงวันที่"
                                    v-model="form.recommendation.date_isolation_end"
                                    :error="form.errors.date_isolation_end"
                                    name="date_isolation_end"
                                    ref="dateIsolationEndInput"
                                    @autosave="autosave('recommendation.date_isolation_end')"
                                />
                                <button
                                    @click="
                                        $refs.dateIsolationEndInput.setDate(configs.next_7_days);
                                        form.recommendation.date_isolation_end = configs.next_7_days;
                                        autosave('recommendation.date_isolation_end')
                                    "
                                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    7 วันถัดไป
                                </button>
                                <button
                                    @click="
                                        $refs.dateIsolationEndInput.setDate(configs.next_14_days);
                                        form.recommendation.date_isolation_end = configs.next_14_days;
                                        autosave('recommendation.date_isolation_end')
                                    "
                                    class="ml-2 text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    14 วันถัดไป
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
                                    @click="
                                        $refs.dateReswabInput.setDate(configs.next_7_days);
                                        form.recommendation.date_reswab = configs.next_7_days;
                                        autosave('recommendation.date_reswab')
                                    "
                                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    7 วันถัดไป
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="flex my-4">
                    <p class="text-bitter-theme-light">
                        <input
                            class="form-radio"
                            type="radio"
                            name="recommendation_choice"
                            value="26"
                            v-model="form.recommendation.choice"
                        >
                    </p>
                    <div class="w-full">
                        <p
                            class="px-2 tracking-wide leading-5 italic cursor-pointer"
                            @click="form.recommendation.choice = 26"
                        >
                            ลางาน กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ 14 วัน
                        </p>
                        <template v-if="form.recommendation.choice == 26">
                            <div class="ml-3 pl-2 border-l-2 border-bitter-theme-light">
                                <FormDatetime
                                    label="กักตัวถึงวันที่"
                                    v-model="form.recommendation.date_isolation_end"
                                    :error="form.errors.date_isolation_end"
                                    name="date_isolation_end"
                                    ref="dateIsolationEndInput"
                                    @autosave="autosave('recommendation.date_isolation_end')"
                                />
                                <button
                                    @click="
                                        $refs.dateIsolationEndInput.setDate(configs.next_14_days);
                                        form.recommendation.date_isolation_end = configs.next_14_days;
                                        autosave('recommendation.date_isolation_end')
                                    "
                                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    14 วันถัดไป
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
                                    @click="
                                        $refs.dateReswabInput.setDate(configs.next_7_days);
                                        form.recommendation.date_reswab = configs.next_7_days;
                                        autosave('recommendation.date_reswab')
                                    "
                                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    7 วันถัดไป
                                </button>
                                <FormDatetime
                                    class="mt-2"
                                    label="นัดทำ Reswab ครั้งที่ถัดไป"
                                    v-model="form.recommendation.date_reswab_next"
                                    :error="form.errors.date_reswab_next"
                                    name="date_reswab_next"
                                    @autosave="autosave('recommendation.date_reswab_next')"
                                    ref="dateReswabNextInput"
                                />
                                <button
                                    @click="
                                        $refs.dateReswabNextInput.setDate(configs.next_14_days);
                                        form.recommendation.date_reswab_next = configs.next_14_days;
                                        autosave('recommendation.date_reswab_next')
                                    "
                                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white"
                                >
                                    14 วันถัดไป
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
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
        const form = useForm({
            ...props.visit.form,
        });

        const isAppointment = computed(() => {
            return props.visit.screen_type !== 'เริ่มตรวจใหม่';
        });

        const showExposureForm = computed(() => {
            return form.exposure.evaluation && form.exposure.evaluation.indexOf('มี') === 0;
        });

        const configs = reactive({
            ...props.formConfigs,
        });

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

        const autosave = (field) => {
            nextTick(() => {
                let payload = {};
                payload['form->' + (field.split('.').join('->'))] = lodashGet(form, field);
                window.axios.patch(configs.patchEndpoint, payload);
            });
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
            if (!(form.patient.sap_id.length === 8 && Number.isInteger(parseInt(form.patient.sap_id)))) {
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
            sapIdUpdated,
            isAppointment,
            showExposureForm,
            dateIsolationEndInput,
            dateReswabInput,
            dateReswabNextInput,
        };
    },
};
</script>