<template>
    <div>
        <canvas ref="chartCanvas" />
        <div class="mt-4 md:mt-12 flex items-center md:space-x-4">
            <FormDatetime
                name="start"
                v-model="form.start"
            />
            <Icon
                v-if="fetching"
                name="circle-notch"
                class="mx-2 md:mx-4 w-6 h-6 animate-spin"
            />
            <p
                class="mx-2 md:mx-4 text-center"
                v-else
            >
                ถึง
            </p>

            <FormDatetime
                name="end"
                v-model="form.end"
            />
        </div>
        <div class="mt-4 md:mt-12 grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4">
            <FormCheckbox
                v-for="set in props.config.datasets"
                :key="set.name"
                :toggler="true"
                :label="set.label"
                v-model="toggleDatasets[set.name]"
                @autosave="
                    () => toggleDatasets[set.name]
                        ? addDataset(set.name)
                        : removeDataset(set.name)
                "
            />
        </div>
    </div>
</template>

<script setup>
import FormDatetime from '../Controls/FormDatetime';
import {Chart,LineElement,LineController,CategoryScale,LinearScale,PointElement,Legend,Title,Tooltip} from 'chart.js';
import {onMounted, watch} from '@vue/runtime-core';
import {reactive, ref} from '@vue/reactivity';
import FormCheckbox from '../Controls/FormCheckbox';
import Icon from '../Helpers/Icon';
import debounce from 'lodash/debounce';

const props = defineProps({
    firstDay: {type: String, required:true},
    lastDay: {type: String, required:true},
    config: {type: Object, required:true},
});

const datasetColors = [
    {bg: 'rgb(255, 99, 132, 0.2)', bd: 'rgb(255, 99, 132)'},
    {bg: 'rgb(126, 222, 111, 0.2)', bd: 'rgb(126, 222, 111)'},
    {bg: 'rgb(34,72,199, 0.2)', bd: 'rgb(34,72,199)'},
    {bg: 'rgba(225,214,4,0.2)', bd: 'rgba(225,214,4,0.93)'},
    {bg: 'rgba(126,23,210,0.2)', bd: 'rgb(126,23,210)'},
    {bg: 'rgba(210,135,23,0.2)', bd: 'rgb(210,135,23)'},
    {bg: 'rgba(22,188,210,0.2)', bd: 'rgb(22,188,210)'},
    {bg: 'rgba(250,8,229,0.2)', bd: 'rgb(250,8,229)'},
];

const toggleDatasets = reactive({});
props.config.datasets.forEach(set => toggleDatasets[set.name] = false);

const form = reactive({
    start: props.firstDay,
    end: props.lastDay,
});
watch(
    [() => form.start, () => form.end],
    () => getData(form)
);

const aspect = ref(false);
watch(
    () => aspect.value,
    (val) => {
        chart.options.aspectRatio = val ? 1 : 2;
        chart.resize();
        console.log(chart.options.aspectRatio);
    }
);

Chart.register(LineElement,LineController,CategoryScale,LinearScale,PointElement,Legend,Title,Tooltip);
const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
const chartConfig = {
    type: 'line',
    data: {labels: [], datasets: []},
    options: {
        aspectRatio: vw < 768 ? 1:2,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: {family: 'Krub', size: 14}
                }
            },
            title: {
                display: true,
                text: props.config.title,
                font: {family: 'Krub', size: 18}
            }
        }
    }
};
let chart;
let chartData;
const chartCanvas = ref(null);
const fetching = ref(false);
const getData = debounce((config) => {
    fetching.value = true;
    window.axios
        .post(props.config.endpoint, config)
        .then(res => {
            chartData = {...res.data};
            chart.data.labels = chartData.labels;
            chart.data.datasets.forEach(set => {
                set.data = chartData.datasets[set.name];
                chart.update();
            });
        }).catch(error => console.log(error))
        .finally(() => fetching.value = false);
}, 1000);

onMounted(() => {
    chart = new Chart(
        chartCanvas.value,
        chartConfig
    );

    addDataset(props.config.datasets[0].name);

    getData({});
});

const addDataset = (name) => {
    let index = props.config.datasets.findIndex(set => set.name === name);
    let set = props.config.datasets[index];
    chart.data.datasets.push({
        label: set.label,
        backgroundColor: datasetColors[index].bg,
        borderColor: datasetColors[index].bd,
        tension: 0.1,
        fill: false,
        data: chartData?.datasets[name],
        borderWidth: 1,
        name: set.name,
    });
    toggleDatasets[name] = true;
    chart.update();
};
const removeDataset = (name) => {
    let index = chart.data.datasets.findIndex(set => set.name === name);
    if (index === -1) {
        return;
    }
    chart.data.datasets.splice(index, 1);
    toggleDatasets[name] = false;
    chart.update();
};
</script>
