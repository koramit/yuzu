<template>
    <div v-if="$page.props.flash.mainMenuLinks.length">
        <div class="mb-4">
            <template
                v-for="(link, key) in $page.props.flash.mainMenuLinks.filter(menu => menu.can)"
                :key="key"
            >
                <a
                    class="flex items-center group py-2 outline-none truncate"
                    :href="route(link.route)"
                    v-if="link.use_a_tag"
                >
                    <Icon
                        :name="link.icon"
                        class="w-4 h-4 transition-colors duration-200 ease-linear"
                        :class="isUrl(route(link.route)) ? 'text-white' : 'text-soft-theme-light group-hover:text-bitter-theme-light'"
                    />
                    <div
                        class="ml-2 duration-300 ease-linear"
                        :class="isUrl(route(link.route)) ? 'text-white' : 'text-soft-theme-light group-hover:text-bitter-theme-light'"
                        v-if="!zenMode"
                    >
                        {{ link.label }}
                    </div>
                </a>
                <Link
                    class="flex items-center group py-2 outline-none truncate"
                    :href="route(link.route)"
                    v-else
                >
                    <Icon
                        :name="link.icon"
                        class="w-4 h-4 transition-colors duration-200 ease-linear"
                        :class="isUrl(route(link.route)) ? 'text-white' : 'text-soft-theme-light group-hover:text-bitter-theme-light'"
                    />
                    <div
                        class="ml-2 duration-300 ease-linear"
                        :class="isUrl(route(link.route)) ? 'text-white' : 'text-soft-theme-light group-hover:text-bitter-theme-light'"
                        v-if="!zenMode"
                    >
                        {{ link.label }}
                    </div>
                </Link>
            </template>
        </div>
    </div>
</template>

<script setup>
import Icon from '@/Components/Helpers/Icon.vue';
import { Link } from '@inertiajs/inertia-vue3';
defineProps({
    zenMode: { type: Boolean }
});
const isUrl = (url) => {
    return (location.origin + location.pathname) === url;
};
</script>