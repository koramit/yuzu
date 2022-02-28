<template>
    <div
        class="mt-2 rounded-md bg-red-300 p-2 "
        v-if="visitDetected"
    >
        <p class="underline text-white font-semibold">
            ผู้ป่วยมีผล Detected ในการตรวจที่ ARI ภายใน 90 วัน
        </p>

        <p
            class="mt-2 p-2 bg-white text-red-400 italic rounded-md"
        >
            วันที่ตรวจ : {{ visitDetected.date_visit }}
        </p>

        <p
            class="mt-2 p-2 bg-white text-red-400 italic rounded-md"
            v-html="visitDetected.ct.replaceAll(' | ', '<br>')"
        />
    </div>
    <!-- <div
        class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12"
        v-if="records.length"
    >
        <h2 class="font-semibold text-thick-theme-light">
            ประวัติการตรวจที่ ARI ใน 90 วัน ({{ records.length }} {{ records.length === 1 ? 'visit' : 'visits' }})
        </h2>
        <div
            class="mt-2 rounded-md bg-red-300 p-2 "
            v-if="visitDetected"
        >
            <p class="underline text-white font-semibold">
                วันที่ตรวจ : {{ visitDetected.date_visit }} ผล : {{ visitDetected.result }}
            </p>
            <p
                class="mt-2 p-2 bg-white text-red-400 italic rounded-md"
                v-html="visitDetected.ct.replaceAll(' | ', '<br>')"
            />
        </div>

        <div
            v-for="visit in nonDetected"
            :key="visit.slug"
            class="mt-2 rounded-md bg-gray-200 p-2 "
        >
            <p class="underline text-thick-theme-light font-semibold">
                วันที่ตรวจ : {{ visit.date_visit }} ผล : {{ visit.result }}
            </p>
        </div>
    </div> -->
</template>

<script setup>
import { computed } from '@vue/runtime-core';

const props = defineProps({
    records: { type: Array, required: true }
});


const visitDetected = computed(() => {
    let index = props.records.findIndex((visit) => visit.result.toLowerCase() === 'detected');
    return index === -1 ? null : props.records[index];
});

// const nonDetected = computed(() => {
//     let index = props.records.findIndex((visit) => visit.result.toLowerCase() === 'detected');

//     return index === -1 ? props.records : props.records.filter(visit => visit.slug !== props.records[index].slug);
// });

</script>
