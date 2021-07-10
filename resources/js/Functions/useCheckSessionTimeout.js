import { nextTick, onMounted, onUnmounted } from 'vue';

export function useCheckSessionTimeout() {
    var lastTimeCheckSessionTimeout = Date.now();

    const endpoint = window.route('check-timeout');
    const sessionLifetimeSeconds = parseInt(document.querySelector('meta[name=session-lifetime-seconds]').content);
    const checkSessionTimeoutOnFocus = () => {
        let timeDiff = Date.now() - lastTimeCheckSessionTimeout;
        if ((timeDiff) > (sessionLifetimeSeconds)) {
            window.axios
                .post(endpoint)
                .then(() => lastTimeCheckSessionTimeout = Date.now())
                .catch(() => location.reload());
        }
    };

    onMounted(() => {
        window.addEventListener('focus', checkSessionTimeoutOnFocus);

        nextTick(() => {
            const pageLoadingIndicator = document.getElementById('page-loading-indicator');
            if (pageLoadingIndicator) {
                setTimeout(() => pageLoadingIndicator.remove(), 1500);
            }
        });
    });

    onUnmounted(() => {
        window.removeEventListener('focus', checkSessionTimeoutOnFocus);
    });
}