import {createApp} from 'vue';
import { createPinia } from 'pinia';

(function () {
    window.addEventListener("DOMContentLoaded", (event) => {
        const el = document.getElementById('checkout-app');
        if (el) {
            import('@js/components/Checkout.vue').then(Checkout => {
                const app = createApp(Checkout.default, {
                    payment: el.hasAttribute('data-payment') && el.dataset.payment !== 'false',
                    week: JSON.parse(el.dataset.week || '{}'),
                    cart: JSON.parse(el.dataset.cart),
                    user: JSON.parse(el.dataset.user),
                    owners: JSON.parse(el.dataset.owners || '[]'),
                    hold: el.dataset.hold || null,
                    terms: JSON.parse(el.dataset.terms || '[]'),
                    exchange: JSON.parse(el.dataset.exchange || '{}'),
                    ownerships: JSON.parse(el.dataset.ownerships || '[]'),
                    credits: JSON.parse(el.dataset.credits || '[]'),
                    fees: JSON.parse(el.dataset.fees),
                    error: el.dataset.error || '',
                    alert: el.dataset.alert || '',
                }).use(createPinia()).mount(el);
            });
        }
    });
})();
