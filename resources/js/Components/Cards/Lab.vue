<template>
    <!-- card -->
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
                    class="underline"
                    :class="{
                        'text-red-400': visit.result.toLowerCase() === 'detected',
                        'text-yellow-400': visit.result.toLowerCase() === 'inconclusive',
                        'text-gray-700': visit.result.toLowerCase() === 'not found',
                    }"
                    v-if="visit.result"
                >
                    {{ visit.result }}
                    <span
                        v-if="visit.note"
                        class="ml-1 italic no-underline"
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
                    :href="route('home') + '/' + visit.screenshot"
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