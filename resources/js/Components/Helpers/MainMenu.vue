<template>
    <div v-if="$page.props.flash.mainMenuLinks.length">
        <div class="mb-4">
            <Link
                class="flex items-center group py-2 outline-none truncate"
                :href="route(link.route)"
                v-for="(link, key) in $page.props.flash.mainMenuLinks.filter(menu => menu.can)"
                :key="key"
            >
                <icon
                    :name="link.icon"
                    class="w-4 h-4 mr-2 transition-colors duration-300 ease-linear"
                    :class="isUrl(route(link.route)) ? 'text-white' : 'text-soft-theme-light group-hover:text-white'"
                />
                <div
                    class="duration-300 ease-linear"
                    :class="isUrl(route(link.route)) ? 'text-white' : 'text-soft-theme-light group-hover:text-white'"
                >
                    {{ link.label }}
                </div>
            </Link>
        </div>
    </div>
</template>

<script>
import Icon from '@/Components/Helpers/Icon.vue';
import { Link } from '@inertiajs/inertia-vue3';
export default {
    components: { Icon, Link },
    setup () {
        const isUrl = (url) => {
            return (location.origin + location.pathname) === url;
        };

        return {
            isUrl
        };
    }
};
</script>