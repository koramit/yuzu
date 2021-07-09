<template>
    <div class="w-full">
        <label
            v-if="label"
            class="form-label"
            :for="name"
        >{{ label }} :</label>
        <div class="relative">
            <select
                :id="name"
                :name="name"
                ref="input"
                :placeholder="placeholder"
                :disabled="disabled"
                :value="modelValue"
                @change="change"
                class="form-input cursor-pointer disabled:cursor-not-allowed"
                :class="{ 'border-red-400': error, 'bg-gray-400': disabled }"
            >
                <option
                    disabled
                    value=""
                >
                    โปรดเลือก
                </option>
                <option
                    class="italic text-yellow-500"
                    v-if="modelValue"
                >
                    ยกเลิก
                </option>
                <option
                    v-for="(option, key) in itemOptions"
                    :key="key"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
                <option
                    v-for="(option, key) in valueOptions"
                    :key="key"
                    :value="option"
                >
                    {{ option }}
                </option>
                <option
                    value="other"
                    v-if="allowOther"
                >
                    อื่นๆ
                </option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg
                    class="fill-current h-4 w-4"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                ><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" /></svg>
            </div>
        </div>
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
    emits: ['update:modelValue', 'autosave'],
    props: {
        modelValue: { type: String, default: '' },
        options: { type: Array, required: true },
        name: { type: String, required: true },
        label: { type: String, default: '' },
        placeholder: { type: String, default: '' },
        disabled: { type: Boolean },
        error: { type: String, default: '' },
        allowOther: { type:Boolean }
    },
    computed: {
        valueOptions () {
            return typeof this.options[0] === 'object' ? [] : this.options;
        },
        itemOptions () {
            return typeof this.options[0] === 'object' ? this.options : [];
        }
    },
    watch: {
        modelValue (val) {
            if (val === 'ยกเลิก') {
                this.$emit('update:modelValue', '');
                this.$emit('autosave');
            }
        }
    },
    methods: {
        change () {
            this.$emit('update:modelValue', this.$refs.input.value);
            this.$emit('autosave');
        },
        setOther (val) {
            this.$nextTick(() => {
                this.$refs.input.value = val;
                this.change();
            });
        }
    }
};
</script>