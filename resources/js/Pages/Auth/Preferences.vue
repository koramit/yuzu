<template>
    <SelectHomePage :configs="selectHomePage" />
    <LinkMocktail :configs="linkMocktail" />
    <SetupNotification />
</template>


<script setup>
import SelectHomePage from '@/Components/Forms/SelectHomePage';
import LinkMocktail from '@/Components/Forms/LinkMocktail';
import SetupNotification from '@/Components/Forms/SetupNotification';

defineProps({
    selectHomePage: { type: Object, required: true},
    linkMocktail: { type: Object, required: true}
});
</script>

<script>
import Layout from '@/Components/Layouts/Layout';
export default { layout: Layout };
</script>