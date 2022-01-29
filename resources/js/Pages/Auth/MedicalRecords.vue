<template>
    <div
        class="bg-white rounded shadow-sm p-4 mb-4 sm:mb-6 md:mb-12"
        v-if="!visits.length"
    >
        <h2 class="font-semibold text-thick-theme-light border-b-2 border-dashed pb-2">
            ยังไม่มีประวัติการตรวจ
        </h2>
    </div>
    <div
        v-else
        class="rounded bg-white shadow-sm my-1 p-1 flex"
        v-for="(visit, key) in visits"
        :key="key"
    >
        <!-- left detail -->
        <div class="w-3/4">
            <p class="p-1 pt-0 text-thick-theme-light">
                วันที่ {{ visit.date_visit }}
            </p>

            <div class="flex items-baseline">
                <p
                    class="p-1 pb-0 text-xl inline-flex justify-start items-center"
                >
                    <Icon
                        class="w-4 h-4 mr-1"
                        :class="{
                            'text-dark-theme-light': !visit.swabbed,
                            'text-bitter-theme-light': visit.swabbed,
                        }"
                        :name="visit.swabbed ? 'vial':'stethoscope'"
                    />
                    <span
                        class="block"
                        :class="{'italic': !visit.swabbed}"
                    >{{ visit.swabbed ? 'ได้ทำ swab' : 'ไม่ได้ทำ swab' }}</span>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import Icon from '@/Components/Helpers/Icon';

defineProps({
    visits: { type: Array, required: true }
});
</script>

<script>
import Layout from '@/Components/Layouts/Layout';
export default { layout: Layout };
</script>