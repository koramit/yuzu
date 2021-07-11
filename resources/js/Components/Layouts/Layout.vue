<template>
    <InertiaHead>
        <title>{{ $page.props.flash.title }}</title>
    </InertiaHead>
    <div>
        <!-- main contailner, flex makes its childs extend full h -->
        <div class="md:h-screen md:flex md:flex-col">
            <!-- this is navbar, with no shrink (fixed width) -->
            <div class="md:flex md:flex-shrink-0 sticky top-0 z-30">
                <!-- left navbar on desktop and full bar on mobile -->
                <div class="bg-dark-theme-light text-white md:flex-shrink-0 md:w-56 xl:w-64 px-4 py-2 flex items-center justify-between md:justify-center">
                    <!-- the logo -->
                    <inertia-link
                        class="inline-block"
                        :href="route('home')"
                    >
                        <span class="font-bold text-lg md:text-2xl">Yuzu</span>
                    </inertia-link>
                    <!-- title display on mobile -->
                    <div class="text-soft-theme-light text-sm truncate mx-1 md:hidden">
                        {{ $page.props.flash.title }}
                    </div>
                    <!-- hotel menu on mobile -->
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
                <!-- right navbar on desktop -->
                <div class="hidden md:flex w-full font-semibold text-dark-theme-light bg-alt-theme-light border-b sticky top-0 z-30 p-4 md:py-0 md:px-12 justify-between items-center">
                    <!-- title display on desktop -->
                    <div class="mt-1 mr-4">
                        {{ $page.props.flash.title }}
                    </div>
                    <!-- username and menu -->
                    <dropdown>
                        <template #default>
                            <div class="flex items-center cursor-pointer select-none group">
                                <div class="group-hover:text-bitter-theme-light focus:text-bitter-theme-light mr-1 whitespace-no-wrap">
                                    <span>{{ $page.props.user.name }}</span>
                                </div>
                                <icon
                                    class="w-4 h-4 group-hover:text-bitter-theme-light focus:text-bitter-theme-light"
                                    name="double-down"
                                />
                            </div>
                        </template>
                        <template #dropdown>
                            <div class="mt-2 py-2 shadow-xl min-w-max bg-thick-theme-light text-white cursor-pointer rounded text-sm">
                                <template v-if="hasRoles">
                                    <inertia-link
                                        class="block px-6 py-2 hover:bg-dark-theme-light hover:text-soft-theme-light"
                                        :href="route('home')"
                                        v-if="! currentPage('home')"
                                    >
                                        หน้าหลัก
                                    </inertia-link>
                                </template>
                                <inertia-link
                                    class="w-full font-semibold text-left px-6 py-2 hover:bg-dark-theme-light hover:text-soft-theme-light"
                                    :href="route('logout')"
                                    method="delete"
                                    as="button"
                                    type="button"
                                >
                                    ออกจากระบบ
                                </inertia-link>
                            </div>
                        </template>
                    </dropdown>
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
                            <template v-if="hasRoles">
                                <inertia-link
                                    class="block py-1"
                                    :href="route('home')"
                                    v-if="! currentPage('home')"
                                >
                                    หน้าหลัก
                                </inertia-link>
                            </template>
                            <inertia-link
                                class="block py-1"
                                :href="route('logout')"
                                method="delete"
                                as="button"
                                type="button"
                            >
                                ออกจากระบบ
                            </inertia-link>
                        </div>
                        <hr class="my-4">
                        <main-menu
                            @click="mobileMenuVisible = false"
                            :url="url()"
                        />
                        <action-menu @action-clicked="actionClicked" />
                    </div>
                </div>
            </div>
            <!-- this is content -->
            <div class="md:flex md:flex-grow md:overflow-hidden">
                <!-- this is sidebar menu on desktop -->
                <div class="hidden md:block bg-thick-theme-light flex-shrink-0 w-56 xl:w-64 py-12 px-6 overflow-y-auto">
                    <!-- class="hidden md:block bg-thick-theme-light flex-shrink-0 w-56 xl:w-64 py-12 px-6 overflow-y-auto" -->
                    <main-menu
                        :url="url()"
                    />
                    <!-- class="hidden md:block bg-thick-theme-light flex-shrink-0 w-56 xl:w-64 py-12 px-6 overflow-y-auto" -->
                    <action-menu
                        @action-clicked="actionClicked"
                    />
                </div>
                <!-- this is main page -->
                <div
                    class="w-full p-4 md:overflow-y-auto sm:p-8 md:p-16 lg:px-24"
                    scroll-region
                >
                    <flash-messages />

                    <slot />
                </div>
            </div>
        </div>

        <confirm-form ref="confirmForm" />
    </div>
</template>

<script>
import Dropdown from '@/Components/Helpers/Dropdown';
import Icon from '@/Components/Helpers/Icon';
import MainMenu from '@/Components/Helpers/MainMenu';
import ActionMenu from '@/Components/Helpers/ActionMenu';
import FlashMessages from '@/Components/Helpers/FlashMessages';
import ConfirmForm from '@/Components/Forms/ConfirmForm';
import { computed, inject, nextTick, ref } from '@vue/runtime-core';
import { useCheckSessionTimeout } from '@/Functions/useCheckSessionTimeout';
import { useRemoveLoader } from '@/Functions/useRemoveLoader';
export default {
    components: { Dropdown, Icon, MainMenu, ActionMenu, FlashMessages, ConfirmForm },
    setup () {
        useCheckSessionTimeout();

        const pageLoadingIndicator = document.getElementById('page-loading-indicator');
        if (pageLoadingIndicator) {
            useRemoveLoader();
        }

        const confirmForm = ref(null);
        const mobileMenuVisible = ref(false);
        const avatarSrcError = ref(false);

        const hasRoles = computed(() => {
            return false;
        });

        const emitter = inject('emitter');

        emitter.on('need-confirm', (cinfigs) => {
            setTimeout(() => nextTick(() => confirmForm.value.open(cinfigs)), 300);
        });

        const url = () => {
            return location.pathname.substr(1);
        };

        const actionClicked = (action) => {
            mobileMenuVisible.value = false;
            nextTick(() => {
                setTimeout(() => {
                    emitter.emit('action-clicked', action);
                }, 300); // equal to animate duration
            });
        };

        const currentPage = (route) => {
            return location.pathname.substr(1) === route;
        };

        return {
            confirmForm,
            mobileMenuVisible,
            avatarSrcError,
            hasRoles,
            url,
            actionClicked,
            currentPage,
        };
    },
};
</script>