<template>
    <Teleport to="body">
        <Modal
            ref="modal"
            width-mode="form-cols-1"
            @closed="$emit('closed')"
            @opened="$refs.inputHn.focus()"
        >
            <template #header>
                <div class="font-semibold text-dark-theme-light">
                    เพิ่มเคสใหม่
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
                        ref="inputHn"
                        :error="form.errors.hn"
                    />
                    <FormInput
                        class="mt-2"
                        v-model="form.patient_name"
                        name="patient_name"
                        label="ชื่อผู้ป่วย (กรณียังไม่มี HN)"
                        placeholder="ชื่อ นามสกุล ไม่ต้องมีคำนำหน้า"
                        :error="form.errors.patient_name"
                        :readonly="hnProvided"
                    />
                    <FormSelect
                        label="ประเภทผู้ป่วย"
                        class="mt-2"
                        v-model="form.patient_type"
                        :error="form.errors.patient_type"
                        :options="['บุคคลทั่วไป', 'เจ้าหน้าที่ศิริราช']"
                        name="patient_type"
                    />
                    <FormSelect
                        label="ประเภทการตรวจ"
                        class="mt-2"
                        v-model="form.screen_type"
                        :error="form.errors.screen_type"
                        :options="['เริ่มตรวจใหม่', 'ตามนัด Swab 7 วัน', 'ตามนัด Swab 14 วัน', 'ตามนัด Reswab 7 วัน', 'ตามนัด Reswab 14 วัน']"
                        name="screen_type"
                    />
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end items-center">
                    <SpinnerButton
                        :spin="form.busy"
                        class="btn-dark w-full mt-6"
                        @click="store"
                        :disabled="!formCompleted"
                    >
                        {{ form.confirmed ? 'ยืนยัน':'เพิ่ม' }}
                    </SpinnerButton>
                </div>
            </template>
        </Modal>
    </Teleport>
</template>

<script>
import Modal from '@/Components/Helpers/Modal';
import FormInput from '@/Components/Controls/FormInput';
import FormSelect from '@/Components/Controls/FormSelect';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import { useForm } from '@inertiajs/inertia-vue3';
import { ref } from '@vue/reactivity';
import { computed, watch } from '@vue/runtime-core';
export default {
    emits: ['closed'],
    components: { Modal, FormInput, FormSelect, SpinnerButton },
    setup () {
        const modal = ref(null);
        const inputHn = ref(null);
        const form = useForm({
            hn: null,
            patient_name: null,
            patient_type: null,
            screen_type: null,
            confirmed: false,
            busy: false,
        });
        const formCompleted = computed(() => {
            return (form.patient_type && form.screen_type && (form.hn || form.patient_name)) ? true : false;
        });

        const hnProvided = computed(() => {
            return (form.hn && form.hn !== '') ? true : false;
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

        watch(
            () => form.patient_name,
            () => {
                form.confirmed = form.patient_name ? true:false;
            }
        );

        const open = () => {
            form.reset();
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
                modal.value.close();
                form.post(window.route('visits.store'));
            }
        };

        return {
            modal,
            inputHn,
            form,
            open,
            store,
            formCompleted,
            hnProvided,
        };
    }

};
</script>