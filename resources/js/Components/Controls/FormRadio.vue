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
import { reactive, ref } from '@vue/reactivity';
import { computed, watch } from '@vue/runtime-core';
export default {
    emits: ['update:modelValue', 'autosave'],
    props: {
        modelValue: { type: String, default: '' },
        options: { type: Array, required: true },
        name: { type: String, required: true },
        label: { type: String, default: '' },
        disabled: { type: Boolean },
        error: { type: String, default: '' },
        allowReset: { type:Boolean },
    },
    setup(props, context) {
        const items = reactive(
            typeof props.options[0] === 'string'
                ?   props.options.map( function (option) {
                    return { value: option, label: option };
                })
                :   [...props.options]
        );

        const selected = ref(props.modelValue);

        watch (
            () => selected.value,
            (val, old) => {
                console.log(old + ' -> ' + val);
                context.emit('update:modelValue', val);
            },
        );

        const computeItems = computed(() => {
            if (!props.allowReset || selected.value === null) {
                return items;
            } else {
                return [...items, { label: 'ยกเลิก', value: null }];
            }
        });

        return {
            items,
            selected,
            computeItems,
        };
    },
};
</script>