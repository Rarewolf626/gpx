import {defineStore} from 'pinia';

export const useCheckoutStore = defineStore('CheckoutStore', {
    state: () => {
        return {
            busy: false,
            week: null,
            step: 'review',
            cart: null,
            fees: null,
            hold: null,
        };
    },
    getters: {
        checkin: (state) => {
            if (!state.week || !state.week.checkin) return null;
            return new Date(Date.parse(state.week.checkin));
        },
        checkout: (state) => {
            if (!state.checkin) return null;
            let checkout = new Date(state.checkin.valueOf());
            checkout.setDate(checkout.getDate() + Number(state.week.no_nights || 0));
            return checkout;
        },
        type: (state) => {
            if(!state.cart || !state.cart.type) return null;
            if (['exchange', 'ExchangeWeek', 'Exchange Week'].includes(state.cart.type)) return 'exchange';
            if (['rental', 'RentalWeek', 'Rental Week'].includes(state.cart.type)) return 'rental';
            if (['extend', 'extension', 'ExtendWeek', 'Extend Week'].includes(state.cart.type)) return 'extend';
            if (['deposit', 'DepositWeek', 'Deposit Week'].includes(state.cart.type)) return 'deposit';
            return null;
        },
        isExchange: (state) => {
            return state.type === 'exchange';
        },
        isBooking: (state) => {
            return ['rental','exchange'].includes(state.type);
        }
    },
    actions: {
        setWeek(week) {
            this.week = week;
        },
        setFees(fees) {
            this.fees = fees;
        },
        setHold(hold) {
            this.hold = hold ? new Date(Date.parse(hold)) : null;
        },
        validateStep(step) {
            if (['review'].includes(step)) return true;
            if (step === 'exchange') {
                return ['exchange', 'rental'].includes(this.type);
            }
            if (step === 'payment') {
                if (['exchange', 'rental'].includes(this.type)) {
                    if (!this.cart.guest.has_guest) return false;
                }
                if (this.type === 'exchange') {
                    if (!this.cart.exchange.type) return false;
                    if (!this.cart.exchange.deposit && this.cart.exchange.type === 'deposit') return false;
                    if (!this.cart.exchange.credit && this.cart.exchange.type === 'credit') return false;
                }
                return true;
            }
            return false;
        },
        setStep(value, scroll = false) {
            if (value === this.step) return;
            if (!this.validateStep(value)) {
                return;
            }

            this.step = value;
            if (scroll) {
                window.scrollTo({top: 0, behavior: 'smooth'});
            }
        },
        setBusy(value) {
            this.busy = !!value;
        },
        setCart(cart) {
            this.cart = cart;
        },
        removeFromCart() {
            if (this.busy) return;
            this.busy = true;
            axios.post('/wp-admin/admin-ajax.php?action=gpx_delete_cart').then(response => {
                if (response.data.refresh || response.data.rr === 'refresh') {
                    window.location.reload();
                } else if (response.data.redirect) {
                    window.location = response.data.redirect
                } else if (response.data.rr === 'redirect') {
                    window.location = '/';
                }
            }).catch(error => {
                this.busy = false;
                window.location = '/';
            });
        },
    }
});
