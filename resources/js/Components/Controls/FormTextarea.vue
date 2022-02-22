<template>
    <div class="w-full">
        <label
            v-if="label"
            class="form-label"
            :for="name"
        >{{ label }} :</label>
        <textarea
            :id="name"
            :name="name"
            ref="textarea"
            @input="oninput"
            @change="$emit('autosave')"
            @focus="$emit('focus')"
            :type="type"
            :placeholder="placeholder"
            :pattern="pattern"
            :readonly="readonly"
            :value="modelValue"
            class="form-input"
            :class="{ 'border-red-400 text-red-400': error }"
        />
        <div
            v-if="error"
            class="text-red-700 mt-2 text-sm"
        >
            {{ error }}
        </div>
    </div>
</template>

<script>
import debounce from 'lodash/debounce';
import autosize from 'autosize';
export default {
    emits: ['autosave', 'update:modelValue', 'focus'],
    props: {
        modelValue: { type: String, default: '' },
        name: { type: String, required: true },
        label: { type: String, default: '' },
        type: { type: String, default: 'text' },
        placeholder: { type: String, default: '' },
        pattern: { type: String, default: '' },
        readonly: { type: Boolean },
        error: { type: String, default: '' },
    },
    created () {
        this.autosave = debounce(() => this.$emit('autosave'), 2500);
    },
    mounted () {
        autosize(this.$refs.textarea);
    },
    methods: {
        focus () {
            this.$refs.textarea.focus();
        },
        resize () {
            autosize.update(this.$refs.textarea);
        },
        oninput () {
            this.$emit('update:modelValue', this.$refs.textarea.value);
            this.autosave();
        }
    }
};
</script>