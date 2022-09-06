<template>
    <div>
        <div :class="{'relative': fetching}">
            <canvas ref="chartCanvas" />
            <div
                class="absolute top-0 left-0 w-full h-full flex items-center justify-center"
                v-if="fetching"
            >
                <img
                    style="width: 150px; height: 150px;"
                    class="floating-x text-bitter-theme-light"
                    :src="route('home') + '/image/inhaler' + (Math.floor(Math.random() * 3) + 1) + '.png'"
                >
            </div>
        </div>
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
        <div
            class="mt-4 md:mt-12 grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4"
            v-if="props.config.datasets.length > 1"
        >
            <div
                v-for="set in props.config.datasets"
                :key="set.name"
                class="p-1 md:p-2 border-bitter-theme-light"
                :class="{'border border-bitter-theme-light rounded': toggleDatasets[set.name] && aggregates[set.name] !== undefined}"
            >
                <FormCheckbox
                    :toggler="true"
                    :label="set.label"
                    v-model="toggleDatasets[set.name]"
                    @autosave="
                        () => toggleDatasets[set.name]
                            ? addDataset(set.name)
                            : removeDataset(set.name)
                    "
                />
                <transition name="slide-fade">
                    <div
                        v-if="toggleDatasets[set.name] && aggregates[set.name] !== undefined"
                        class="p-1 md:p-2 bg-white rounded text-xs md:text-sm grid grid-cols-2 gap-1 md:gap-2"
                    >
                        <p
                            v-for="stat in Object.keys(aggregates[set.name])"
                            :key="set.name + stat"
                            class="text-thick-theme-light"
                        >
                            {{ stat }}: <span class="font-semibold">{{ aggregates[set.name][stat] }}</span>
                        </p>
                    </div>
                </transition>
            </div>
        </div>
        <div
            class="mt-4 md:mt-12 grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4"
            v-if="categories.length"
        >
            <div
                v-for="category in categories"
                :key="category.index"
                class="p-1 md:p-2 bg-dark-theme-light rounded text-white"
            >
                <FormCheckbox
                    :toggler="true"
                    :label="category.label"
                    v-model="category.show"
                    @autosave="toggleCategory(category)"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import FormDatetime from '../Controls/FormDatetime';
import {Chart,ArcElement,LineElement,LineController,BubbleController,DoughnutController,CategoryScale,PolarAreaController,RadarController,LinearScale,RadialLinearScale,PointElement,Legend,Filler,Title,Tooltip} from 'chart.js';
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
    {bg: 'rgb(255, 99, 132, 0.35)', bd: 'rgb(255, 99, 132)'},
    {bg: 'rgb(126, 222, 111, 0.35)', bd: 'rgb(126, 222, 111)'},
    {bg: 'rgb(34,72,199, 0.35)', bd: 'rgb(34,72,199)'},
    {bg: 'rgba(225,214,4,0.35)', bd: 'rgba(225,214,4,0.93)'},
    {bg: 'rgba(126,23,210,0.35)', bd: 'rgb(126,23,210)'},
    {bg: 'rgba(210,135,23,0.35)', bd: 'rgb(210,135,23)'},
    {bg: 'rgba(22,188,210,0.35)', bd: 'rgb(22,188,210)'},
    {bg: 'rgba(250,8,229,0.35)', bd: 'rgb(250,8,229)'},
    {bg: 'rgba(55,47,56,0.35)', bd: 'rgb(55,47,56)'},
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
    }
);

Chart.register(ArcElement,LineElement,LineController,BubbleController,DoughnutController,PolarAreaController,RadarController,CategoryScale,LinearScale,RadialLinearScale,PointElement,Legend,Filler,Title,Tooltip);
const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
const chartConfig = {
    type: props.config.type,
    data: {labels: [], datasets: []},
    options: {
        responsive: true,
        scales: props.config.type === 'polarArea' ? {
            r: {
                pointLabels: {
                    display: true,
                    centerPointLabels: true,
                    font: {family: 'Krub', size: 16}
                }
            }
        } : undefined,
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
const aggregates = ref({});
const categories = ref([]);
const chartCanvas = ref(null);
const fetching = ref(false);
const getData = debounce((config) => {
    fetching.value = true;
    window.axios
        .post(props.config.endpoint, config)
        .then(res => {
            chartData = {...res.data};
            aggregates.value = chartData.aggregates ?? {};
            categories.value = chartData.categories ?? [];
            chart.data.labels = [...chartData.labels];
            chart.data.datasets.forEach(set => {
                set.data = [...chartData.datasets[set.name]];
                if (props.config.type === 'doughnut') {
                    set.backgroundColor = datasetColors.slice(0, chartData?.labels.length+1).map(color => color.bd);
                } else if (props.config.type === 'polarArea') {
                    set.backgroundColor = datasetColors.slice(0, chartData?.labels.length+1).map(color => color.bg);
                }
            });
            chart.update();
        }).catch(error => console.log(error))
        .finally(() => fetching.value = false);
}, 300);

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
    let selectedData = chartData?.datasets[name] ?? [];
    let data;
    if (categories.value.length && selectedData.length) {
        data = categories.value.filter(c => c.show).map(c => selectedData[c.index]);
    } else {
        data = [...selectedData];
    }
    if (props.config.type === 'line') {
        chart.data.datasets.push({
            label: set.label,
            backgroundColor: datasetColors[index].bg,
            borderColor: datasetColors[index].bd,
            tension: 0.2,
            fill: false,
            data: [...data],
            borderWidth: 1,
            name: set.name,
        });
    } else if (props.config.type === 'doughnut') {
        chart.data.datasets.push({
            label: set.label,
            data: [...data],
            backgroundColor: datasetColors.slice(0, chartData?.labels.length+1).map(color => color.bd),
            hoverOffset: 4,
            name: set.name,
        });
    } else if (props.config.type === 'radar') {
        chart.data.datasets.push({
            label: set.label,
            backgroundColor: datasetColors[index].bg,
            borderColor: datasetColors[index].bd,
            pointBackgroundColor: datasetColors[index].bd,
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: datasetColors[index].bd,
            fill: true,
            data: [...data],
            name: set.name,
        });
    } else if (props.config.type === 'polarArea') {
        chart.data.datasets.push({
            label: set.label,
            backgroundColor: datasetColors.slice(0, chartData?.labels.length+1).map(color => color.bg),
            data: [...data],
            name: set.name,
        });
    }
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
const toggleCategory = (category) => {
    if (! category.show) {
        sliceCategory(category.label);
        return;
    }

    let index = 0;
    for(let i = 0; i < categories.value.length; i++) {
        if (categories.value[i].label === category.label) {
            break;
        }
        if (categories.value[i].show) {
            index = index + 1;
        }
    }
    chart.data.labels.splice(index, 0, category.label);
    chart.data.datasets.forEach(set => {
        let data = chartData.datasets[set.name][category.index];
        set.data.splice(index, 0, data);
    });
    chart.update();
};
const sliceCategory = (label) => {
    let index = chart.data.labels.findIndex(l => l === label);
    if (index === -1) {
        return;
    }
    chart.data.labels.splice(index, 1);
    chart.data.datasets.forEach(set => {
        set.data.splice(index, 1);
    });
    chart.update();
};
</script>

<style scoped>
.slide-fade-enter-active {
    transition: all .3s ease;
}

.slide-fade-leave-active {
    transition: all .3s cubic-bezier(1.0, -1.5, 0.8, 1.0);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateY(-20px);
    opacity: 0;
}
</style>
