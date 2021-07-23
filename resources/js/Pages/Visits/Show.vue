<template>
    <div>
        <div class="bg-white rounded shadow-sm p-4 mt-8">
            <h2 class="font-semibold pb-2 border-b-2 border-dashed text-thick-theme-light text-xl flex justify-center items-baseline">
                <p>แบบเวชระเบียนผู้ป่วยนอก</p>
                <a
                    href="#"
                    class="ml-6 text-sm font-normal text-dark-theme-light flex"
                    target="_blank"
                >
                    <icon
                        class="mr-2 h-4 w-4"
                        name="print"
                    />
                    พิมพ์
                </a>
            </h2>
            <h3 class="font-normal underline text-dark-theme-light mt-6">
                ข้อมูลผู้ป่วย
            </h3>
            <div class="mt-2 sm:grid grid-flow-col grid-rows-4 xl:grid-rows-2 gap-2 lg:gap-3 xl:gap-4">
                <DisplayInput
                    v-for="(field, key) in Object.keys(content.visit)"
                    class="mt-2 md:mt-0"
                    :key="key"
                    :label="field"
                    :data="content.visit[field]"
                />
            </div>
            <h3 class="font-normal underline text-dark-theme-light mt-6">
                อาการแสดง
            </h3>
            <div class="mt-2 sm:grid grid-cols-3 gap-x-2 lg:gap-x-3 xl:gap-x-4">
                <DisplayInput
                    v-for="(field, key) in Object.keys(content.symptom_headers)"
                    class="mt-2 md:mt-0"
                    :key="key"
                    :label="field"
                    :data="content.symptom_headers[field]"
                />
            </div>
            <div class="mt-2">
                <DisplayInput
                    class="mt-2 md:mt-0"
                    :data="content.symptoms"
                />
            </div>
            <template
                v-for="(topic, key) in configs.topics"
                :key="key"
            >
                <h3 class="font-normal underline text-dark-theme-light mt-8 md:mt-12">
                    {{ topic }}
                </h3>
                <div class="mt-2">
                    <display-input
                        class="mt-2 md:mt-0"
                        :data="content[topic]"
                    />
                </div>
            </template>
            <!-- <h3 class="font-normal underline text-dark-theme-light mt-6">
                ประวัติเสี่ยง
            </h3>
            <div class="mt-2">
                <DisplayInput
                    class="mt-2 md:mt-0"
                    :data="content.exposure"
                />
            </div>
            <h3 class="font-normal underline text-dark-theme-light mt-6">
                ประวัติการฉีดวัคซีน COVID-19
            </h3>
            <div class="mt-2">
                <DisplayInput
                    class="mt-2 md:mt-0"
                    :data="content.vaccination"
                />
            </div>
            <h3 class="font-normal underline text-dark-theme-light mt-6">
                โรคประจำตัว
            </h3>
            <div class="mt-2">
                <DisplayInput
                    class="mt-2 md:mt-0"
                    :data="content.comorbids"
                />
            </div>
            <h3 class="font-normal underline text-dark-theme-light mt-6">
                วินิจฉัย
            </h3>
            <div class="mt-2">
                <DisplayInput
                    class="mt-2 md:mt-0"
                    :data="content.diagnosis"
                />
            </div> -->
        </div>
    </div>
</template>

<script>
import Layout from '@/Components/Layouts/Layout';
import DisplayInput from '@/Components/Helpers/DisplayInput';
import Icon from '@/Components/Helpers/Icon';
export default {
    layout: Layout,
    components: { DisplayInput, Icon },
    props: {
        content: { type: Object, required: true },
        configs: { type: Object, required: true },
    }
};
</script>