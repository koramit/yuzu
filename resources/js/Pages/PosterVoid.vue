<template>
    <Head>
        <title>YUZU Quality Fair 2022</title>
    </Head>
    <div class="w-full min-h-screen flex flex-col items-center justify-center p-4">
        <h1 class="text-dark-theme-light font-semibold text-2xl">
            YUZU 🍊 <span class="text-thick-theme-light italic">:</span> Mahidol Quality fair 2022
        </h1>
        <p class="mt-6 md:mt-8 text-thick-theme-light font-semibold text-lg text-center">
            เปิดให้เข้าชมวันที่ 25<span class="text-dark-theme-light italic">-</span> 30 พฤศจิกายน 2022
        </p>
    </div>
</template>

<script setup>
import { useCheckSessionTimeout } from '@/Functions/useCheckSessionTimeout';
import { useRemoveLoader } from '@/Functions/useRemoveLoader';

useCheckSessionTimeout();
useRemoveLoader();
</script>

<style scoped>

</style>
