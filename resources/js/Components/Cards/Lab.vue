<template>
    <!-- lab summary -->
    <div class="my-2">
        <div class="p-2 rounded-md bg-white my-2">
            <p class="text-md font-semibold text-thick-theme-light underline">
                รวม {{ visits.filter(v => v.result !== 'Pending').length }}/{{ visits.length }}
            </p>
            <div class="flex flex-wrap ">
                <span
                    v-for="(result, key) in ['Not detected', 'Inconclusive', 'Detected', 'Pending']"
                    :key="key"
                    class="inline-block my-1 whitespace-nowrap text-sm shadow-sm italic px-2 py-1 rounded-xl mr-2 text-white"
                    :class="{
                        'bg-green-400': result === 'Not detected',
                        'bg-yellow-400': result === 'Inconclusive',
                        'bg-red-400': result === 'Detected',
                        'bg-gray-600': result === 'Pending',
                    }"
                >
                    {{ result }} {{ visits.filter(v => v.result.toLowerCase() === result.toLowerCase() ).length }}
                </span>
            </div>
        </div>
        <div
            class="p-2 rounded-md bg-white my-2"
            v-for="type in ['บุคคลทั่วไป', 'เจ้าหน้าที่ศิริราช']"
            :key="type"
        >
            <p class="text-md font-semibold text-thick-theme-light underline">
                {{ type }} {{ visits.filter(v => v.result !== 'Pending' && v.patient_type === type).length }}/{{ visits.filter(v => v.patient_type === type).length }}
            </p>
            <div class="flex flex-wrap ">
                <span
                    v-for="(result, key) in ['Not detected', 'Inconclusive', 'Detected', 'Pending']"
                    :key="key"
                    class="inline-block my-1 whitespace-nowrap text-sm shadow-sm italic px-2 py-1 rounded-xl mr-2 text-white"
                    :class="{
                        'bg-green-400': result === 'Not detected',
                        'bg-yellow-400': result === 'Inconclusive',
                        'bg-red-400': result === 'Detected',
                        'bg-gray-600': result === 'Pending',
                    }"
                >
                    {{ result }} {{ visits.filter(v => v.patient_type === type && v.result.toLowerCase() === result.toLowerCase() ).length }}
                </span>
            </div>
        </div>
    </div>
    <!-- card -->
    <transition-group
        name="flip-list"
        tag="div"
    >
        <div
            class="rounded bg-white shadow-sm my-1 p-1 flex"
            v-for="(visit, key) in visits"
            :key="key"
        >
            <!-- left detail -->
            <div class="w-3/4">
                <p class="p-1 pb-0 text-thick-theme-light">
                    {{ visit.patient_type ?? 'ยังไม่ระบุประเภท' }}
                    <span
                        :class="{
                            'text-red-400': visit.result.toLowerCase() === 'detected',
                            'text-yellow-400': visit.result.toLowerCase() === 'inconclusive',
                            'text-gray-700': visit.result.toLowerCase() === 'not found',
                        }"
                        v-if="visit.result"
                    >
                        <span class="mr-1 underline">{{ visit.result }}</span>
                        <span
                            v-if="visit.note"
                            class="mr-1 italic"
                        >{{ visit.note }}</span>
                    </span>
                </p>
                <div class="flex items-baseline">
                    <p class="p-1 text-lg pt-0">
                        HN {{ visit.hn }} <br class="md:hidden"> {{ visit.patient_name }}
                    </p>
                </div>
                <p class="px-1 text-xs text-dark-theme-light italic">
                    ดึงแลปเมื่อ {{ visit.updated_at_for_humans }}
                </p>
            </div>
            <!-- right menu -->
            <div class="w-1/4 text-sm p-1 grid justify-items-start">
                <!-- update -->
                <div>
                    <a
                        class="inline-flex text-green-200 justify-start"
                        :href="route('croissant', visit)"
                        target="_blank"
                        v-if="visit.screenshot"
                    >
                        <Icon
                            class="w-4 h-4 mr-1"
                            name="eyes"
                        />
                        <span class="block font-normal text-thick-theme-light">ดูผลแลป</span>
                    </a>
                </div>
            </div>
        </div>
    </transition-group>
</template>

<script>
import Icon from '@/Components/Helpers/Icon';
import { Link } from '@inertiajs/inertia-vue3';
export default {
    emits: ['cancel'],
    components: { Icon, Link },
    props: {
        visits: { type: Array, required: true }
    },
    setup() {

    },
};
</script>

<style scoped>
.flip-list-move {
    transition: transform 0.8s ease;
}
</style>