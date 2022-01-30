<template>
    <div
        class="rounded bg-white shadow-sm my-1 p-1 flex"
        v-for="(visit, key) in [...visits].sort((a, b) => a.specimen_no > b.specimen_no )"
        :key="key"
    >
        <!-- left detail -->
        <div class="w-3/4">
            <div class="flex items-center">
                <p class="p-1 pb-0 text-thick-theme-light">
                    {{ visit.patient_type }}
                    <span class="mr-1 underline">{{ visit.swab_at }}</span>
                </p>
                <Icon
                    class="ml-2 mr-1 w-4 h-4 text-bitter-theme-light"
                    name="vial"
                />
                <p class="mr-1 text-xl font-semibold text-thick-theme-light">
                    # {{ visit.specimen_no }}
                </p>
            </div>
            <div class="flex items-baseline">
                <p class="p-1 text-lg pt-0">
                    HN {{ visit.hn }} <br class="md:hidden"> {{ visit.patient_name }}
                </p>
            </div>
            <p class="px-1 text-xs text-dark-theme-light italic">
                ส่ง swab เมื่อ {{ visit.enlisted_swab_at_for_humans }}
            </p>
        </div>
        <!-- right menu -->
        <div class="w-1/4 text-sm p-1 grid justify-items-start">
            <button
                class="inline-flex text-alt-theme-light justify-start"
            >
                <Icon
                    class="w-4 h-4 mr-1"
                    name="bullhorn"
                />
                <span class="block font-normal text-thick-theme-light">เรียกคิว</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import Icon from '@/Components/Helpers/Icon';
defineProps({
    visits: { type: Array, required: true }
});
</script>