<template>
    <div v-if="$page.props.flash.actionMenu.length">
        <div class="mb-4">
            <button
                class="flex items-center group py-2 text-soft-theme-light group-hover:text-white"
                v-for="(action, key) in $page.props.flash.actionMenu"
                :key="key"
                @click="$emit('action-clicked', action.action)"
            >
                <icon
                    :name="action.icon"
                    class="w-4 h-4 mr-2"
                />
                <div>
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