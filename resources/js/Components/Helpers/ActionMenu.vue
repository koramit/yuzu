<template>
    <div v-if="$page.props.flash.actionMenu.length">
        <div class="mb-4">
            <button
                class="flex items-center group py-2 text-soft-theme-light"
                v-for="(action, key) in $page.props.flash.actionMenu.filter(action => action.can)"
                :key="key"
                @click="$emit('action-clicked', action.action)"
            >
                <icon
                    :name="action.icon"
                    class="w-4 h-4 mr-2 group-hover:text-white transition-colors duration-300 ease-in-out"
                />
                <div class="group-hover:text-white transition-colors duration-300 ease-in-out">
                    {{ action.label }}
                </div>
            </button>
        </div>
    </div>
</template>

<script>
import Icon from '@/Components/Helpers/Icon.vue';
export default {
    components: { Icon },
    emits: ['action-clicked'],
};
</script>