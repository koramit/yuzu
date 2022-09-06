<template>
    <h1 class="mt-8 md:mt-16 lg:mt-28 p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64 text-center">
        <span class="block text-dark-theme-light font-semibold text-lg md:text-2xl">
            🍊 YUZU : ระบบสารสนเทศเพื่อบริหารจัดการข้อมูล<br class="hidden md:block lg:hidden">ภายใน <span class="text-bitter-theme-light">ARI คลินิก</span> 🍊
        </span>
        <span class="block mt-4 md:mt-8 text-thick-theme-light italic md:text-xl">ในงานมหกรรมคุณภาพ Quality Fair ประจำปี 2565 </span>
    </h1>
    <hr class="border-b border-dashed my-8 md:my-16 lg:my-28">
    <h2 class="mb-8 md:mb-16 lg:mb-28 p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64 text-lg text-thick-theme-light font-semibold italic underline">
        สถิติการให้บริการของ <span class="text-bitter-theme-light">ARI คลินิก</span>ตั้งแต่เริ่มใช้งานระบบ <span class="text-dark-theme-light">YUZU</span> 18 สิงหาคม 2564
    </h2>
    <TimeSeriesChart
        class="p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64"
        :config="serviceChartConfig"
        :first-day="firstDay"
        :last-day="lastDay"
    />
    <hr class="border-b border-dashed my-8 md:my-16 lg:my-28">
    <TimeSeriesChart
        class="p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64"
        :config="weekdayHourServiceChartConfig"
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
        :config="labResultTotalChartConfig"
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
    <TimeSeriesChart
        class="p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64"
        :config="labResultVaccinationsChartConfig"
        :first-day="firstDay"
        :last-day="lastDay"
    />
    <hr class="border-b border-dashed my-8 md:my-16 lg:my-28">
    <h2 class="mb-4 md:mb-8 lg:mb-14 p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64 text-lg text-thick-theme-light font-semibold italic underline">
        รู้จักระบบ <span class="text-dark-theme-light">YUZU</span> 🍊
    </h2>
    <div class="md:grid grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-12 p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64">
        <div class="mt-6 md:mt-0">
            <h3 class="flex items-center font-semibold text-bitter-theme-light italic p-2 md:p-4">
                <span class="block">ความมั่นคงเป็นสำคัญ</span>
                <Icon
                    name="shield-halved"
                    class="ml-1 md:ml-2 h-4 w-4 text-dark-theme-light"
                />
            </h3>
            <p class="p-2 md:p-4 bg-white rounded shadow">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequatur delectus, doloremque ea eum illum ipsa itaque nobis obcaecati odit perspiciatis porro provident quaerat quis soluta temporibus tenetur veniam. Eveniet, placeat?
            </p>
        </div>
        <div class="mt-6 md:mt-0">
            <h3 class="flex items-center font-semibold text-bitter-theme-light italic p-2 md:p-4">
                <span class="block">เวชระเบียนอิเล็กทรอนิกส์</span>
                <Icon
                    name="device-phone"
                    class="ml-1 md:ml-2 h-4 w-4 text-dark-theme-light"
                />
            </h3>
            <p class="p-2 md:p-4 bg-white rounded shadow">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequatur delectus, doloremque ea eum illum ipsa itaque nobis obcaecati odit perspiciatis porro provident quaerat quis soluta temporibus tenetur veniam. Eveniet, placeat?
            </p>
        </div>
        <div class="mt-6 md:mt-0">
            <h3 class="flex items-center font-semibold text-bitter-theme-light italic p-2 md:p-4">
                <span class="block">Realtime application</span>
                <Icon
                    name="sync-alt"
                    class="ml-1 md:ml-2 h-4 w-4 text-dark-theme-light"
                />
            </h3>
            <p class="p-2 md:p-4 bg-white rounded shadow">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequatur delectus, doloremque ea eum illum ipsa itaque nobis obcaecati odit perspiciatis porro provident quaerat quis soluta temporibus tenetur veniam. Eveniet, placeat?
            </p>
        </div>
        <div class="mt-6 md:mt-0">
            <h3 class="flex items-center font-semibold text-bitter-theme-light italic p-2 md:p-4">
                <span class="block">ถูกต้องและต่อเนื่อง</span>
                <Icon
                    name="check-circle"
                    class="ml-1 md:ml-2 h-4 w-4 text-dark-theme-light"
                />
            </h3>
            <p class="p-2 md:p-4 bg-white rounded shadow">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequatur delectus, doloremque ea eum illum ipsa itaque nobis obcaecati odit perspiciatis porro provident quaerat quis soluta temporibus tenetur veniam. Eveniet, placeat?
            </p>
        </div>
        <div class="mt-6 md:mt-0">
            <h3 class="flex items-center font-semibold text-bitter-theme-light italic p-2 md:p-4">
                <span class="block">นวัตกรรมยุค Disruption</span>
                <Icon
                    name="bolt"
                    class="ml-1 md:ml-2 h-4 w-4 text-dark-theme-light"
                />
            </h3>
            <p class="p-2 md:p-4 bg-white rounded shadow">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequatur delectus, doloremque ea eum illum ipsa itaque nobis obcaecati odit perspiciatis porro provident quaerat quis soluta temporibus tenetur veniam. Eveniet, placeat?
            </p>
        </div>
        <div class="mt-6 md:mt-0">
            <h3 class="flex items-center font-semibold text-bitter-theme-light italic p-2 md:p-4">
                <span class="block">ผลิตในศิริราช</span>
                <Icon
                    name="home"
                    class="ml-1 md:ml-2 h-4 w-4 text-dark-theme-light"
                />
            </h3>
            <p class="p-2 md:p-4 bg-white rounded shadow">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequatur delectus, doloremque ea eum illum ipsa itaque nobis obcaecati odit perspiciatis porro provident quaerat quis soluta temporibus tenetur veniam. Eveniet, placeat?
            </p>
        </div>
    </div>
    <hr class="border-b border-dashed my-8 md:my-16 lg:my-28">
</template>

<script setup>
import { useCheckSessionTimeout } from '@/Functions/useCheckSessionTimeout';
import { useRemoveLoader } from '@/Functions/useRemoveLoader';
import TimeSeriesChart from '../Components/Charts/TimeSeriesChart';
import {reactive} from '@vue/reactivity';
import Icon from '../Components/Helpers/Icon';

defineProps({
    firstDay: {type: String, required:true},
    lastDay: {type: String, required:true},
});

useCheckSessionTimeout();
useRemoveLoader();

const serviceChartConfig = reactive({
    endpoint: window.route('poster-data.service'),
    title: 'จำนวนผู้มารับบริการ',
    type: 'line',
    datasets: [
        {name: 'all', label: 'รวม'},
        {name: 'staff', label: 'บุคลากร'},
        {name: 'public', label: 'บุคคลทั่วไป'},
        {name: 'publicSwab', label: 'บุคคลทั่วไป-PCR'},
    ],
});

const labResultChartConfig = reactive({
    endpoint: window.route('poster-data.lab-result'),
    title: 'จำนวนผลตรวจ PCR',
    type: 'line',
    datasets: [
        {name: 'allPos', label: 'รวมพบเชื้อ'},
        {name: 'allNeg', label: 'รวมไม่พบเชื้อ'},
        {name: 'staffPos', label: 'บุคลากรพบเชื้อ'},
        {name: 'staffNeg', label: 'บุคลากรไม่พบเชื้อ'},
        {name: 'publicPos', label: 'บุคคลทั่วไปพบเชื้อ'},
        {name: 'publicNeg', label: 'บุคคลทั่วไปไม่พบเชื้อ'},
        {name: 'all', label: 'ตรวจ'},
    ],
});

const labPositiveChartConfig = reactive({
    endpoint: window.route('poster-data.lab-positive'),
    title: 'ตรวจ PCR ผลพบเชื้อต่อผู้ป่วยมีอาการและการได้รับวัคซีน',
    type: 'line',
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

const labResultTotalChartConfig = reactive({
    endpoint: window.route('poster-data.lab-result-total'),
    title: 'เปรียบเทียบผลตรวจ PCR',
    type: 'doughnut',
    datasets: [
        {name: 'all', label: 'รวม'},
        {name: 'staff', label: 'บุคลากร'},
        {name: 'public', label: 'บุคคลทั่วไป'},
    ],
});

const labResultVaccinationsChartConfig = reactive({
    endpoint: window.route('poster-data.lab-result-vaccination'),
    title: 'ประวัติการได้รับวัคซีน ณ วันที่ตรวจ PCR',
    type: 'radar',
    datasets: [
        {name: 'allPos', label: 'รวมพบเชื้อ'},
        {name: 'allNeg', label: 'รวมไม่พบเชื้อ'},
        {name: 'staffPos', label: 'บุคลากรพบเชื้อ'},
        {name: 'staffNeg', label: 'บุคลากรพบไม่เชื้อ'},
        {name: 'publicPos', label: 'บุคคลทั่วไปพบเชื้อ'},
        {name: 'publicNeg', label: 'บุคคลทั่วไม่ไปพบเชื้อ'},
    ],
});

const weekdayHourServiceChartConfig = reactive({
    endpoint: window.route('poster-data.service-weekday'),
    title: 'ค่าเฉลี่ยผู้มารับบริการในแต่ละวันของสัปดาห์',
    type: 'polarArea',
    datasets: [
        {name: 'all', label: 'รวม'},
    ],
});

</script>
