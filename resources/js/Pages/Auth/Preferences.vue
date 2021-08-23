<template>
    <SelectHomePage :data="selectHomePage" />
</template>


<script setup>
import SelectHomePage from '@/Components/Forms/SelectHomePage';

const props = defineProps({
    selectHomePage: { type: Object, required: true}
});
</script>

<script>
import Layout from '@/Components/Layouts/Layout';
export default { layout: Layout };
</script>