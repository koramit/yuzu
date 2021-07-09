<template>
    <teleport to="body">
        <modal
            ref="modal"
            width-mode="form-cols-1"
            @opened="$refs.otherItem.focus()"
            @closed="$emit('closed', otherItem)"
        >
            <template #header>
                <div class="font-semibold text-dark-theme-light">
                    {{ placeholder }}
                </div>
            </template>
            <template #body>
                <div class="py-4 my-2 md:py-6 md:my-4 border-t border-b border-bitter-theme-light">
                    <form-input
                        v-model="otherItem"
                        name="otherItem"
                        ref="otherItem"
                    />
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end items-center">
                    <button
                        class="btn btn-dark px-5"
                        @click="$refs.modal.close()"
                        :disabled="!otherItem"
                    >
                        เพิ่ม
                    </button>
                </div>
            </template>
        </modal>
    </teleport>
</template>

<script>
import FormInput from '@/Components/Controls/FormInput';
import Modal from '@/Components/Helpers/Modal';
export default {
    emits: ['closed'],
    components: { FormInput, Modal },
    props: {
        placeholder: { type: String, default: 'โปรดระบุ' },
    },
    data () {
        return {
            otherItem: '',
        };
    },
    methods: {
        open () {
            this.otherItem = '';
            this.$refs.modal.open();
        }
    }
};
</script>