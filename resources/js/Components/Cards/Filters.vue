<template>
    <div class="mb-2">
        <button
            v-for="(filter, key) in cardFilters"
            :key="key"
            class="text-sm shadow-sm italic px-2 py-1 rounded-xl mr-2 bg-bitter-theme-light "
            :class="{
                'border-2 border-white text-white': filter.on,
                'text-soft-theme-light': !filter.on,
            }"
            @click="toggle(filter.name)"
        >
            <Icon
                class="ml-1 inline w-2 h-2"
                name="filter"
                v-if="filter.on"
            />
            {{ filter.label }}
        </button>
    </div>
</template>

<script>
import Icon from '@/Components/Helpers/Icon';
import { reactive } from '@vue/reactivity';
export default {
    emits: ['toggle', 'filtered'],
    components: { Icon },
    props: {
        filters: { type: Array, required: true}
    },
    setup(props, context) {
        const cardFilters = reactive([...props.filters]);

        const toggle = (name) => {
            let index = cardFilters.findIndex(filter => filter.name === name);
            cardFilters[index].on = !cardFilters[index].on;
            context.emit('toggle', name);
            if (name === 'staff' && cardFilters[cardFilters.findIndex(filter => filter.name === name)].on) {
                cardFilters[cardFilters.findIndex(filter => filter.name === 'public')].on = false;
                context.emit('filtered', 'public', false);
            } else if (name === 'public' && cardFilters[cardFilters.findIndex(filter => filter.name === name)].on) {
                cardFilters[cardFilters.findIndex(filter => filter.name === 'staff')].on = false;
                context.emit('filtered', 'staff', false);
            } else if (name === 'walk_in' && cardFilters[cardFilters.findIndex(filter => filter.name === name)].on) {
                cardFilters[cardFilters.findIndex(filter => filter.name === 'appointment')].on = false;
                context.emit('filtered', 'appointment', false);
            } else if (name === 'appointment' && cardFilters[cardFilters.findIndex(filter => filter.name === name)].on) {
                cardFilters[cardFilters.findIndex(filter => filter.name === 'walk_in')].on = false;
                context.emit('filtered', 'walk_in', false);
            } else if (name === 'swab_at_scg' && cardFilters[cardFilters.findIndex(filter => filter.name === name)].on) {
                cardFilters[cardFilters.findIndex(filter => filter.name === 'swab_at_sky_walk')].on = false;
                context.emit('filtered', 'swab_at_sky_walk', false);
            } else if (name === 'swab_at_sky_walk' && cardFilters[cardFilters.findIndex(filter => filter.name === name)].on) {
                cardFilters[cardFilters.findIndex(filter => filter.name === 'swab_at_scg')].on = false;
                context.emit('filtered', 'swab_at_scg', false);
            }
        };

        return {
            cardFilters,
            toggle,
        };
    },
};
</script>