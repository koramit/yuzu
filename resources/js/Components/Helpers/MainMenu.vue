<template>
    <div v-if="$page.props.flash.mainMenuLinks.length">
        <div class="mb-4">
            <inertia-link
                class="flex items-center group py-2 outline-none truncate"
                :href="link.route[0] === '#' ? link.route : `${$page.props.app.baseUrl}/${link.route}`"
                v-for="(link, key) in $page.props.flash.mainMenuLinks"
                :key="key"
            >
                <icon
                    :name="link.icon"
                    class="w-4 h-4 mr-2"
                    :class="isUrl(link.route) ? 'text-white' : 'text-soft-theme-light group-hover:text-white'"
                />
                <div :class="isUrl(link.route) ? 'text-white' : 'text-soft-theme-light group-hover:text-white'">
                    {{ link.label }}
                </div>
            </inertia-link>
        </div>
    </div>
</template>

<script>
import Icon from '@/Components/Helpers/Icon.vue';
export default {
    components: { Icon },
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