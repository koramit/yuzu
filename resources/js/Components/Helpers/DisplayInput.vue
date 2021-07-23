<template>
    <div class="w-full">
        <label
            v-if="label"
            class="form-label text-print-size"
        >{{ label }} :</label>
        <div
            class="break-words text-print-size"
            v-html="formattedData"
        />
    </div>
</template>

<script>
import { computed } from '@vue/runtime-core';
export default {
    props: {
        label: { type: String, default: '' },
        data: { type: String, default: '' },
        format: { type: String, default: '' },
    },
    setup (props) {
        const formattedData = computed(() => {
            if (props.data || props.format) {
                return props.data;
            }

            if (props.format === 'date') {
                let months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                let ymd = this.data.split('-');
                return ymd[2] + ' ' + months[parseInt(ymd[1])] + ' ' + ymd[0];
            }

            return props.data;
        });

        return {
            formattedData,
        };
    }
    // computed: {
    //     formattedData () {
    //         if (!this.data || !this.format) {
    //             return this.data;
    //         }

    //         if (this.format === 'date') {
    //             let months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    //             let ymd = this.data.split('-');
    //             return ymd[2] + ' ' + months[parseInt(ymd[1])] + ' ' + ymd[0];
    //         }

    //         return this.data;
    //     }
    // },
};
</script>