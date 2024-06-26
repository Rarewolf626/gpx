import {createApp} from 'vue';
import ExtendCredit from '@js/components/ExtendCredit.vue';

(function () {
    window.addEventListener("DOMContentLoaded", (event) => {
        const el = document.getElementById('extend-credit');
        if (el) {
            const app = createApp(ExtendCredit).mount(el);
            window.ExtendCredit = app;
        }
    });
})();
