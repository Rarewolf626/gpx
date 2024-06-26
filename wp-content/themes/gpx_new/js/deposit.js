import {createApp} from 'vue';
import DepositWeek from '@js/components/DepositWeek.vue';

(function () {
    window.addEventListener("DOMContentLoaded", (event) => {
        const el = document.getElementById('deposit-form');
        if (el) {
            const app = createApp(DepositWeek).mount(el);
            document.addEventListener('click', event => {
                if((event.target.tagName.toLowerCase() === 'a' || event.target.tagName.toLowerCase() === 'button') && event.target.classList.contains('deposit-modal')) {
                    event.preventDefault();
                    event.stopPropagation();
                    app.load();
                }
            });
        }
    });
})();
