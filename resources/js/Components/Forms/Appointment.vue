<template>
    <Modal
        ref="modal"
        width-mode="form-cols-1"
        @closed="$emit('closed')"
        @opened="$refs.hnInput.focus()"
    >
        <template #header>
            <div class="font-semibold text-dark-theme-light">
                เพิ่มเคสของพรุ่งนี้ {{ $page.props.app.tomorrow_label }}
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
                    v-if="form.patient_name"
                    class="mt-2"
                    v-model="form.patient_name"
                    name="patient_name"
                    label="ชื่อผู้ป่วย"
                    :readonly="true"
                />
            </div>
        </template>
        <template #footer>
            <!-- <div class="flex items-center"> -->
            <SpinnerButton
                :spin="form.busy"
                class="btn-dark w-full mt-6"
                @click="store"
                :disabled="!form.hn"
            >
                {{ form.confirmed ? 'ยืนยัน':'ตรวจสอบ' }}
            </SpinnerButton>
            <SpinnerButton
                :spin="fileUploader.processing"
                class="btn-bitter w-full mt-6"
                @click="importAppointments.click()"
            >
                นำเข้า Excel
            </SpinnerButton>
            <input
                class="hidden"
                type="file"
                ref="importAppointments"
                @input="fileSelected"
                accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
            >
        </template>
    </Modal>
</template>

<script>
import Modal from '@/Components/Helpers/Modal';
import FormInput from '@/Components/Controls/FormInput';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import { ref } from '@vue/reactivity';
import { watch } from '@vue/runtime-core';
export default {
    emits: ['closed'],
    components: { Modal, FormInput, SpinnerButton },
    setup () {
        const modal = ref(null);
        const hnInput = ref(null);
        const form = useForm({
            hn: null,
            patient_name: null,
            date_visit: null,
            confirmed: false,
            busy: false,
        });

        watch(
            () => form.hn,
            () => {
                if (form.patient_name !== null) {
                    form.patient_name = null;
                    form.confirmed = false;
                }

                if (!form.hn) {
                    form.errors.hn = null;
                }
            }
        );

        const open = () => {
            form.reset();
            form.date_visit = usePage().props.value.app.tomorrow;
            modal.value.open();
        };

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
                form.post(window.route('appointments.store'), {
                    onError: () => modal.value.close(),
                    onFinish: () => modal.value ? modal.value.close() : null
                });
            }
        };

        const importAppointments = ref(null);
        const fileUploader = useForm({file: null});
        const fileSelected = (event) => {
            fileUploader.file = event.target.files[0];
            fileUploader.post(window.route('import.appointments'), {
                onError: () => modal.value.close(),
                onFinish: () => modal.value ? modal.value.close() : null
            });
        };

        return {
            modal,
            hnInput,
            importAppointments,
            form,
            open,
            store,
            fileSelected,
            fileUploader
        };
    }

};
</script>