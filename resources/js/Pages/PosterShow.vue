<template>
    <h1>Poster</h1>
    <TimeSeriesChart
        :config="serviceChartConfig"
        :first-day="firstDay"
        :last-day="lastDay"
    />
</template>

<script setup>
import { useCheckSessionTimeout } from '@/Functions/useCheckSessionTimeout';
import { useRemoveLoader } from '@/Functions/useRemoveLoader';
import {reactive} from '@vue/reactivity';
import TimeSeriesChart from '../Components/Charts/TimeSeriesChart';

defineProps({
    firstDay: {type: String, required:true},
    lastDay: {type: String, required:true},
});

useCheckSessionTimeout();
useRemoveLoader();

const serviceChartConfig = reactive({
    endpoint: window.route('poster-data'),
    title: 'จำนวนผู้มารับบริการรายวัน',
    datasets: [
        {name: 'all', label: 'ทั้งหมด'},
        {name: 'staff', label: 'บุคลากร'},
        {name: 'public', label: 'บุคคลทั่วไป'},
        {name: 'publicSwab', label: 'บุคคลทั่วไป-PCR'},
    ],
});

</script>

<style scoped>

</style>
