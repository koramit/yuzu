<template>
    <div>
        <label
            v-if="label"
            class="form-label"
        >{{ label }} :</label>
        <div
            v-for="(item, key) in computeItems"
            :key="key"
            class="mb-2 flex text-gray-600 appearance-none w-full py-1 px-2 lg:py-2 lg:px-3 bg-white shadow-sm rounded border-2 border-gray-200 transition-colors duration-200 ease-in-out cursor-pointer"
            :class="{
                'opacity-50': selected && selected !== item.value,
                'border-bitter-theme-light font-medium': selected === item.value,
            }"
        >
            <div class="text-bitter-theme-light">
                <input
                    :id="item.value+'-'+name"
                    type="radio"
                    class="shadow-sm h-5 w-5 transition-all duration-200 ease-in-out appearance-none inline-block align-middle rounded-full border border-dark-theme-light select-none flex-shrink-0 cursor-pointer focus:outline-none"
                    :value="item.value"
                    :name="name"
                    v-model="selected"
                    :checked="item.value === selected"
                >
            </div>
            <label
                :for="item.value+'-'+name"
                v-text="item.label"
                class="ml-4 w-full block cursor-pointer"
            />
        </div>
    </div>
</template>

<script>
import { ref } from '@vue/reactivity';
import { computed, watch } from '@vue/runtime-core';
export default {
    emits: ['update:modelValue', 'autosave'],
    props: {
        modelValue: { type: [String, Number], default: '' },
        options: { type: Array, required: true },
        name: { type: String, required: true },
        label: { type: String, default: '' },
        disabled: { type: Boolean },
        error: { type: String, default: '' },
        allowReset: { type:Boolean },
        allowOther: { type:Boolean },
    },
    setup(props, context) {
        const selected = ref(props.modelValue);

        watch (
            () => selected.value,
            (val) => {
                context.emit('update:modelValue', val);
                context.emit('autosave');
            },
        );

        const computeItems = computed(() => {
            let options = typeof props.options[0] === 'string'
                ?   props.options.map( function (option) {
                    return { value: option, label: option };
                })
                :   [...props.options];

            if (props.allowOther) {
                options.push({ label: 'อืนๆ', value: 'other' });
            }

            if (!props.allowReset || selected.value === null) {
                return options;
            } else {
                options.push({ label: 'ยกเลิก', value: null });
                return options;
                // return [...options, { label: 'ยกเลิก', value: null }];
            }
        });

        const setOther = (val) => {
            selected.value = val;
            // context.emit('update:modelValue', val);
            // context.emit('autosave');
        };

        return {
            selected,
            computeItems,
            setOther,
        };
    },
};
</script>