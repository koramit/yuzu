<template>
    <div v-if="toggler">
        <!-- Toggle Button -->
        <label
            class="inline-flex items-center cursor-pointer"
        >
            <!-- toggle -->
            <div class="relative">
                <!-- input -->
                <input
                    type="checkbox"
                    class="hidden"
                    @change="change"
                >
                <!-- line -->
                <div
                    class="w-8 h-5 bg-gray-200 rounded-full shadow-inner transition-all duration-200 ease-in-out"
                    :class="{ 'bg-bitter-theme-light' : modelValue }"
                />
                <!-- dot -->
                <div
                    class="absolute w-5 h-5 bg-white rounded-full shadow inset-y-0 left-0 transition-all duration-200 ease-in-out transform"
                    :class="{ 'translate-x-3' : modelValue }"
                />
            </div>
            <!-- label -->
            <div class="ml-3 text-sm md:text-base xl:text-lg">
                {{ label }}
            </div>
        </label>
    </div>
    <div v-else>
        <label class="inline-flex items-center cursor-pointer ">
            <span class="text-bitter-theme-light">
                <input
                    type="checkbox"
                    class="shadow-xs h-6 w-6 transition-all duration-200 ease-in-out appearance-none color inline-block align-middle border border-dark-theme-light select-none flex-shrink-0 rounded cursor-pointer focus:outline-none"
                    :checked="modelValue"
                    @change="change"
                    :disabled="disabled"
                >
            </span>
            <span class="ml-4 text-sm md:text-base xl:text-lg">{{ label }}</span>
        </label>
    </div>
</template>

<script>
export default {
    emits: ['update:modelValue', 'autosave'],
    props: {
        modelValue: { type: Boolean },
        label: { type: String, default: '' },
        toggler: { type: Boolean },
        disabled: { type: Boolean },
    },
    methods: {
        change () {
            this.$emit('update:modelValue', !this.modelValue);
            this.$emit('autosave');
        },
        check () {
            this.$emit('update:modelValue', !this.modelValue);
        }
    }
};
</script>