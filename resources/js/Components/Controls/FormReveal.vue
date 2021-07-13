<template>
    <div class="w-full">
        <label
            v-if="label"
            class="form-label"
            :for="name"
        >{{ label }} :</label>
        <div class="relative">
            <input
                class="form-input"
                readonly
                type="text"
                :value="show ? secret:'🤫🤫🤫🤫🤫🤫🤫🤫🤫🤫🤫🤫'"
                :id="name"
            >
            <div
                class="absolute inset-y-0 right-0 flex items-center px-2 text-thick-theme-light cursor-pointer"
                @click="show = !show"
            >
                <Icon
                    class="w-4 h-4"
                    :name="show ? 'eyes-slash':'eyes'"
                />
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from '@vue/reactivity';
import Icon from '@/Components/Helpers/Icon';
export default {
    components: { Icon },
    props: {
        label: { type: String, default: '' },
        name: { type: String, required: true },
        secret: { type: [String, Number], required: true },
    },
    setup () {
        const show = ref(false);

        return {
            show,
        };
    },
};
</script>