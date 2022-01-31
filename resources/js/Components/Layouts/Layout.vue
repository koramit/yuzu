<template>
    <Head>
        <title>{{ $page.props.flash.title }}</title>
    </Head>
    <div>
        <!-- main contailner, flex makes its childs extend full h -->
        <div class="md:h-screen md:flex md:flex-col">
            <!-- this is navbar, with no shrink (fixed width) -->
            <div class="md:flex md:flex-shrink-0 sticky top-0 z-30">
                <!-- full navbar on mobile and left brand on desktop -->
                <div
                    class="bg-dark-theme-light text-white md:flex-shrink-0 px-4 py-2 flex items-center justify-between md:justify-center"
                    :class="{
                        'md:w-56 xl:w-64': !zenMode,
                        'md:w-12': zenMode
                    }"
                >
                    <!-- the logo -->
                    <Link
                        class="inline-block md:hidden"
                        :href="route('home')"
                    >
                        <span class="font-bold text-lg md:text-2xl">Yuzu</span>
                    </Link>
                    <button
                        class="hidden md:inline-block font-bold text-lg md:text-2xl"
                        @click="zenMode = !zenMode"
                    >
                        {{ zenMode ? '🍊':'Yuzu' }}
                    </button>
                    <!-- title display on mobile -->
                    <div class="text-soft-theme-light text-sm truncate mx-1 md:hidden">
                        {{ $page.props.flash.title }}
                    </div>
                    <!-- lemon menu on mobile -->
                    <button
                        class="md:hidden text-soft-theme-light transition-colors duration-300 ease-in-out"
                        :class="{'text-bitter-theme-light': mobileMenuVisible}"
                        @click="mobileMenuVisible = !mobileMenuVisible"
                    >
                        <svg
                            class="w-6 h-6"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 512 512"
                        ><path
                            fill="currentColor"
                            d="M484.112 27.889C455.989-.233 416.108-8.057 387.059 8.865 347.604 31.848 223.504-41.111 91.196 91.197-41.277 223.672 31.923 347.472 8.866 387.058c-16.922 29.051-9.1 68.932 19.022 97.054 28.135 28.135 68.011 35.938 97.057 19.021 39.423-22.97 163.557 49.969 295.858-82.329 132.474-132.477 59.273-256.277 82.331-295.861 16.922-29.05 9.1-68.931-19.022-97.054zm-22.405 72.894c-38.8 66.609 45.6 165.635-74.845 286.08-120.44 120.443-219.475 36.048-286.076 74.843-22.679 13.207-64.035-27.241-50.493-50.488 38.8-66.609-45.6-165.635 74.845-286.08C245.573 4.702 344.616 89.086 411.219 50.292c22.73-13.24 64.005 27.288 50.488 50.491zm-169.861 8.736c1.37 10.96-6.404 20.957-17.365 22.327-54.846 6.855-135.779 87.787-142.635 142.635-1.373 10.989-11.399 18.734-22.326 17.365-10.961-1.37-18.735-11.366-17.365-22.326 9.162-73.286 104.167-168.215 177.365-177.365 10.953-1.368 20.956 6.403 22.326 17.364z"
                        /></svg>
                    </button>
                </div>
                <!-- middle and right navbar on desktop -->
                <div class="hidden md:flex w-full font-semibold text-dark-theme-light bg-alt-theme-light border-b sticky top-0 z-30 p-4 md:py-0 md:px-12 justify-between items-center">
                    <!-- title display on desktop -->
                    <div class="mr-4 w-full flex justify-between items-center">
                        <div>{{ $page.props.flash.title }}</div>
                        <div
                            class="text-white"
                            id="scaleFontButtons"
                        >
                            <button
                                class="w-6 h-6 rounded-full transition-colors duration-200 ease-in hover:bg-white hover:text-dark-theme-light mr-2"
                                v-text="'a'"
                                @click="scaleFont('down')"
                            />
                            <button
                                class="w-6 h-6 rounded-full transition-colors duration-200 ease-in hover:bg-white hover:text-dark-theme-light font-semibold mr-2"
                                v-text="'A'"
                                @click="scaleFont('up')"
                            />
                        </div>
                    </div>
                    <!-- username and menu -->
                    <Dropdown>
                        <template #default>
                            <div class="w-full flex items-center cursor-pointer select-none group">
                                <div class="group-hover:text-bitter-theme-light focus:text-bitter-theme-light mr-1 whitespace-nowrap">
                                    <span>{{ $page.props.user.name }}</span>
                                </div>
                                <Icon
                                    class="w-4 h-4 group-hover:text-bitter-theme-light focus:text-bitter-theme-light"
                                    name="double-down"
                                />
                            </div>
                        </template>
                        <template #dropdown>
                            <div class="mt-2 py-2 shadow-xl min-w-max bg-thick-theme-light text-white cursor-pointer rounded text-sm">
                                <!-- <template v-if="$page.props.user.roles.length"> -->
                                <Link
                                    class="block px-6 py-2 hover:bg-dark-theme-light hover:text-soft-theme-light"
                                    :href="route('preferences')"
                                    v-if="! isUrl(route('preferences'))"
                                >
                                    ตั้งค่า
                                </Link>
                                <Link
                                    class="block px-6 py-2 hover:bg-dark-theme-light hover:text-soft-theme-light"
                                    :href="route('medical-records')"
                                    v-if="! isUrl(route('medical-records'))"
                                >
                                    ประวัติการตรวจ
                                </Link>
                                <!-- </template> -->
                                <Link
                                    class="w-full font-semibold text-left px-6 py-2 hover:bg-dark-theme-light hover:text-soft-theme-light"
                                    :href="route('logout')"
                                    method="delete"
                                    as="button"
                                    type="button"
                                >
                                    ออกจากระบบ
                                </Link>
                            </div>
                        </template>
                    </Dropdown>
                </div>
                <!-- menu on mobile -->
                <div
                    class="md:hidden w-5/6 block fixed left-0 inset-y-0 overflow-y-scroll text-soft-theme-light bg-dark-theme-light rounded-tr rounded-br shadow-md transition-transform transform duration-300 ease-in-out"
                    :class="{ '-translate-x-full': !mobileMenuVisible }"
                >
                    <div class="p-4 relative min-h-full">
                        <!-- username and menu -->
                        <div
                            class="flex flex-col text-center"
                            @click="mobileMenuVisible = false"
                        >
                            <!-- <div class="flex justify-center mt-2">
                                <div
                                    class="w-12 h-12 rounded-full overflow-hidden border-bitter-theme-light border-2"
                                    v-if="!avatarSrcError"
                                >
                                    <img
                                        :src="`${$page.props.user.avatar}`"
                                        alt="C"
                                        @error="avatarSrcError = true"
                                    >
                                </div>
                            </div> -->
                            <span class="inline-block py-1 text-white">{{ $page.props.user.name }}</span>
                            <!-- <template v-if="$page.props.user.roles.length"> -->
                            <Link
                                class="block py-1"
                                :href="route('preferences')"
                                v-if="! isUrl(route('preferences'))"
                            >
                                ตั้งค่า
                            </Link>
                            <Link
                                class="block py-1"
                                :href="route('medical-records')"
                                v-if="! isUrl(route('medical-records'))"
                            >
                                ประวัติการตรวจ
                            </Link>
                            <!-- </template> -->
                            <Link
                                class="block py-1"
                                :href="route('logout')"
                                method="delete"
                                as="button"
                                type="button"
                            >
                                ออกจากระบบ
                            </Link>
                        </div>
                        <hr class="my-4">
                        <MainMenu @click="mobileMenuVisible = false" />
                        <ActionMenu @action-clicked="actionClicked" />
                    </div>
                </div>
            </div>
            <!-- this is content -->
            <div class="md:flex md:flex-grow md:overflow-hidden">
                <!-- this is sidebar menu on desktop -->
                <div
                    class="hidden md:block bg-thick-theme-light flex-shrink-0 overflow-y-auto"
                    :class="{
                        'w-56 xl:w-64 px-6 py-12': !zenMode,
                        'w-12 md:p-4': zenMode,
                    }"
                >
                    <MainMenu :zen-mode="zenMode" />
                    <ActionMenu
                        :zen-mode="zenMode"
                        @action-clicked="actionClicked"
                    />
                </div>
                <!-- this is main page -->
                <div
                    class="w-full p-4 md:overflow-y-auto sm:p-8"
                    :class="{
                        'md:p-16 lg:px-24': !zenMode,
                        'md:p-4': zenMode
                    }"
                    scroll-region
                >
                    <FlashMessages />

                    <slot />
                </div>
            </div>
        </div>

        <ConfirmForm ref="confirmForm" />
    </div>
</template>

<script setup>
import Dropdown from '@/Components/Helpers/Dropdown';
import Icon from '@/Components/Helpers/Icon';
import MainMenu from '@/Components/Helpers/MainMenu';
import ActionMenu from '@/Components/Helpers/ActionMenu';
import FlashMessages from '@/Components/Helpers/FlashMessages';
import ConfirmForm from '@/Components/Forms/ConfirmForm';
import { nextTick, onMounted, ref, watch } from '@vue/runtime-core';
import { useCheckSessionTimeout } from '@/Functions/useCheckSessionTimeout';
import { useRemoveLoader } from '@/Functions/useRemoveLoader';
import { Head, Link, usePage } from '@inertiajs/inertia-vue3';
useCheckSessionTimeout();
useRemoveLoader();
const confirmForm = ref(null);
const mobileMenuVisible = ref(false);
const avatarSrcError = ref(false);
const zenMode = ref(usePage().props.value.user.configs.appearance.zenMode);

const actionClicked = (action) => {
    mobileMenuVisible.value = false;
    nextTick(() => {
        setTimeout(() => {
            usePage().props.value.event.payload = action;
            usePage().props.value.event.name = 'action-clicked';
            usePage().props.value.event.fire = + new Date();
        }, 300); // equal to animate duration
    });
};

const isUrl = (url) => {
    return (location.origin + location.pathname) === url;
};

watch (
    () => usePage().props.value.event.fire,
    (event) => {
        if (! event) {
            return;
        }
        if (usePage().props.value.event.name === 'need-confirm') {
            setTimeout(() => confirmForm.value.open(usePage().props.value.event.payload), 300);
        }
    }
);

let fontScaleIndex = usePage().props.value.user.configs.appearance.fontScaleIndex;
let fontScales = [67, 80, 90, 100];
const scaleFont = (mode) => {
    fontScaleIndex = mode === 'up' ? (fontScaleIndex+1) : (fontScaleIndex-1);
    if (fontScaleIndex > (fontScales.length - 1)) {
        fontScaleIndex = fontScales.length - 1;
    } else if (fontScaleIndex < 0) {
        fontScaleIndex = 0;
    }

    document.querySelector('html').style.fontSize = fontScales[fontScaleIndex] + '%';
};
onMounted(() => {
    let vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
    if (vw >= 768) { // md breakpoint
        document.querySelector('html').style.fontSize = fontScales[fontScaleIndex] + '%';
    }
});
</script>