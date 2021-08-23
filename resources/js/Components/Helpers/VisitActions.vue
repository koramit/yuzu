<template>
    <Dropdown
        class="w-full"
        :dropleft="true"
        :auto-close="false"
        @open="fetchActions"
    >
        <template #default>
            <button
                class="flex p-1 justify-start items-center"
            >
                <Icon
                    class="w-4 h-4 mr-1 text-bitter-theme-light"
                    name="history"
                />
                <span class="block font-normal text-thick-theme-light">Timeline</span>
            </button>
        </template>
        <template #dropdown>
            <div class="bg-gray-100 rounded-lg shadow-md p-2 text-sm md:text-base md:p-4 max-h-96 overflow-y-scroll">
                <div
                    class="md:grid md:grid-cols-3 md:gap-2 flex flex-wrap space-x-2 md:flex-none"
                    v-for="(action, key) in actions"
                    :key="key"
                >
                    <p class="text-bitter-theme-light font-semibold">
                        {{ action.time }}
                    </p>
                    <p class="text-thick-theme-light font-semibold">
                        {{ action.action }}
                    </p>
                    <p class="italic text-dark-theme-light">
                        {{ action.user }}
                    </p>
                </div>
            </div>
        </template>
    </Dropdown>
</template>

<script setup>
import Dropdown from '@/Components/Helpers/Dropdown';
import Icon from '@/Components/Helpers/Icon';
import { ref } from '@vue/reactivity';

const props = defineProps({
    slug: { type: String, required: true},
});

const actions = ref([]);

const fetchActions = () => {
    window.axios
        .get(window.route('visits.transactions', props.slug))
        .then(response => actions.value = [...response.data])
        .catch(error => console.log(error));
};

</script>