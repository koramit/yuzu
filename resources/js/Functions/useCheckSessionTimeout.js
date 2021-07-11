import { onMounted, onUnmounted } from 'vue';

export function useCheckSessionTimeout() {
    var lastTimeCheckSessionTimeout = Date.now();
    const sessionLifetimeSeconds = parseInt(document.querySelector('meta[name=session-lifetime-seconds]').content);
    const checkSessionTimeoutOnFocus = () => {
        let timeDiff = Date.now() - lastTimeCheckSessionTimeout;
        if ((timeDiff) > (sessionLifetimeSeconds)) {
            window.axios
                .post(window.route('check-timeout'))
                .then(() => lastTimeCheckSessionTimeout = Date.now())
                .catch(() => location.reload());
        }
    };

    onMounted(() => {
        window.addEventListener('focus', checkSessionTimeoutOnFocus);
    });

    onUnmounted(() => {
        window.removeEventListener('focus', checkSessionTimeoutOnFocus);
    });
}