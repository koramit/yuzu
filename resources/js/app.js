window._ = require('lodash');

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import mitt from 'mitt';

const emitter = mitt();

InertiaProgress.init({
    delay: 200,
    color: '#AD9C68'
});

createInertiaApp({
    resolve: name => require(`./Pages/${name}`), // single file
    // resolve: name => import(`./Pages/${name}`), // split code: use extra request
    setup({ el, app, props, plugin }) {
        createApp({ render: () => h(app, props) })
            .use(plugin)
            .mixin({ methods: { route: window.route } }) // enable route() on template
            .provide('emitter', emitter)
            .mount(el);
    },
});
