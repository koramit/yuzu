<template>
    <teleport to="body">
        <modal
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
                        <form-input
                            v-model="reason"
                            placeholder="โปรดระบุเหตุผล"
                            name="reason"
                        />
                    </template>
                </div>
            </template>
            <template #footer>
                <spinner-button
                    :spin="busy"
                    class="btn-dark w-full mt-6"
                    @click="confirmed"
                    :disabled="needReason && !reason"
                >
                    ยืนยัน
                </spinner-button>
            </template>
        </modal>
    </teleport>
</template>
<script>
import FormInput from '@/Components/Controls/FormInput';
import SpinnerButton from '@/Components/Controls/SpinnerButton';
import Modal from '@/Components/Helpers/Modal';
export default {
    emits: ['closed'],
    components: { FormInput, Modal, SpinnerButton },
    data () {
        return {
            busy: false,
            reason: null,
            confirmText: null,
            needReason: false
        };
    },
    methods: {
        open (configs) {
            this.needReason = configs.needReason;
            this.confirmText = configs.confirmText;
            this.$refs.modal.open();
        },
        confirmed () {
            this.eventBus.emit('confirmed', this.reason);
            this.$refs.modal.close();
        }
    }
};
</script>