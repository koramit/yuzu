<template>
    <div>
        <div class="flex items-center">
            <p class="form-label">
                <span class="inline-block">{{ label }}</span>
                <icon
                    class="ml-1 w-4 h-4 inline-block opacity-25 animate-spin"
                    name="circle-notch"
                    v-if="loading || form.processing"
                />
            </p>
            <button
                v-if="!readonly"
                class="ml-4"
                @click="$refs.useCamera.click()"
            >
                <icon
                    class="w-4 h-4 text-thick-theme-light"
                    name="camera"
                />
            </button>
            <button
                v-if="!readonly"
                class="ml-4"
                @click="$refs.useGallery.click()"
            >
                <icon
                    class="w-4 h-4 text-thick-theme-light"
                    name="image"
                />
            </button>
            <button
                class="ml-4"
                v-if="modelValue"
                @click="show = !show"
            >
                <icon
                    class="w-4 h-4 text-dark-theme-light"
                    :name="show ? 'eyes-slash':'eyes'"
                />
            </button>
        </div>
        <div
            v-if="error"
            class="text-red-700 text-sm"
        >
            {{ error }}
        </div>
        <img
            v-if="modelValue !== undefined && show"
            :src="`${baseUrl}/uploads/${modelValue}`"
            @loadstart="loading = true"
            @load="$nextTick(() => loading = false)"
            alt=""
        >
        <input
            class="hidden"
            type="file"
            ref="useCamera"
            @input="fileInput"
            capture="environment"
            accept="image/*"
        >
        <input
            class="hidden"
            type="file"
            ref="useGallery"
            @input="fileInput"
            accept="image/*"
        >
    </div>
</template>

<script>
import { useForm } from '@inertiajs/inertia-vue3';
import Icon from '@/Components/Helpers/Icon';
export default {
    emits: ['update:modelValue', 'autosave'],
    components: { Icon },
    props: {
        modelValue: { type: String, default: '' },
        label: { type: String, required: true },
        name: { type: String, required: true },
        noteId: { type: Number, required: true },
        error: { type: String, default: '' },
        readonly: { type: Boolean, },
    },
    data () {
        return {
            form: useForm({
                file: null,
                name: this.name,
            }),
            baseUrl: this.$page.props.app.baseUrl,
            show: false,
            loading: false,
        };
    },
    methods: {
        fileInput (event) {
            this.form.file = event.target.files[0];
            this.form.transform(data => ({...data, note_id: this.noteId}))
                .post(`${this.baseUrl}/uploads`, {
                    preserveScroll: true,
                    onFinish: () => {
                        if (!this.show) {
                            this.show = true;
                        }
                        // this is NOT A ERROR, but its only way (that I know) to make inertia accept response data when using visit api
                        this.$emit('update:modelValue', this.form.errors.filename);
                    }
                });
        }
    }
};
</script>