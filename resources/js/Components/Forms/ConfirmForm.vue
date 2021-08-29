<template>
    <!-- <teleport to="body"> -->
    <Modal
        ref="modal"
        width-mode="form-cols-1"
        @closed="$emit('closed')"
    >
        <template #header>
            <div class="font-semibold text-dark-theme-light">
                โปรดยืนยัน
            </div>
        </template>
        <template #body>
            <div class="py-4 my-2 md:py-6 md:my-4 border-t border-b border-bitter-theme-light">
                <p
                    class="font-semibold text-yellow-400"
                    v-html="confirmText"
                />
                <template v-if="needReason">
                    <FormInput
                        v-model="reason"
                        placeholder="โปรดระบุเหตุผล"
                        name="reason"
                    />
                </template>
            </div>
        </template>
        <template #footer>
            <SpinnerButton
                class="btn-dark w-full mt-6"
                @click="confirmed"
                :disabled="needReason && !reason"
            >
                ยืนยัน
            </SpinnerButton>
        </template>
    </Modal>
</template>
<script>
import FormInput from '@/Components/Controls/FormInput';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import Modal from '@/Components/Helpers/Modal';
import { ref } from '@vue/reactivity';
import { inject } from '@vue/runtime-core';
export default {
    emits: ['closed'],
    components: { FormInput, Modal, SpinnerButton },
    setup () {
        const emitter = inject('emitter');
        const reason = ref(null);
        const confirmText = ref(null);
        const needReason = ref(false);
        const modal = ref(null);

        const open = (configs) => {
            needReason.value = configs.needReason;
            confirmText.value = configs.confirmText;
            modal.value.open();
        };

        const confirmed = () => {
            emitter.emit('confirmed', reason.value);
            modal.value.close();
        };

        return {
            needReason,
            reason,
            confirmText,
            modal,
            open,
            confirmed
        };
    }
};
</script>