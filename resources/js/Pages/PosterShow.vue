<template>
    <h1>Poster</h1>
    <hr class="border-b border-dashed my-8 md:my-16 lg:my-28">
    <TimeSeriesChart
        class="p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64"
        :config="serviceChartConfig"
        :first-day="firstDay"
        :last-day="lastDay"
    />
    <hr class="border-b border-dashed my-8 md:my-16 lg:my-28">
    <TimeSeriesChart
        class="p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64"
        :config="labResultChartConfig"
        :first-day="firstDay"
        :last-day="lastDay"
    />
    <hr class="border-b border-dashed my-8 md:my-16 lg:my-28">
    <TimeSeriesChart
        class="p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64"
        :config="labPositiveChartConfig"
        :first-day="firstDay"
        :last-day="lastDay"
    />
    <hr class="border-b border-dashed my-8 md:my-16 lg:my-28">
</template>

<script setup>
import { useCheckSessionTimeout } from '@/Functions/useCheckSessionTimeout';
import { useRemoveLoader } from '@/Functions/useRemoveLoader';
import TimeSeriesChart from '../Components/Charts/TimeSeriesChart';
import {reactive} from '@vue/reactivity';

defineProps({
    firstDay: {type: String, required:true},
    lastDay: {type: String, required:true},
});

useCheckSessionTimeout();
useRemoveLoader();

const serviceChartConfig = reactive({
    endpoint: window.route('poster-data.service'),
    title: 'จำนวนผู้มารับบริการ',
    datasets: [
        {name: 'all', label: 'รวม'},
        {name: 'staff', label: 'บุคลากร'},
        {name: 'public', label: 'บุคคลทั่วไป'},
        {name: 'publicSwab', label: 'บุคคลทั่วไป-PCR'},
    ],
});

const labResultChartConfig = reactive({
    endpoint: window.route('poster-data.lab-result'),
    title: 'ผลตรวจ PCR',
    datasets: [
        {name: 'all', label: 'ตรวจ'},
        {name: 'allPos', label: 'รวมพบเชื้อ'},
        {name: 'allNeg', label: 'รวมไม่พบเชื้อ'},
        {name: 'staffPos', label: 'บุคลากรพบเชื้อ'},
        {name: 'staffNeg', label: 'บุคลากรไม่พบเชื้อ'},
        {name: 'publicPos', label: 'บุคคลทั่วไปพบเชื้อ'},
        {name: 'publicNeg', label: 'บุคคลทั่วไปไม่พบเชื้อ'},
    ],
});

const labPositiveChartConfig = reactive({
    endpoint: window.route('poster-data.lab-positive'),
    title: 'ตรวจ PCR ผลพบเชื้อ',
    datasets: [
        {name: 'all', label: 'รวมผลพบเชื้อ'},
        {name: 'allVaccinated', label: 'รวมได้รับวัคซีน'},
        {name: 'allSymptom', label: 'รวมมีอาการ'},
        {name: 'staff', label: 'บุคลากรผลพบเชื้อ'},
        {name: 'staffAsymptom', label: 'บุคลากรไม่มีอาการ'},
        {name: 'public', label: 'บุคคลทั่วไปผลพบเชื้อ'},
        {name: 'publicAsymptom', label: 'บุคคลทั่วไปไม่มีอาการ'},
        {name: 'publicUnvaccinated', label: 'บุคคลทั่วไปไม่ได้รับวัคซีน'},
    ],
});

</script>
