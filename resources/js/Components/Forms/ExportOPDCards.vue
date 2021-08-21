<template>
    <Dropdown
        :dropleft="true"
        :auto-close="false"
        class="w-3/4 md:w-1/2"
        ref="exportDropdown"
    >
        <template #default>
            <button class="flex items-center text-bitter-theme-light hover:text-dark-theme-light">
                <Icon
                    name="file-excel"
                    class="w-4 h-4 mr-1"
                />
                <span>Export OPD cards</span>
            </button>
        </template>
        <template #dropdown>
            <div class="rounded shadow p-2 bg-thick-theme-light">
                <label class="form-label text-white">date visit :</label>
                <FormDatetime
                    v-model="date_visit"
                    name="date_visit"
                />
                <a
                    class="mt-4 block w-full btn btn-bitter text-center"
                    @click="closeDropdown"
                    :href="route('export.opd_cards') + '?date_visit=' + date_visit"
                >Export</a>
            </div>
        </template>
    </Dropdown>
</template>

<script>
import Icon from '@/Components/Helpers/Icon';
import Dropdown from '@/Components/Helpers/Dropdown';
import FormDatetime from '@/Components/Controls/FormDatetime';
import { usePage } from '@inertiajs/inertia-vue3';
import { ref } from '@vue/reactivity';
export default {
    components: {Icon, Dropdown, FormDatetime },
    setup() {
        const date_visit = ref(usePage().props.value.app.today);
        const exportDropdown = ref(null);

        const closeDropdown = () => setTimeout(() => exportDropdown.value.close(), 500);
        return {
            date_visit,
            closeDropdown,
            exportDropdown
        };
    },
};
</script>