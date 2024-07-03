import {createApp} from 'vue';

(function () {
    window.addEventListener("DOMContentLoaded", (event) => {
        const el = document.getElementById('perks-exchange-credit');
        if (el) {
            import('@js/components/ExchangeCredit.vue').then(ExchangeCredit => {
                const props = JSON.parse(el.dataset.props || '{}') || {};
                createApp(ExchangeCredit.default, props).mount(el);
            });
        }
    });
})();
