<template>
    <Paper>
        <template #default>
            <div class="px-12 py-6 print-p-0">
                <h2 class="font-semibold pb-2 border-b-2 border-dashed text-xl text-center">
                    แบบเวชระเบียนผู้ป่วยนอก (ARI Clinic)
                </h2>
                <h3 class="font-normal underline mt-4">
                    ข้อมูลผู้ป่วย
                </h3>
                <div class="mt-1 grid grid-rows-3 grid-flow-col gap-2">
                    <DisplayInput
                        v-for="(field, key) in visit"
                        class="mt-2 md:mt-0"
                        :key="key"
                        :label="field"
                        :data="content.visit[field]"
                    />
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div
                        v-for="side in ['left_topics', 'right_topics']"
                        :key="side"
                    >
                        <div
                            v-for="(topic, key) in configs[side].filter(t => content[t])"
                            :key="key"
                        >
                            <h3 class="font-normal underline mt-4">
                                {{ topic }}
                            </h3>
                            <div class="mt-1">
                                <display-input
                                    :data="content[topic]"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mt-2 text-print-size">
                    Electronic Signed by {{ content.md.name }} ว. {{ content.md.pln }} เมื่อ {{ content.md.signed_at }}
                </p>
            </div>
        </template>
        <template #header-right>
            <div class="flex justify-end">
                <svg id="barcode-hn" />
            </div>
        </template>

        <template #footer-left>
            <p class="text-xs">
                1001 - แบบเวชระเบียนผู้ป่วยนอก
            </p>
            <svg id="document-code" />
        </template>
    </Paper>
</template>

<script>
import DisplayInput from '@/Components/Helpers/DisplayInput';
import Plain from '@/Components/Layouts/Plain';
import Paper from '@/Components/Layouts/Paper';
import { computed, onMounted } from '@vue/runtime-core';
import JsBarcode from 'jsbarcode';
export default {
    layout: Plain,
    components: { DisplayInput, Paper },
    props: {
        content: { type: Object, required: true },
        configs: { type: Object, required: true },
    },
    setup (props) {
        const visit = computed(() => {
            return Object.keys(props.content.visit).filter(d => props.content.visit[d]);
        });

        onMounted(() => {
            JsBarcode('#barcode-hn', props.content.visit.hn, {
                format: 'CODE39',
                width: 1,
                height: 25,
                displayValue: true
            });

            JsBarcode('#document-code', 'D1001', {
                format: 'CODE39',
                width: 1,
                height: 25,
                displayValue: false
            });
        });

        return {
            visit
        };
    }
};
</script>