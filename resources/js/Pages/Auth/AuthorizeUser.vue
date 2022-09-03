<template>
    <div class="bg-white rounded shadow-sm p-4 mb-4 sm:mb-6 md:mb-12 mx-auto">
        <h2 class="font-semibold text-thick-theme-light border-b-2 border-dashed pb-2">
            แพทย์ไม่มีรหัส SIIT
        </h2>
        <div
            class="mt-4 md:mt-6 sm:grid grid-cols-3 border rounded sm:border-none p-2 sm:p-0 space-y-4 sm:space-y-0"
            v-for="user in formUsers"
            :key="user.id"
        >
            <p>{{ user.full_name }}</p>
            <p>{{ user.login }}</p>
            <FormCheckbox
                :toggler="true"
                :name="user.login"
                v-model="user.md"
                label="เปิดสิทธิ์แพทย์"
                @autosave="authorize(user)"
            />
        </div>
    </div>
</template>

<script setup>
import {ref} from '@vue/reactivity';
import FormCheckbox from '../../Components/Controls/FormCheckbox';

const props = defineProps({
    users: {type: Array, required: true}
});

const formUsers = ref([...props.users]);
const authorize = (user) => {
    console.log(user);
    window.axios
        .patch(user.update_route)
        .then(res => console.log(res.data))
        .catch(error => console.log(error));
};
</script>

<script>
import Layout from '@/Components/Layouts/Layout';
export default { layout: Layout };
</script>
