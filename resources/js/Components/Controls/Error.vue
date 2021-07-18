<template>
    <div
        v-if="error"
        class="text-red-700 mt-2 text-sm"
    >
        {{ error }}
    </div>
</template>

<script>
export default {
    props: {
        error: { type: String, default: '' },
    }
};
</script>