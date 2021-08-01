<template>
    <Teleport to="body">
        <Modal
            ref="modal"
            width-mode="form-cols-1"
            @closed="$emit('closed')"
            @opened="$refs.hnInput.focus()"
        >
            <template #header>
                <div class="font-semibold text-dark-theme-light">
                    บันทึก HN
                </div>
            </template>
            <template #body>
                <div class="py-4 my-2 md:py-6 md:my-4 border-t border-b border-bitter-theme-light">
                    <FormInput
                        class="mt-2"
                        v-model="form.hn"
                        name="hn"
                        type="tel"
                        label="hn"
                        ref="hnInput"
                        :error="form.errors.hn"
                    />
                    <FormInput
                        class="mt-2"
                        v-model="form.patient_name"
                        name="patient_name"
                        label="ชื่อผู้ป่วยตาม HN"
                        :error="form.errors.patient_name"
                        :readonly="true"
                    />
                    <FormInput
                        class="mt-2"
                        v-model="form.patient_name_org"
                        name="patient_name_org"
                        label="ชื่อผู้ป่วยตามใบคัดกรอง"
                        :error="form.errors.patient_name_org"
                        :readonly="true"
                    />
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end items-center">
                    <SpinnerButton
                        :spin="form.busy"
                        class="btn-dark w-full mt-6"
                        @click="store"
                        :disabled="!form.hn"
                    >
                        {{ form.confirmed ? 'ยืนยัน':'ตรวจสอบ' }}
                    </SpinnerButton>
                </div>
            </template>
        </Modal>
    </Teleport>
</template>

<script>
import Modal from '@/Components/Helpers/Modal';
import FormInput from '@/Components/Controls/FormInput';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import { ref } from '@vue/reactivity';
import { useForm } from '@inertiajs/inertia-vue3';
export default {
    emits: ['closed'],
    components: { Modal, FormInput, SpinnerButton },
    setup () {
        const form = useForm({
            hn: null,
            patient_name: null,
            patient_name_org: null,
            confirmed: false,
            slug: null,
            busy: false,
        });
        const modal = ref(null);
        const open = (visit) => {
            form.hn = null;
            form.patient_name = null;
            form.patient_name_org = visit.patient_name;
            form.confirmed = false;
            form.slug = visit.slug;
            form.busy = false;
            modal.value.open();
        };
        const hnInput = ref(null);

        const store = () => {
            form.busy = true;
            if (! form.confirmed) {
                window.axios
                    .get(window.route('resources.api.patients.show', form.hn))
                    .then(response => {
                        if (response.data.found) {
                            form.patient_name = response.data.full_name;
                            form.confirmed = true;
                            form.errors.hn = null;
                        } else {
                            form.errors.hn = response.data.message;
                        }
                    }).catch(errors => console.log(errors))
                    .finally(() => form.busy = false);
            } else {
                let endpoint = window.route('visits.fill-hn.store', form.slug);
                form.post(endpoint, {
                    onError: () => modal.value.close(),
                    onFinish: () => modal.value ? modal.value.close() : null
                });
            }
        };

        return {
            form,
            modal,
            open,
            hnInput,
            store,
        };
    },
};
</script>