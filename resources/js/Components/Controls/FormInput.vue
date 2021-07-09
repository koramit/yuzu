<template>
    <div class="w-full">
        <label
            v-if="label"
            class="form-label"
            :for="name"
        >{{ label }} :</label>
        <div
            v-if="switchLabel"
            class="flex"
        >
            <input
                :id="name"
                :name="name"
                ref="input"
                @input="$emit('update:modelValue', $refs.input.value)"
                @change="$emit('autosave')"
                :type="type"
                :placeholder="placeholder"
                :pattern="pattern"
                :readonly="readonly"
                :value="modelValue"
                class="form-input border-r-0 rounded-r-none"
                :class="{ 'border-red-400': error }"
            >
            <div class="w-auto flex items-center px-2 border-2 border-gray-200 rounded shadow-sm border-l-0 rounded-l-none bg-gray-50">
                <label class="inline-flex items-center">
                    <input
                        type="checkbox"
                        class="shadow-xs h-6 w-6 transition-all duration-200 ease-in-out appearance-none color inline-block align-middle border border-gray-400 select-none flex-shrink-0 rounded cursor-pointer focus:outline-none"
                        :checked="modelCheckbox"
                        @change="change"
                    >
                    <span class="ml-4 text-lg cursor-pointer whitespace-nowrap">{{ switchLabel }}</span>
                </label>
            </div>
        </div>
        <input
            v-else
            :id="name"
            :name="name"
            ref="input"
            @input="$emit('update:modelValue', $refs.input.value)"
            @change="$emit('autosave')"
            :type="type"
            :placeholder="placeholder"
            :pattern="pattern"
            :readonly="readonly"
            :value="modelValue"
            class="form-input"
            :class="{ 'border-red-400 text-red-400': error }"
        >
        <div
            v-if="error"
            class="text-red-700 mt-2 text-sm"
        >
            {{ error }}
        </div>
    </div>
</template>

<script>
export default {
    emits: ['autosave', 'update:modelValue', 'update:modelCheckbox'],
    props: {
        modelValue: { type: String, default: '' },
        modelCheckbox: { type: Boolean },
        name: { type: String, required: true },
        label: { type: String, default: '' },
        type: { type: String, default: 'text' },
        placeholder: { type: String, default: '' },
        pattern: { type: String, default: '' },
        readonly: { type: Boolean },
        error: { type: String, default: '' },
        switchLabel: { type: String, default: '' }
    },
    methods: {
        focus () {
            this.$refs.input.focus();
        },
        change () {
            this.$emit('update:modelCheckbox', !this.modelCheckbox);
            this.$emit('autosave');
        },
    }
};
</script>