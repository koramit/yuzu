<template>
    <Head>
        <title>YUZU Quality Fair 2022</title>
    </Head>
    <h1 class="mt-8 md:mt-16 lg:mt-28 p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64 text-center">
        <span class="block text-dark-theme-light font-semibold text-lg md:text-2xl">
            🍊 YUZU : ระบบสารสนเทศเพื่อบริหารจัดการข้อมูล<br class="hidden md:block lg:hidden">ภายใน <span class="text-bitter-theme-light">ARI คลินิก</span> 🍊
        </span>
        <span class="block mt-4 md:mt-8 text-thick-theme-light italic md:text-xl">ในงาน Mahidol Quality Fair 2022 </span>
    </h1>
    <hr class="border border-dashed border-thick-theme-light my-8 md:my-16 lg:my-28">
    <h2 class="mb-8 md:mb-16 lg:mb-28 p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64 text-lg md:text-2xl text-thick-theme-light font-semibold italic underline">
        สถิติการให้บริการของ <span class="text-bitter-theme-light">ARI คลินิก</span>ตั้งแต่เริ่มใช้งานระบบ <span class="text-dark-theme-light">YUZU</span> 18 สิงหาคม 2564
    </h2>
    <TimeSeriesChart
        class="p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64"
        :config="serviceChartConfig"
        :first-day="firstDay"
        :last-day="lastDay"
    />
    <hr class="border border-dashed border-thick-theme-light my-8 md:my-16 lg:my-28">
    <TimeSeriesChart
        class="p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64"
        :config="weekdayHourServiceChartConfig"
        :first-day="firstDay"
        :last-day="lastDay"
    />
    <hr class="border border-dashed border-thick-theme-light my-8 md:my-16 lg:my-28">
    <TimeSeriesChart
        class="p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64"
        :config="labResultChartConfig"
        :first-day="firstDay"
        :last-day="lastDay"
    />
    <hr class="border border-dashed border-thick-theme-light my-8 md:my-16 lg:my-28">
    <TimeSeriesChart
        class="p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64"
        :config="labResultTotalChartConfig"
        :first-day="firstDay"
        :last-day="lastDay"
    />
    <hr class="border border-dashed border-thick-theme-light my-8 md:my-16 lg:my-28">
    <TimeSeriesChart
        class="p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64"
        :config="labPositiveChartConfig"
        :first-day="firstDay"
        :last-day="lastDay"
    />
    <hr class="border border-dashed border-thick-theme-light my-8 md:my-16 lg:my-28">
    <TimeSeriesChart
        class="p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64"
        :config="labResultVaccinationsChartConfig"
        :first-day="firstDay"
        :last-day="lastDay"
    />
    <hr class="border border-dashed border-thick-theme-light my-8 md:my-16 lg:my-28">
    <h2 class="mb-4 md:mb-8 lg:mb-14 p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64 text-lg md:text-2xl text-thick-theme-light font-semibold italic underline">
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
            <p class="p-2 md:p-4 leading-7">
                ใช้ Siriraj Single Sign-On (SiSO) เพื่อเข้าสู่ระบบ โดยจะเปิดสิทธิ์อัตโนมัติให้กับแพทย์ พยาบาลและเจ้าหน้าที่เวชระเบียนหมุนเวียนปฏิบัติงานที่ ARI คลินิก และก่อนที่จะสามารถทำรายการต่าง ๆ ได้นั้นจะต้องใช้รหัสปฏิบัติงานอีกชั้นหนึ่ง ซึ่งเป็นรหัสที่มีอายุใช้งานได้เพียง 1 สัปดาห์โดยที่ In charge หรือผู้ดูแลระบบสามารถยกเลิกและเปลี่ยนรหัสที่ออกให้ได้ทุกเมื่อ มีการเข้ารหัสเพื่อเก็บข้อมูลส่วนบุคคลของผู้ป่วยและบันทึกการทำรายการข้อมูลสุขภาพของผู้ใช้งานทุกขั้นตอน เป็นไปตามหลักความมั่นคงระบบสารสนเทศ
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
            <p class="p-2 md:p-4 leading-7">
                ใช้งานได้กับทั้งสมาร์ทโฟน แท็บเล็ตและคอมพิวเตอร์ ฟอร์มอิเล็กทรอนิกส์ถูกใช้งานทุกจุดตั้งแต่การคัดกรอง งานเวชระเบียน แพทย์ตรวจ จุดทำสวอบและแพทย์ประเมินหลังทราบผลตรวจ ทำให้ได้ข้อมูลที่ถูกต้อง รวดเร็วและพร้อมที่จะนำข้อมูลไปใช้ในการพัฒนาต่อยอด นอกจากนี้ฟอร์มอิเล็กทรอนิกส์นั้นปรับเปลี่ยนได้ง่าย สามารถตรวจสอบความครบถ้วนก่อนทำการบันทึกได้ ทำให้ตอบโจทย์ด้านเกณฑ์การรักษาที่เปลี่ยนไปตามสถานการณ์
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
            <p class="p-2 md:p-4 leading-7">
                การให้บริการผู้ป่วยมีด้วยกัน 6 สถานีย่อย ระบบจะ Update ข้อมูลบนหน้าจอของผู้ใช้งานอย่างอัตโนมัติ ทำให้แต่ละสถานีสามารถทราบข้อมูลได้อย่าง Realtime จึงทำงานได้สะดวกรวดเร็ว ผู้ปฏิบัติงานแต่ละสถานีไม่ต้องเปลี่ยนหน้าจอไปมา ใช้เพียงหน้าหลักในงานของตนเองเพียงหน้าเดียว ทำให้สามารถเข้าใจระบบได้ในเวลาไม่นานแม้เป็นบุคลากรหมุนเวียนที่ไม่เคยใช้งานมาก่อน
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
            <p class="p-2 md:p-4 leading-7">
                นอกจากเชื่อมต่อกับระบบ SiSO แล้ว ยังดึงข้อมูลผู้ป่วยด้วย HN จากระบบเวชระเบียนกลางทำให้มีความถูกต้องและได้ข้อมูลที่จำเป็นไปใช้งานครบถ้วนไม่เสียเวลากรอกข้อมูลลดเวลาในการคัดกรอง สามารถทำข้อมูลใบส่งตัวไปยังระบบ Hospitel ลดความซ้ำซ้อนของงาน ส่งต่อข้อมูลไปยังระบบ Si COVID-19 Care เพื่อการดูแลผู้ป่วยอย่างต่อเนื่อง เชื่อมผลสวอบจากห้องปฏิบัติการทำให้การประเมินผู้ป่วยทำได้อย่างรวดเร็ว ดึงข้อมูลประวัติการรับวัคซีนอัตโนมัติเพื่อใช้ประกอบการพิจารณาให้การรักษา
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
            <p class="p-2 md:p-4 leading-7">
                สถานการณ์การระบาดของโรค COVID-19 ที่เปลี่ยนแปลงอย่างต่อเนื่องมาพร้อมกับการปรับเกณฑ์การรักษา การที่ระบบจะมีความสามารถในการตอบสนองต่อการเปลี่ยนแปลงได้อย่างรวดเร็วนับเป็นสิ่งที่ท้าทายอย่างยิ่ง นับแต่เริ่มใช้งาน YUZU สามารถปรับตัวตามสถานการณ์ได้ทันเวลา เพิ่มการเชื่อมต่อข้อมูลกับระบบต่าง ๆ และยังต่อยอดในการอำนวยความสะดวก เช่น สามารถแจ้งเตือนแพทย์ทาง LINE application เมื่อผลแล็บออกครบในแต่ละวันทำให้ไม่ต้องคอยเปิดโปรแกรมเพื่อติดตามเอง เป็นต้น
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
            <p class="p-2 md:p-4 leading-7">
                ออกแบบและพัฒนาทุกขั้นตอนโดยบุคลากรศิริราช อายุรแพทย์โรคติดเชื้อทำงานใกล้ชิดกับพยาบาล ARI คลินิกเพื่อวางระบบงานให้มีประสิทธิภาพทั้งยังใช้งานง่าย นักวิชาการคอมพิวเตอร์ภาควิชาอายุรศาสตร์พัฒนาระบบด้วย Open Source Software ที่เชื่อถือได้ ถูกใช้งานอย่างแพร่หลายในระดับโลกและใช้งานได้โดยไม่มีค่าใช้จ่าย ดังนั้น นอกจากจะสามารถเขียนโค้ดโปรแกรมได้รวดเร็วแล้วยังทำให้ YUZU ไม่มีต้นทุนทางด้าน Software อีกด้วย
            </p>
        </div>
    </div>
    <hr class="border border-dashed border-thick-theme-light my-8 md:my-16 lg:my-28">
    <footer class="mb-8 md:mb-16 lg:mb-28 p-4 md:px-10 lg:px-32 xl:px-40 2xl:px-64 text-center">
        <p class="text-sm text-thick-theme-light font-semibold">
            ผู้จัดทำโครงการขอขอบคุณ บุคลากรหมุนเวียนปฏิบัติงาน <span class="text-bitter-theme-light">ARI คลินิก</span> และผู้เข้าชมงานทุกท่าน
            หวังเป็นอย่างยิ่งว่าจะไม่มีข้อมูลของท่านในระบบ <span class="text-dark-theme-light">YUZU</span> 🤗
        </p>
        <p class="mt-4 md:mt-8 text-xs italic">
            สาขาวิชาโรคติดเชื้อและอายุรศาสตร์เขตร้อน / งานการพยาบาลตรวจรักษาผู้ป่วยนอก
        </p>
    </footer>
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
    title: 'ผลตรวจ PCR',
    type: 'line',
    datasets: [
        {name: 'allPos', label: 'รวมพบเชื้อ'},
        {name: 'allNeg', label: 'รวมไม่พบเชื้อ'},
        {name: 'staffPos', label: 'บุคลากรพบเชื้อ'},
        {name: 'staffNeg', label: 'บุคลากรไม่พบเชื้อ'},
        {name: 'publicPos', label: 'บุคคลทั่วไปพบเชื้อ'},
        {name: 'publicNeg', label: 'บุคคลทั่วไปไม่พบเชื้อ'},
        {name: 'all', label: 'รวมตรวจ'},
    ],
});

const labPositiveChartConfig = reactive({
    endpoint: window.route('poster-data.lab-positive'),
    title: 'ผล PCR พบเชื้อต่อผู้ป่วยมีอาการและการได้รับวัคซีน',
    type: 'line',
    datasets: [
        {name: 'all', label: 'รวม'},
        {name: 'allVaccinated', label: 'รวมได้รับวัคซีน'},
        {name: 'allSymptom', label: 'รวมมีอาการ'},
        {name: 'staff', label: 'บุคลากร'},
        {name: 'staffAsymptom', label: 'บุคลากรไม่มีอาการ'},
        {name: 'public', label: 'บุคคลทั่วไป'},
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
        {name: 'staffNeg', label: 'บุคลากรไม่พบเชื้อ'},
        {name: 'publicPos', label: 'บุคคลทั่วไปพบเชื้อ'},
        {name: 'publicNeg', label: 'บุคคลทั่วไปไม่พบเชื้อ'},
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
