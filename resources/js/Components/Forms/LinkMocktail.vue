<template>
    <div
        class="bg-white rounded shadow-sm p-4 mt-4 sm:mt-6 md:mt-12"
        v-if="configs.can"
    >
        <h2 class="font-semibold text-thick-theme-light border-b-2 border-dashed pb-2">
            {{ linked ? 'Mocktail Linked' : 'Link Mocktail' }}
        </h2>
        <template v-if="!linked">
            <FormInput
                class="mt-8"
                label="ชื่อบัญชี"
                name="login"
                v-model="form.login"
                :error="form.errors.login"
                ref="login_input"
            />
            <FormInput
                class="mt-2"
                type="password"
                label="รหัสผ่าน"
                name="password"
                v-model="form.password"
                :error="form.errors.password"
                @keydown.enter="login"
            />
            <SpinnerButton
                :spin="form.processing"
                class="btn-bitter w-full mt-8"
                @click="login"
                :disabled="!form.login || !form.password"
            >
                Link
            </SpinnerButton>
        </template>
    </div>
</template>

<script setup>
import FormInput from '@/Components/Controls/FormInput';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import { reactive, ref } from '@vue/reactivity';

const props = defineProps({
    configs: { type: Object, required: true }
});

const linked = ref(props.configs.linked);

const form = reactive({
    login: null,
    password: null,
    processing: false,
    errors: {},
});

const login = () => {
    form.processing = true;
    window.axios
        .post(window.route('mocktail.link'), form)
        .then((response) => {
            if (response.data.ok) {
                linked.value = true;
            } else {
                form.errors.login = response.data.login;
            }
        }).finally(() => form.processing = false);
};
</script>