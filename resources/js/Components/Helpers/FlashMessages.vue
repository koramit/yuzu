<template>
    <div
        v-if="Object.keys($page.props.errors).length && $page.props.errors.hidden === undefined"
        class="flex items-center rounded-tl-lg rounded-tr-lg border-red-400 border-8 border-t-0 border-l-0 border-r-0 shadow p-4 mb-2"
    >
        <icon
            class="block w-12 h-12 text-red-400"
            name="exclamation-circle"
        />
        <div class="ml-4">
            <div class="flex my-2 text-dark-theme-light text-sm font-normal">
                <p>๏</p>
                <p class="px-2 tracking-wide leading-5">
                    ข้อมูลไม่ถูกต้อง <span class="font-semibold">{{ Object.keys($page.props.errors).length }} รายการ</span> กรุณาตรวจสอบ
                </p>
            </div>
        </div>
    </div>
    <div
        v-else-if="$page.props.flash.messages && !Object.keys($page.props.errors).length"
        class="flex items-center rounded-tl-lg rounded-tr-lg border-8 border-t-0 border-l-0 border-r-0 shadow p-4 mb-2"
        :class="{
            'border-thick-theme-light': $page.props.flash.messages.status === 'info',
            'border-bitter-theme-light': $page.props.flash.messages.status === 'success',
            'border-dark-theme-light': $page.props.flash.messages.status === 'warning',
        }"
    >
        <icon
            class="block w-12 h-12 text-thick-theme-light"
            name="info-circle"
            v-if="$page.props.flash.messages.status === 'info'"
        />
        <icon
            class="block w-12 h-12 text-bitter-theme-light"
            name="check-circle"
            v-else-if="$page.props.flash.messages.status === 'success'"
        />
        <icon
            class="block w-12 h-12 text-dark-theme-light"
            name="exclamation-circle"
            v-else-if="$page.props.flash.messages.status === 'warning'"
        />
        <div class="ml-4">
            <div
                class="flex my-2 text-dark-theme-light text-sm font-normal"
                v-for="(message, key) in $page.props.flash.messages.messages"
                :key="key"
            >
                <p>๏</p>
                <p
                    class="px-2 tracking-wide leading-5"
                    v-html="message"
                />
            </div>
        </div>
    </div>
</template>

<script>
import Icon from '@/Components/Helpers/Icon';
export default {
    components: { Icon },
};
</script>