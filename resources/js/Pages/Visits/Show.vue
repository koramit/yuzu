<template>
    <div>
        <div class="bg-white rounded shadow-sm p-4 mt-8">
            <h2 class="font-semibold pb-2 border-b-2 border-dashed text-thick-theme-light text-xl flex justify-center items-baseline">
                <p>แบบเวชระเบียนผู้ป่วยนอก</p>
                <a
                    :href="route('print-opd-card', content.slug)"
                    class="ml-6 text-sm font-normal text-dark-theme-light flex"
                    target="_blank"
                    v-if="can.print_opd_card"
                >
                    <icon
                        class="mr-2 h-4 w-4"
                        name="print"
                    />
                    พิมพ์
                </a>
                <span
                    v-else
                    class="ml-6 text-sm font-normal text-dark-theme-light flex"
                >ยังเขียนไม่เสร็จ</span>
            </h2>
            <h3 class="font-normal underline text-dark-theme-light mt-6">
                ข้อมูลผู้ป่วย
            </h3>
            <div
                class="mt-2 sm:grid grid-flow-col gap-2 lg:gap-3 xl:gap-4"
                :class="{
                    'grid-rows-4 xl:grid-rows-2': visit.length < 9,
                    'grid-rows-3': visit.length === 9,
                    'grid-rows-5 xl:grid-rows-3': visit.length > 9,
                }"
            >
                <DisplayInput
                    v-for="(field, key) in visit"
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
                v-for="(topic, key) in configs.topics.filter(t => content[t])"
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
        </div>
        <template v-if="can.evaluate">
            <div class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12">
                <h2 class="font-semibold text-thick-theme-light">
                    Consultation note
                </h2>
                <FormTextarea
                    class="mt-2"
                    name="consultaion"
                    v-model="form.consultation"
                />
            </div>
            <div class="mt-4 sm:mt-6 md:mt-12">
                <SpinnerButton
                    @click="saveForm"
                    class="block w-full mt-2 btn btn-bitter"
                >
                    บันทึก
                </SpinnerButton>
            </div>
        </template>
    </div>
</template>

<script>
import Layout from '@/Components/Layouts/Layout';
import DisplayInput from '@/Components/Helpers/DisplayInput';
import FormTextarea from '@/Components/Controls/FormTextarea';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import Icon from '@/Components/Helpers/Icon';
import { computed } from '@vue/runtime-core';
import { useForm } from '@inertiajs/inertia-vue3';
export default {
    layout: Layout,
    components: { DisplayInput, Icon, FormTextarea, SpinnerButton },
    props: {
        content: { type: Object, required: true },
        configs: { type: Object, required: true },
        can: { type: Object, required: true },
    },
    setup (props) {
        const visit = computed(() => {
            return Object.keys(props.content.visit).filter(d => props.content.visit[d]);
        });

        const form = useForm({...props.content.evaluation});

        const saveForm = () => {
            form.patch(window.route('visits.evaluate', props.content.slug));
        };

        return {
            visit,
            form,
            saveForm,
        };
    }
};
</script>