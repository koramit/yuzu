<template>
    <div class="w-full">
        <label
            class="form-label"
            :for="name"
        >{{ label }} :</label>
        <input
            :id="name"
            :name="name"
            ref="input"
            type="date"
            :placeholder="placeholder"
            :disabled="disabled"
            readonly
            :value="modelValue"
            class="form-input"
        >
        <div
            v-if="error"
            class="text-red-700 mt-2 text-sm"
        >
            {{ error }}
        </div>
    </div>
</template>

<script>
import 'flatpickr/dist/themes/light.css';
import flatpickr from 'flatpickr';
export default {
    emits: ['autosave', 'update:modelValue'],
    props: {
        modelValue: { type: String, default: '' },
        name: { type: String, required: true },
        label: { type: String, default: '' },
        mode: { type: String, default: 'date' },
        placeholder: { type: String, default: '' },
        format: { type: String, default: 'F j, Y' }, // format for display date
        options: { type: Object, default: () => {} },
        error: { type: String, default: '' },
        disabled: { type: Boolean },
    },
    created () {
        const onChange = (selectedDates, dateStr, fp) => {
            // update model
            this.$emit('update:modelValue', dateStr);
            // Emit autosave if field name available
            this.$emit('autosave');
        };

        this.flatpickrOptions = {
            date: { // init flatpickr instance
                altInput: true,
                altFormat: this.format,
                onChange: onChange
            },
            time: {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                minTime: '07:00',
                maxTime: '21:00',
                time_24hr: true,
                minuteIncrement: 30,
                onChange: onChange,
            }
        };

        if (this.modelValue) {
            this.flatpickrOptions.date.defaultDate = this.modelValue;
        }

        if (this.options !== undefined) {
            this.flatpickrOptions[this.mode] = {... this.flatpickrOptions[this.mode], ...this.options};
        }
    },
    watch: {
        disabled (val) {
            this.fp._input.disabled = val;
        },
        error (val) {
            if (val) {
                this.fp._input.classList.add('border-red-400', 'text-red-400');
            } else {
                this.fp._input.classList.remove('border-red-400', 'text-red-400');
            }
        }
    },
    mounted () {
        this.fp = flatpickr(this.$refs.input, this.flatpickrOptions[this.mode]);
    },
    methods: {
        setDate (date) {
            this.fp.setDate(date);
            // update model
            this.$emit('update:modelValue', this.$refs.input.value);
            // Emit autosave if field name available
            this.$emit('autosave');
        },
        clear() {
            this.fp.clear();
            // update model
            this.$emit('update:modelValue', this.$refs.input.value);
            // Emit autosave if field name available
            this.$emit('autosave');
        }
    }
};
</script>

<style>
    .calendar-event {
        position: absolute;
        width: 3px;
        height: 3px;
        border-radius: 150px;
        bottom: 3px;
        left: calc(50% - 1.5px);
        content: "•";
        display: block;
        background: #3d8eb9;
    }

    .calendar-event.busy {
        background: #f64747;
    }
</style>