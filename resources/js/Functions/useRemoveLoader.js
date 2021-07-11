import { nextTick, onMounted } from 'vue';

export function useRemoveLoader() {
    onMounted(() => {
        nextTick(() => {
            const pageLoadingIndicator = document.getElementById('page-loading-indicator');
            if (pageLoadingIndicator) {
                setTimeout(() => pageLoadingIndicator.remove(), 1500);
            }
        });
    });
}