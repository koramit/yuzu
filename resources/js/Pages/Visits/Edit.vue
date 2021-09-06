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
                @autosave="updateHn"
            />
            <FormInput
                class="mt-2"
                name="name"
                label="ชื่อผู้ป่วย"
                v-model="form.patient.name"
                :readonly="visit.has_patient"
                :error="form.errors.name"
            />
            <FormDatetime
                class="mt-2"
                label="วันที่ตรวจ"
                v-model="form.visit.date_visit"
                name="date_visit"
                :disabled="true"
            />
            <div class="mt-2">
                <label class="form-label">แถว</label>
                <FormRadio
                    class="md:grid grid-cols-2 gap-x-2"
                    v-model="form.patient.track"
                    :error="form.errors.track"
                    name="track"
                    :options="configs.tracks"
                />
                <Error :error="form.errors.track" />
            </div>
            <div class="mt-2">
                <label class="form-label">ลักษณะผู้ป่วย</label>
                <FormRadio
                    class="md:grid grid-cols-2 gap-x-2"
                    v-model="form.patient.mobility"
                    :error="form.errors.mobility"
                    name="mobility"
                    :options="configs.mobilities"
                />
                <Error :error="form.errors.mobility" />
            </div>
            <div class="mt-2">
                <label class="form-label">ประเภทผู้ป่วย</label>
                <FormRadio
                    class="md:grid grid-cols-2 gap-x-2"
                    v-model="form.visit.patient_type"
                    :error="form.errors.patient_type"
                    name="patient_type"
                    :options="configs.patient_types"
                />
                <Error :error="form.errors.patient_type" />
            </div>
            <FormRadio
                label="ประเภทการตรวจ"
                v-model="form.visit.screen_type"
                :error="form.errors.screen_type"
                name="screen_type"
                :options="configs.screen_types"
            />
            <Error :error="form.errors.screen_type" />
            <template v-if="form.visit.screen_type === 'นัดมา swab ซ้ำ'">
                <FormDatetime
                    class="mt-2"
                    label="เคย swab ครั้งที่ 1 เมื่อวันที่"
                    v-model="form.patient.date_swabbed"
                    :error="form.errors.date_swabbed"
                    name="date_swabbed"
                    ref="dateSwabbedInput"
                />
                <button
                    @click="addDays(form.visit.date_visit, dateSwabbedInput, -7)"
                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white disabled:cursor-not-allowed disabled:opacity-50"
                >
                    -7
                </button>
                <button
                    @click="addDays(form.visit.date_visit, dateSwabbedInput, -14)"
                    class="ml-2 text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white disabled:cursor-not-allowed disabled:opacity-50"
                >
                    -14
                </button>
                <FormDatetime
                    class="mt-2"
                    label="เคย swab ครั้งที่ 2 เมื่อวันที่ (ถ้ามี)"
                    v-model="form.patient.date_reswabbed"
                    :error="form.errors.date_reswabbed"
                    name="date_reswabbed"
                    ref="dateReswabbedInput"
                    :disabled="!(form.patient.date_swabbed && true)"
                />
                <button
                    @click="addDays(form.visit.date_visit, dateReswabbedInput, -7)"
                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!(form.patient.date_swabbed && true)"
                >
                    -7
                </button>
            </template>
            <FormReveal
                v-if="visit.has_patient && visit.patient_document_id"
                class="mt-2"
                name="patient_document_id"
                label="เลขประจำตัวประชาชน (จิ้มตาเพื่อแสดง)"
                :secret="visit.patient_document_id"
            />
            <FormInput
                v-if="!visit.patient_document_id"
                class="mt-2"
                name="passport_no"
                label="เลขหนังสือเดินทาง"
                v-model="form.patient.passport_no"
                :error="form.errors.passport_no"
            />
            <div class="mt-2">
                <label class="form-label">สิทธิ์การรักษา</label>
                <FormRadio
                    class="md:grid grid-flow-col grid-cols-3 grid-rows-3 gap-x-2"
                    v-model="form.patient.insurance"
                    :error="form.errors.insurance"
                    name="insurance"
                    :options="configs.insurances"
                    :allow-other="true"
                    ref="insurance"
                />
                <Error :error="form.errors.insurance" />
            </div>
            <FormInput
                class="mt-2"
                name="tel_no"
                type="tel"
                label="หมายเลขโทรศัพท์ของผู้ป่วย"
                v-model="form.patient.tel_no"
                :error="form.errors.tel_no"
            />
            <FormInput
                class="mt-2"
                name="tel_no_alt"
                type="tel"
                label="หมายเลขโทรศัพท์ของผู้ป่วย (สำรอง)"
                v-model="form.patient.tel_no_alt"
                :error="form.errors.tel_no_alt"
            />
            <FormCheckbox
                class="mt-2"
                name="tel_no_confirmed"
                label="ยืนยันหมายเลขโทรศัพท์ของผู้ป่วยแล้ว"
                v-model="form.patient.tel_no_confirmed"
            />
            <Error :error="form.errors.tel_no_confirmed" />
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
            />
            <template v-if="form.patient.no_sap_id">
                <div class="mt-2">
                    <label
                        class="form-label"
                        v-text="'ปฏิบัติงาน'"
                    />
                    <FormRadio
                        class="md:grid grid-flow-col grid-cols-2 grid-rows-3 gap-x-2"
                        name="position"
                        v-model="form.patient.position"
                        :error="form.errors.position"
                        :options="configs.positions"
                        :allow-other="true"
                        ref="position"
                    />
                    <Error :error="form.errors.position" />
                </div>
            </template>
            <template v-else>
                <FormInput
                    class="mt-2"
                    name="sap_id"
                    type="tel"
                    label="sap id หรือรหัสนักศึกษา"
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
            <FormInput
                class="mt-2"
                name="service_location"
                label="หน่วยงาน / หอผู้ป่วย / แผนก ที่ขึ้นปฏิบัติงาน"
                v-model="form.patient.service_location"
            />
            <div class="mt-2">
                <label class="form-label">ความเสี่ยง</label>
                <FormRadio
                    class="md:grid grid-flow-col grid-cols-2 grid-rows-3 gap-x-2"
                    v-model="form.patient.risk"
                    :error="form.errors.risk"
                    name="risk"
                    :options="configs.risk_levels"
                />
            </div>
            <FormDatetime
                class="mt-2"
                label="วันที่สัมผัสที่ประเมินจาก Incident manager"
                v-model="form.patient.date_latest_expose_by_im"
                :error="form.errors.date_latest_expose_by_im"
                name="date_latest_expose_by_im"
            />
        </div>
        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                อาการแสดง
            </h2>
            <FormInput
                class="mt-2"
                label="อุณหภูมิ (℃)"
                type="number"
                name="temperature_celsius"
                v-model="form.patient.temperature_celsius"
                :error="form.errors.temperature_celsius"
            />
            <FormInput
                class="mt-2"
                label="O₂ sat (% RA)"
                type="tel"
                name="o2_sat"
                v-model="form.patient.o2_sat"
                :error="form.errors.o2_sat"
            />
            <div :class="{'mt-2 rounded border-2 border-red-400 p-2': form.errors.symptoms}">
                <FormCheckbox
                    class="mt-4"
                    v-model="form.symptoms.asymptomatic_symptom"
                    label="ไม่มีอาการ"
                    :toggler="true"
                />
                <div v-if="!form.symptoms.asymptomatic_symptom">
                    <FormDatetime
                        class="mt-2"
                        label="วันแรกที่มีอาการ"
                        v-model="form.symptoms.date_symptom_start"
                        :error="form.errors.date_symptom_start"
                        name="date_symptom_start"
                    />
                    <div class="md:grid grid-flow-col grid-cols-2 grid-rows-5 gap-2">
                        <FormCheckbox
                            v-for="(symptom, key) in configs.symptoms"
                            :key="key"
                            class="mt-2"
                            v-model="form.symptoms[symptom.name]"
                            :label="symptom.label"
                        />
                    </div>
                    <FormInput
                        class="mt-2"
                        placeholder="อาการอื่นๆ คือ"
                        name="other_symptoms"
                        v-model="form.symptoms.other_symptoms"
                    />
                </div>
            </div>
            <Error :error="form.errors.symptoms" />
        </div>

        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                ATK
            </h2>
            <FormCheckbox
                class="mt-2"
                name="atk_positive"
                label="ตรวจ Antigen test kit (ATK) ได้ผลบวก"
                v-model="form.exposure.atk_positive"
            />
        </div>

        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                ประวัติเสี่ยง
            </h2>
            <div class="mt-2">
                <label
                    class="form-label"
                    v-text="form.visit.screen_type"
                />
                <FormRadio
                    class="md:grid grid-cols-2 gap-x-2"
                    v-model="form.exposure.evaluation"
                    :error="form.errors.evaluation"
                    name="evaluation"
                    :options="isEmployee ? configs.appointment_evaluations : configs.appointment_evaluations_public"
                    :allow-other="true"
                    v-if="isAppointment"
                    ref="evaluation"
                />
                <FormRadio
                    v-else
                    class="md:grid grid-flow-col grid-cols-2 grid-rows-2 gap-x-2"
                    v-model="form.exposure.evaluation"
                    :error="form.errors.evaluation"
                    name="evaluation"
                    :options="configs.evaluations"
                />
                <Error :error="form.errors.evaluation" />
            </div>
            <template v-if="showExposureForm">
                <FormDatetime
                    class="mt-2"
                    label="วันสุดท้ายที่สัมผัส"
                    v-model="form.exposure.date_latest_expose"
                    :error="form.errors.date_latest_expose"
                    name="date_latest_expose"
                />
                <FormCheckbox
                    class="mt-2"
                    v-model="form.exposure.contact"
                    label="สัมผัสผู้ติดเชื้อยืนยัน"
                />
                <template v-if="form.exposure.contact">
                    <div class="ml-3 pl-2 border-l-2 border-bitter-theme-light">
                        <div class="mt-2">
                            <label class="form-label">ลักษณะการสัมผัส</label>
                            <FormRadio
                                class="md:grid grid-cols-2 gap-x-2"
                                v-model="form.exposure.contact_type"
                                :error="form.errors.contact_type"
                                name="exposure_contact_type"
                                :options="configs.contact_types"
                            />
                            <Error :error="form.errors.contact_type" />
                        </div>
                        <div class="p-2">
                            <button
                                class="m-2 p-2 rounded bg-bitter-theme-light text-white"
                                v-for="choice in [
                                    'นอนห้องเดียวกัน',
                                    'กินข้าวด้วยกัน',
                                    'อยู่บ้านเดียวกัน',
                                    'พูดคุยใกล้ชิดกัน',
                                    'ทำงานห้องเดียวกัน',
                                    'โต๊ะทำงานใกล้กัน',
                                    'นั่งรถคันเดียวกัน',
                                ]"
                                :key="choice"
                                @click="form.exposure.contact_detail = (form.exposure.contact_detail ?? '') + ' ' + choice"
                            >
                                {{ choice }}
                            </button>
                        </div>
                        <FormTextarea
                            placeholder="รายละเอียดการสัมผัส"
                            name="exposure_contact_detail"
                            v-model="form.exposure.contact_detail"
                        />
                    </div>
                </template>
                <FormCheckbox
                    class="mt-4"
                    v-model="form.exposure.hot_spot"
                    label="ไปพื้นที่เสี่ยงตามประกาศของศิริราช"
                />
                <template v-if="form.exposure.hot_spot">
                    <div class="ml-3 pl-2 pt-2 border-l-2 border-bitter-theme-light">
                        <FormTextarea
                            placeholder="ระบุพื้นที่เสี่ยง"
                            name="exposure_hot_spot_detail"
                            v-model="form.exposure.hot_spot_detail"
                            :error="form.errors.hot_spot_detail"
                        />
                    </div>
                </template>
                <Error :error="form.errors.exposure_type" />
            </template>
            <template v-if="form.exposure.evaluation === 'อื่นๆ'">
                <FormTextarea
                    class="mt-2"
                    placeholder="ระบุความเสี่ยงอื่นๆ"
                    name="exposure_other_detail"
                    v-model="form.exposure.other_detail"
                    :error="form.errors.exposure_other_detail"
                />
            </template>
        </div>

        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                โรคประจำตัว
            </h2>
            <div :class="{'mt-2 rounded border-2 border-red-400 p-2': form.errors.comorbids}">
                <FormCheckbox
                    class="mt-2"
                    v-model="form.comorbids.no_comorbids"
                    label="ไม่มี"
                    :toggler="true"
                />
                <div v-if="!form.comorbids.no_comorbids">
                    <FormCheckbox
                        class="mt-2"
                        v-model="form.comorbids.dm"
                        label="เบาหวาน"
                    />
                    <FormCheckbox
                        class="mt-2"
                        v-model="form.comorbids.ht"
                        label="ความดันโลหิตสูง"
                    />
                    <FormCheckbox
                        class="mt-2"
                        v-model="form.comorbids.dlp"
                        label="ไขมันในเลือดสูง"
                    />
                    <FormCheckbox
                        class="mt-2"
                        v-model="form.comorbids.obesity"
                        label="ภาวะอ้วน"
                    />
                    <template v-if="form.comorbids.obesity">
                        <FormInput
                            class="mt-2"
                            label="น้ำหนัก (kg.) ถ้าทราบ"
                            type="number"
                            name="weight"
                            v-model="form.patient.weight"
                            :error="form.errors.weight"
                        />
                        <FormInput
                            class="mt-2"
                            label="ส่วนสูง (cm.) ถ้าทราบ"
                            type="number"
                            name="height"
                            v-model="form.patient.height"
                            :error="form.errors.height"
                        />
                        <FormInput
                            class="mt-2"
                            label="BMI (kg/m²)"
                            type="number"
                            name="BMI"
                            v-model="BMI"
                            :readonly="true"
                        />
                    </template>
                    <FormInput
                        class="mt-2"
                        placeholder="ระบุโรคประจำตัวอื่นๆ"
                        name="other_comorbids"
                        v-model="form.comorbids.other_comorbids"
                    />
                </div>
            </div>
            <Error :error="form.errors.comorbids" />
        </div>

        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                ประวัติการฉีดวัคซีน COVID-19
            </h2>
            <FormCheckbox
                class="mt-2"
                v-model="form.vaccination.unvaccinated"
                label="ไม่เคยฉีด"
                :toggler="true"
            />
            <div v-if="!form.vaccination.unvaccinated">
                <label class="mt-2 form-label">วัคซีน</label>
                <div class="md:grid grid-flow-col grid-cols-2 grid-rows-3 gap-2">
                    <FormCheckbox
                        v-for="(vaccine, key) in configs.vaccines"
                        :key="key"
                        class="mt-2 md:mt-0"
                        v-model="form.vaccination[vaccine]"
                        :label="vaccine"
                    />
                </div>
                <div class="mt-2">
                    <label class="form-label">จำนวนเข็มที่ฉีดแล้ว</label>
                    <FormRadio
                        class="md:grid grid-flow-col grid-cols-2 grid-rows-2 gap-x-2"
                        v-model="form.vaccination.doses"
                        :error="form.errors.doses"
                        name="vaccination_doses"
                        :options="configs.vaccination_doses"
                        :allow-reset="true"
                    />
                </div>
                <FormDatetime
                    class="mt-2"
                    label="วันที่ฉีดล่าสุด"
                    v-model="form.vaccination.date_latest_vacciniated"
                    :error="form.errors.date_latest_vacciniated"
                    name="date_latest_vacciniated"
                />
            </div>
            <Error :error="form.errors.unvaccinated" />
        </div>

        <!-- v-if="$page.props.user.roles.includes('md')" -->
        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                วินิจฉัย
            </h2>
            <div :class="{'mt-2 rounded border-2 border-red-400 p-2': form.errors.diagnosis}">
                <FormCheckbox
                    class="mt-2"
                    v-model="form.diagnosis.no_symptom"
                    label="ไม่มีอาการ"
                    :toggler="true"
                />
                <div v-if="!form.diagnosis.no_symptom">
                    <FormCheckbox
                        class="mt-2"
                        v-model="form.diagnosis.suspected_covid_19"
                        label="Suspected COVID-19 infection"
                    />
                    <FormCheckbox
                        class="mt-2"
                        v-model="form.diagnosis.uri"
                        label="Upper respiratory tract infection (URI)"
                    />
                    <FormCheckbox
                        class="mt-2"
                        v-model="form.diagnosis.suspected_pneumonia"
                        label="Suspected pneumonia"
                    />
                    <FormInput
                        class="mt-2"
                        name="other_diagnosis"
                        placeholder="วินิจฉัยอื่นๆ"
                        v-model="form.diagnosis.other_diagnosis"
                    />
                </div>
            </div>
            <Error :error="form.errors.diagnosis" />
        </div>

        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                การจัดการ
            </h2>
            <template v-if="!visit.swabbed">
                <FormCheckbox
                    class="mt-2"
                    v-model="form.management.np_swab"
                    label="NP swab for PCR test of SARS-CoV-2"
                />
                <Error :error="form.errors.np_swab" />
            </template>
            <label
                v-else
                class="form-label"
            >ได้ทำ swab แล้ว</label>
            <FormRadio
                v-if="form.management.np_swab && $page.props.user.roles.includes('nurse')"
                label="ส่ง swab ที่"
                name="swab_at"
                v-model="form.management.swab_at"
                :options="configs.swab_units"
            />
            <FormTextarea
                class="mt-2"
                label="ส่งตรวจอื่นๆ"
                name="management_other_tests"
                v-model="form.management.other_tests"
            />
            <FormTextarea
                class="mt-2"
                label="Home medication"
                name="home_medication"
                v-model="form.management.home_medication"
            />
        </div>

        <div
            class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12"
            v-if="form.visit.patient_type && !(form.visit.patient_type === 'บุคคลทั่วไป' && form.management.np_swab)"
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
                v-if="!isEmployee"
                class="mt-2"
                v-model="form.recommendation.choice"
                :error="form.errors.recommendation_choice"
                name="recommendation_choice"
                :options="configs.public_recommendations"
                :allow-reset="true"
            />

            <template v-if="form.recommendation.choice == 13">
                <FormDatetime
                    label="กักตัวถึงวันที่"
                    v-model="form.recommendation.date_isolation_end"
                    :error="form.errors.date_isolation_end"
                    name="date_isolation_end"
                    ref="dateIsolationEndInput"
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
                    label="นัดทำ swab"
                    v-model="form.recommendation.date_reswab"
                    :error="form.errors.date_reswab"
                    name="date_reswab"
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
                    ref="dateReswabNextInput"
                    :disabled="!(form.recommendation.date_reswab && true)"
                />
                <button
                    @click="addDays(form.recommendation.date_reswab, dateReswabNextInput, 7)"
                    class="text-xs shadow-sm italic px-2 rounded-xl bg-bitter-theme-light text-white disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!(form.recommendation.date_reswab && true)"
                >
                    +7
                </button>
            </template>
        </div>

        <div
            class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12"
            v-if="form.visit.screen_type && form.visit.screen_type !== 'เริ่มตรวจใหม่' && $page.props.user.roles.includes('nurse')"
        >
            <h2 class="font-semibold text-thick-theme-light">
                อาจารย์โรคติดเชื้อเวร
            </h2>
            <FormSelect
                class="mt-2"
                v-model="form.md_name"
                :error="form.errors.md_name"
                name="md_name"
                :options="configs.id_staffs"
            />
            <small
                class="text-md text-thick-theme-light italic"
            >๏ กรณีนัดโดยไม่ต้องพบแพทย์</small>
        </div>

        <!-- note -->
        <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
            <h2 class="font-semibold text-thick-theme-light">
                Note
            </h2>
            <FormTextarea
                class="mt-2"
                placeholder="เพิ่มเติม"
                name="note"
                v-model="form.note"
            />
        </div>

        <div class="mt-4 sm:mt-6 md:mt-12">
            <SpinnerButton
                @click="saveForm"
                class="block w-full mt-2 btn btn-bitter"
                v-if="configs.can.includes('save')"
            >
                บันทึก
            </SpinnerButton>

            <template v-if="!visit.swabbed">
                <SpinnerButton
                    v-if="configs.can.includes('save-exam')"
                    class="block w-full mt-4 btn btn-dark"
                    @click="saveToExam"
                >
                    ส่งตรวจ
                </SpinnerButton>

                <SpinnerButton
                    v-if="configs.can.includes('save-discharge') && ! form.management.np_swab"
                    class="block w-full mt-4 btn btn-dark"
                    @click="saveToDischarge"
                >
                    จำหน่ายไม่ต้อง swab
                </SpinnerButton>

                <SpinnerButton
                    v-if="canSaveToSwab"
                    class="block w-full mt-4 btn btn-dark"
                    @click="saveToSwab"
                >
                    ส่ง swab
                </SpinnerButton>
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
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import Error from '@/Components/Controls/Error';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import Layout from '@/Components/Layouts/Layout';
import { reactive, ref } from '@vue/reactivity';
import { computed, inject, nextTick, watch } from '@vue/runtime-core';
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
        SpinnerButton,
        Error,
    },
    props: {
        visit: { type: Object, required: true },
        formConfigs: { type: Object, required: true },
    },
    setup (props) {
        const saveForm = () => {
            form.patch(window.route('visits.update', props.visit.slug));
        };
        const saveToExam = () => {
            form.patch(window.route('visits.exam-list.store', props.visit.slug));
        };
        const saveToSwab = () => {
            form.patch(window.route('visits.swab-list.store', props.visit.slug));
        };
        const saveToDischarge = () => {
            form.post(window.route('visits.discharge-list.store', props.visit.slug));
        };
        const emitter = inject('emitter');
        emitter.on('action-clicked', (action) => {
            if (action === 'save') {
                nextTick(saveForm);
            } else if (action === 'save-exam') {
                nextTick(saveToExam);
            } else if (action === 'save-swab') {
                nextTick(saveToSwab);
            } else if (action === 'save-discharge') {
                nextTick(saveToDischarge);
            }
        });
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

        const updateHn = () => {
            form.errors.hn = null;

            if (!(form.patient.hn.length === 8 && Number.isInteger(parseInt(form.patient.hn.length)))) {
                return;
            }

            window.axios
                .get(window.route('resources.api.patients.show', form.patient.hn))
                .then(response => {
                    if (response.data.found) {
                        form.patient.name = response.data.full_name;
                        form.patient.tel_no = response.data.tel_no;
                    } else {
                        form.errors.hn = response.data.message;
                        form.patient.name = null;
                    }
                });
        };

        const isEmployee = computed(() => {
            return form.visit.patient_type ? (form.visit.patient_type === 'เจ้าหน้าที่ศิริราช') : false;
        });
        watch (
            () => form.visit.patient_type,
            () => {
                form.recommendation.choice = null;
                form.recommendation.date_isolation_end = null;
                form.recommendation.date_reswab = null;
                form.recommendation.date_reswab_next = null;

                form.patient.no_sap_id = false;
                form.patient.sap_id = null;
                form.patient.position = null;
                form.patient.division = null;
                form.patient.division = null;
                form.patient.risk = null;
            }
        );
        watch (
            () => form.patient.no_sap_id,
            () => {
                form.patient.sap_id = null;
                form.patient.position = null;
                form.patient.division = null;
            }
        );

        const isAppointment = computed(() => {
            return form.visit.screen_type ? (form.visit.screen_type !== 'เริ่มตรวจใหม่') : false;
        });
        const showExposureForm = computed(() => {
            return form.exposure.evaluation && form.exposure.evaluation.indexOf('มี') === 0;
        });
        const toggleSaveToSwab = (show) => {
            if (show) {
                if (props.visit.swabbed) {
                    return;
                }
                if (usePage().props.value.flash.actionMenu.findIndex(action => action.action === 'save-swab') === -1) {
                    usePage().props.value.flash.actionMenu.push({icon: 'share-square', label: 'ส่ง swab', action: 'save-swab', can: true});
                }
            } else {
                let pos = usePage().props.value.flash.actionMenu.findIndex(action => action.action === 'save-swab');
                if (pos !== -1) {
                    usePage().props.value.flash.actionMenu.splice(pos, 1);
                }
            }
        };
        const toggleSaveToDischarge = (show) => {
            if (show) {
                if (usePage().props.value.flash.actionMenu.findIndex(action => action.action === 'save-discharge') === -1) {
                    usePage().props.value.flash.actionMenu.push({icon: 'share-square', label: 'จำหน่ายไม่ต้อง swab', action: 'save-discharge', can: true});
                }
            } else {
                let pos = usePage().props.value.flash.actionMenu.findIndex(action => action.action === 'save-discharge');
                if (pos !== -1) {
                    usePage().props.value.flash.actionMenu.splice(pos, 1);
                }
            }
        };
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
                }

                if (props.visit.status !== 'appointment') {
                    if (val.indexOf('swab') !== -1) {
                        form.management.np_swab = true;
                        toggleSaveToSwab(true);
                    } else {
                        toggleSaveToSwab(false);
                    }
                }

                form.patient.date_swabbed = null;
                form.patient.date_reswabbed = null;
            }
        );

        watch (
            () => form.management.np_swab,
            (val) => {
                if (usePage().props.value.user.roles.includes('md')) {
                    toggleSaveToSwab(val);
                    toggleSaveToDischarge(!val);
                } else if (usePage().props.value.user.roles.includes('nurse')) {
                    if (
                        val
                        && !form.diagnosis.no_symptom
                        && !form.diagnosis.suspected_covid_19
                        && !form.diagnosis.uri
                        && !form.diagnosis.suspected_pneumonia
                        && !form.diagnosis.other_diagnosis
                    ) {
                        form.diagnosis.suspected_covid_19 = true;
                    }
                }

                if (val) {
                    if (usePage().props.value.user.roles.includes('md')) {
                        form.management.swab_at = 'SCG';
                    } else if (isAppointment.value && isEmployee.value) {
                        form.management.swab_at = 'Sky Walk';
                    } else {
                        form.management.swab_at = 'SCG';
                    }

                    form.recommendation.choice = null;
                } else {
                    form.management.swab_at = null;
                }
            },
        );

        watch (
            () => form.patient.date_swabbed,
            (val) => {
                if (! val) {
                    nextTick(() => dateReswabbedInput.value.clear());
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

        const evaluation = ref(null);
        watch (
            () => form.exposure.evaluation,
            (val) => {
                if (val !== 'other') {
                    return;
                }

                selectOther.placeholder = 'ระบุอื่นๆ';
                selectOther.configs = 'appointment_evaluations';
                selectOther.input = evaluation.value;
                selectOtherInput.value.open();
            }
        );
        if (form.exposure.evaluation && !configs.appointment_evaluations.includes(form.exposure.evaluation)) {
            configs.appointment_evaluations.push(form.exposure.evaluation);
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
            }
        );

        const sapIdUpdated = () => {
            if (!((form.patient.sap_id.length === 8 || form.patient.sap_id.length === 7) && Number.isInteger(parseInt(form.patient.sap_id)))) {
                return;
            }

            window.axios
                .get(window.route('resources.api.employees.show', form.patient.sap_id))
                .then(response => {
                    if (response.data['found']) {
                        form.patient.position = response.data['position'];
                        form.patient.division = response.data['division'];
                    } else {
                        form.patient.position = null;
                        form.patient.division = null;
                        form.errors.sap_id = 'ไม่มี ID นี้ในระบบ';
                    }
                }).catch(error => console.log(error));
        };

        watch (
            () => form.symptoms.asymptomatic_symptom,
            (val) => {
                if (val) {
                    form.symptoms.date_symptom_start = null;
                    form.symptoms.fever = false;
                    form.symptoms.cough = false;
                    form.symptoms.sore_throat = false;
                    form.symptoms.rhinorrhoea = false;
                    form.symptoms.sputum = false;
                    form.symptoms.fatigue = false;
                    form.symptoms.anosmia = false;
                    form.symptoms.loss_of_taste = false;
                    form.symptoms.myalgia = false;
                    form.symptoms.diarrhea = false;
                    form.symptoms.other_symptoms = null;
                }
            }
        );

        watch (
            () => form.comorbids.no_comorbids,
            (val) => {
                if (val) {
                    form.comorbids.dm = false;
                    form.comorbids.ht = false;
                    form.comorbids.dlp = false;
                    form.comorbids.obesity = false;
                    form.comorbids.other_comorbids = null;
                }
            }
        );

        watch (
            () => form.vaccination.unvaccinated,
            (val) => {
                if (val) {
                    form.vaccination.brand = null;
                    form.vaccination.doses = null;
                    form.vaccination.date_latest_vacciniated = null;
                }
            }
        );

        watch (
            () => form.diagnosis.no_symptom,
            (val) => {
                if (val) {
                    form.diagnosis.suspected_covid_19 = false;
                    form.diagnosis.uri = false;
                    form.diagnosis.suspected_pneumonia = false;
                    form.diagnosis.other_diagnosis = null;
                }
            }
        );

        watch (
            () => form.recommendation.date_reswab,
            (val) => {
                if (! val) {
                    nextTick(() => dateReswabNextInput.value.clear());
                }
            }
        );

        watch (
            () => form.exposure.evaluation,
            () => {
                form.exposure.date_latest_expose = null;
                form.exposure.contact = false;
                form.exposure.contact_type = null;
                form.exposure.contact_name = null;
                form.exposure.hot_spot = false;
                form.exposure.hot_spot_detail = null;
                form.exposure.other_detail = null;
            }
        );

        const canSaveToSwab = computed(() => {
            if (props.visit.status !== 'appointment') {
                if (usePage().props.value.user.roles.includes('nurse')) {
                    return form.visit.screen_type && form.visit.screen_type !== 'เริ่มตรวจใหม่';
                } else if (usePage().props.value.user.roles.includes('md')) {
                    return form.management.np_swab;
                }
            }
            return false;
        });

        const dateIsolationEndInput = ref(null);
        const dateReswabInput = ref(null);
        const dateReswabNextInput = ref(null);
        const dateSwabbedInput = ref(null);
        const dateReswabbedInput = ref(null);

        return {
            form,
            configs,
            selectOther,
            selectOtherInput,
            selectOtherClosed,
            insurance,
            position,
            evaluation,
            sapIdUpdated,
            isAppointment,
            showExposureForm,
            dateIsolationEndInput,
            dateReswabInput,
            dateReswabNextInput,
            dateSwabbedInput,
            dateReswabbedInput,
            isEmployee,
            addDays,
            addDaysAvailable,
            BMI,
            updateHn,
            saveForm,
            saveToExam,
            saveToSwab,
            saveToDischarge,
            canSaveToSwab,
        };
    },
};
</script>