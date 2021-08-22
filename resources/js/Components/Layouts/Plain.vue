<template>
    <Head>
        <title>{{ $page.props.flash.title }}</title>
    </Head>
    <div>
        <slot />
    </div>
</template>

<script>
import { useCheckSessionTimeout } from '@/Functions/useCheckSessionTimeout';
import { useRemoveLoader } from '@/Functions/useRemoveLoader';
import { Head } from '@inertiajs/inertia-vue3';
import { nextTick } from '@vue/runtime-core';
export default {
    components: { Head },
    setup() {
        useCheckSessionTimeout();
        useRemoveLoader();
        nextTick(() => document.querySelector('body').classList.toggle('bg-soft-theme-light'));
    },
};
</script>