<template>
    <SelectHomePage :configs="selectHomePage" />
    <LayoutAppearance :configs="layoutAppearance" />
    <LinkMocktail :configs="linkMocktail" />
    <!-- <SetupNotification :configs="setupNotification" />
    <ManageNotification :configs="manageNotification" /> -->
    <LinkPatient :configs="linkPatient" />
</template>


<script setup>
import SelectHomePage from '@/Components/Forms/SelectHomePage';
import LayoutAppearance from '@/Components/Forms/LayoutAppearance';
import LinkMocktail from '@/Components/Forms/LinkMocktail';
import SetupNotification from '@/Components/Forms/SetupNotification';
import ManageNotification from '@/Components/Forms/ManageNotification';
import LinkPatient from '@/Components/Forms/LinkPatient';

defineProps({
    selectHomePage: { type: Object, required: true},
    layoutAppearance: { type: Object, required: true},
    linkMocktail: { type: Object, required: true},
    setupNotification: { type: Object, required: true},
    manageNotification: { type: Object, required: true},
    linkPatient: { type: Object, required: true},
});
</script>

<script>
import Layout from '@/Components/Layouts/Layout';
export default { layout: Layout };
</script>