<template>
    <div v-if="$page.props.flash.mainMenuLinks.length">
        <div class="mb-4">
            <Link
                class="flex items-center group py-2 outline-none truncate"
                :href="route(link.route)"
                v-for="(link, key) in $page.props.flash.mainMenuLinks"
                :key="key"
            >
                <icon
                    :name="link.icon"
                    class="w-4 h-4 mr-2"
                    :class="isUrl(route(link.route)) ? 'text-white' : 'text-soft-theme-light group-hover:text-white'"
                />
                <div :class="isUrl(route(link.route)) ? 'text-white' : 'text-soft-theme-light group-hover:text-white'">
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
    props: {
        url: { type: String, default: '' }
    },
    methods: {
        isUrl(...urls) {
            if (urls[0] === '') {
                return this.url === '';
            }
            return urls.filter(url => this.url.startsWith(url)).length;
        },
    }
};
</script>