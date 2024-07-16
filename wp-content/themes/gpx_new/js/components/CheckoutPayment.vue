<script setup>
import {ref, watch} from 'vue';
import {storeToRefs} from 'pinia';
import CheckoutResortDetails from "@js/components/CheckoutResortDetails.vue";
import CheckoutWeekDetails from "@js/components/CheckoutWeekDetails.vue";
import CheckoutCoupon from "@js/components/CheckoutCoupon.vue";
import CheckoutPaymentTotals from "@js/components/CheckoutPaymentTotals.vue";
import CheckoutPaymentTerms from "@js/components/CheckoutPaymentTerms.vue";
import CheckoutHeading from "@js/components/CheckoutHeading.vue";
import CheckoutBookingSummary from "@js/components/CheckoutBookingSummary.vue";
import CountrySelect from "@js/components/CountrySelect.vue";
import MonthSelect from "@js/components/MonthSelect.vue";
import YearSelect from "@js/components/YearSelect.vue";
import {useCheckoutStore} from "@js/stores/CheckoutStore";
import EmailValidator from 'email-validator';
import CardValidator from 'card-validator';
import CheckoutRemoveFromCart from "@js/components/CheckoutRemoveFromCart.vue";

const props = defineProps({
    user: Object,
    error: Boolean,
    terms: {
        type: [Array, String],
        default: '',
    },
})

const checkoutStore = useCheckoutStore();
const {busy, cart, week, fees, isExchange, isBooking} = storeToRefs(checkoutStore);

const agree = ref(false);
const onfile = ref(false);
const processing = ref(false);
const result = ref({
    complete: false,
    success: false,
    message: '',
});

const initialBilling = {
    first_name: '',
    last_name: '',
    address: '',
    city: '',
    state: '',
    zip: '',
    country: props.user.country,
    email: '',
};

const billing = ref({
    name: '',
    address: '',
    city: '',
    state: '',
    zip: '',
    country: props.user.country,
    email: '',
});

const card = ref({
    number: '',
    cvv2: '',
    month: null,
    year: null,
});

watch(
    () => onfile.value,
    (value) => {
        billing.value = value ? {
            name: props.user.name,
            email: props.user.email,
            address: props.user.address,
            city: props.user.city,
            state: props.user.state,
            zip: props.user.zip,
            country: props.user.country,
        } : initialBilling;
        validation.value = {};
    }
)

const removeFlexBooking = () => {
    return setFlexBooking(false);
};

const addFlexBooking = () => {
    return setFlexBooking(true);
};

const setFlexBooking = (flex) => {
    if (busy.value) return;
    checkoutStore.setBusy(true);
    axios.post('/wp-admin/admin-ajax.php?action=gpx_checkout_flex_fee', {flex: flex})
        .then(response => {
            checkoutStore.setCart(response.data.cart);
            checkoutStore.setBusy(false);
        })
        .catch(error => {
            checkoutStore.setBusy(false);
        });
};

const validation = ref({});
const validate = () => {
    let valid = true;
    validation.value = {};
    if (cart.value.totals.total > 0) {
        if (billing.value.address === '') {
            valid = false;
            validation.value.address = 'Street address is required';
        }
        if (billing.value.city === '') {
            valid = false;
            validation.value.city = 'City is required';
        }
        if (billing.value.state === '') {
            valid = false;
            validation.value.state = 'State is required';
        }
        if (billing.value.zip === '') {
            valid = false;
            validation.value.zip = 'Postal code is required';
        } else if (!CardValidator.postalCode(billing.value.zip).isValid) {
            valid = false;
            validation.value.zip = 'Postal code is invalid';
        }
        if (billing.value.country === '') {
            valid = false;
            validation.value.country = 'Country is required';
        }
        if (billing.value.email === '') {
            valid = false;
            validation.value.email = 'Email is required';
        } else if (!EmailValidator.validate(billing.value.email)) {
            valid = false;
            validation.value.email = 'Email is invalid';
        }
        if (billing.value.name === '') {
            valid = false;
            validation.value.name = 'Name is required';
        }

        if (card.value.number === '') {
            valid = false;
            validation.value.number = 'Card number is required';
        } else if (!CardValidator.number(card.value.number).isValid) {
            valid = false;
            validation.value.number = 'Card number is invalid';
            //console.log(card);
        }
        if (card.value.cvv2 === '') {
            valid = false;
            validation.value.cvv2 = 'CVV is required';
        }
        // remove cvv validation
       //else if (!CardValidator.cvv(card.value.cvv2).isValid) {
       //     valid = false;
       //     validation.value.cvv2 = 'CVV is invalid';
            //console.log(card);
       // }
        if (!card.value.month || !card.value.year) {
            valid = false;
            validation.value.expires = 'Expiration date is required';
        } else if (!CardValidator.expirationDate({
            month: String(card.value.month),
            year: String(card.value.year)
        }).isValid) {
            valid = false;
            validation.value.expires = 'Expiration date is invalid';
        }
    }
    if (!agree.value) {
        valid = false;
        validation.value.agree = 'You must agree to the terms';
    }

    return valid;
}

const submit = async () => {
    if (busy.value || result.complete) return;
    if (!validate()) return;
    checkoutStore.setBusy(true);
    processing.value = true;
    if (cart.value.totals.total <= 0) {
        return await processOrder({});
    }
    return await generateAccessToken();
};

const genericProcessErrorMessage = 'Unable To Process Your Request At This Time. Please Try Again Later.';

const generateAccessToken = async () => {
    // Generate an access block for the i4go api
    try {
        const response = await axios.post('/wp-admin/admin-ajax.php?action=gpx_i4go_auth');
        if (!response.data.success) {
            const error = new Error(response.data.message || genericProcessErrorMessage);
            error.response = response;
            throw error;
        }
        return await authorizePayment(response.data.access);
    } catch (error) {
        result.value = {
            complete: false,
            success: false,
            message: error.response?.data?.i4go_responsetext || genericProcessErrorMessage
        };
        processing.value = false;
        checkoutStore.setBusy(false);
    }
};
const authorizePayment = async (access) => {
    // send the credit card to the i4go server for authorization
    try {
        const response = await axios.post(access.i4go_server, new URLSearchParams({
            fuseaction: 'api.jsonPostCardEntry',
            i4go_accessblock: access.i4go_accessblock,
            i4go_cardholdername: billing.value.name,
            i4go_streetaddress: billing.value.address,
            i4go_cardnumber: card.value.number,
            i4go_expirationmonth: card.value.month,
            i4go_expirationyear: card.value.year,
            i4go_cvv2code: card.value.cvv2,
            i4go_cvv2indicator: 0,
            i4go_postalcode: billing.value.zip
        }));
        if (response.data?.i4go_responsecode !== '1') {
            const error = new Error(response.data.i4go_responsetext || response.data.message || genericProcessErrorMessage);
            error.response = response;
            throw error;
        }
        return await processOrder(response.data);
    } catch (error) {
        result.value = {
            complete: false,
            success: false,
            message: error?.response?.data?.i4go_responsetext || error?.response?.data?.message || genericProcessErrorMessage
        };
        processing.value = false;
        checkoutStore.setBusy(false);
    }
};
const processOrder = async (payment) => {
    // process the payment
    try {
        const response = await axios.post('/wp-admin/admin-ajax.php?action=gpx_i4go_process', {
            cart: cart.value.cartid,
            amount: cart.value.totals.total,
            payment: payment,
            billing: billing.value
        });
        if (!response.data.success) {
            const error = new Error(response.data.message || genericProcessErrorMessage);
            error.response = response;
            throw error;
        }
        result.value = {
            complete: true,
            success: true,
            message: response.data.message || 'Transaction processed.'
        };
        processing.value = false;
        if (response.data.redirect) {
            window.location = response.data.redirect;
        }
    } catch (error) {
        processing.value = false;
        if (error?.response.status === 422) {
            if (error.response.data?.errors) {
                error.response.data.errors.forEach((messages, field) => validation.value[field] = messages[0]);
            }
        }
        if (error?.response.data.redirect) {
            window.alertModal.alert(
                error.response.data.message,
                false,
                (event) => window.location.href = error.response.data.redirect
            );
            return;
        }

        result.value = {
            complete: false,
            success: false,
            message: error?.response.data.message || genericProcessErrorMessage
        };
        checkoutStore.setBusy(false);
    }
};


</script>

<template>
    <section class="booking booking-payment booking-active" id="booking-3">
        <checkout-heading v-if="week.week_id"/>
        <div class="w-featured bg-gray-light w-result-home">
            <div class="w-list-view dgt-container">
                <div class="checkhold" :data-pid="week.week_id" :data-cid="cart.cid" :data-type="cart.type"></div>
                <div class="w-item-view filtered">
                    <checkout-resort-details v-if="week.week_id"/>
                    <checkout-week-details
                        v-if="isBooking && week.week_id"
                        :show-flex="isExchange && fees.show_flex"
                        show-countdown
                        @addFlexBooking="addFlexBooking"
                        @removeFlexBooking="removeFlexBooking"
                    />
                    <checkout-booking-summary v-if="isBooking && week.week_id"/>
                </div>

                <checkout-coupon :cid="cart.cid"/>

                <form class="payment-form" method="post" @submit.prevent="submit" novalidate>
                    <div class="payment">
                        <h3>Payment</h3>
                        <div class="w-cnt">
                            <div v-if="!isBooking || !week.week_id" class="text-right">
                                <checkout-remove-from-cart/>
                            </div>

                            <div class="grid grid-cols-1 grid-cols-md-2 gap-6">
                                <checkout-payment-totals class="order-md-1 w-full" @removeFlexBooking="removeFlexBooking"/>
                                <div class="order-md-0 w-full">
                                    <div v-if="cart.totals.total > 0">
                                        <div class="carts">
                                            <div class="check check--onfile">
                                                <input type="checkbox" id="onfile" v-model="onfile" :disabled="busy">
                                                <label class="font-weight-bold" for="onfile">Use Address on File</label>
                                            </div>
                                            <div class="mb-2">
                                                <img src="@img/payment.png" alt="amex, visa, mastercard">
                                            </div>
                                            <div class="payment-form w-full grid grid-cols-1 gap-2">
                                                <fieldset class="w-full grid grid-cols-1 gap-2" :disabled="busy">
                                                    <div v-if="cart.totals.total <= 0" class="">
                                                        <label for="billing_cardholder"
                                                               class="control-label required">Name</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            :class="{'has-error': !!validation.name}"
                                                            id="billing_cardholder"
                                                            name="billing_name"
                                                            v-model.trim="billing.name"
                                                            autocomplete="off"
                                                            required
                                                            @input="delete validation.name"
                                                        >
                                                        <div v-if="validation.name" class="form-error"
                                                             v-text="validation.name"/>
                                                    </div>
                                                    <div class="">
                                                        <label for="billing_address" class="control-label required">Street
                                                            Address</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            :class="{'has-error': !!validation.address}"
                                                            name="billing_address"
                                                            id="billing_address"
                                                            autocomplete="off"
                                                            v-model.trim="billing.address"
                                                            required
                                                            @input="delete validation.address"
                                                        >
                                                        <div v-if="validation.address" class="form-error"
                                                             v-text="validation.address"/>
                                                    </div>
                                                    <div class="">
                                                        <label for="billing_city"
                                                               class="control-label required">City</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            :class="{'has-error': !!validation.city}"
                                                            name="billing_city"
                                                            id="billing_city"
                                                            v-model.trim="billing.city"
                                                            autocomplete="off"
                                                            required
                                                            @input="delete validation.city"
                                                        >
                                                        <div v-if="validation.city" class="form-error"
                                                             v-text="validation.city"/>
                                                    </div>

                                                    <div class="">
                                                        <label for="billing_state"
                                                               class="control-label required">State</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            :class="{'has-error': !!validation.state}"
                                                            placeholder="State"
                                                            id="billing_state"
                                                            v-model.trim="billing.state"
                                                            autocomplete="off"
                                                            required
                                                            @input="delete validation.state"
                                                        >
                                                        <div v-if="validation.state" class="form-error"
                                                             v-text="validation.state"/>
                                                    </div>

                                                    <div class="">
                                                        <label for="billing_zip" class="control-label required">Post /
                                                            Zip
                                                            Code</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            :class="{'has-error': !!validation.zip}"
                                                            name="billing_zip"
                                                            id="billing_zip"
                                                            v-model.trim="billing.zip"
                                                            autocomplete="off"
                                                            required
                                                            @input="delete validation.zip"
                                                        >
                                                        <div v-if="validation.zip" class="form-error"
                                                             v-text="validation.zip"/>
                                                    </div>

                                                    <div class="">
                                                        <label for="billing_country"
                                                               class="control-label required">Country</label>
                                                        <country-select id="billing_country" name="billing_country"
                                                                        class="form-control"
                                                                        :class="{'has-error': !!validation.country}"
                                                                        required
                                                                        v-model="billing.country"
                                                                        @input="delete validation.country"
                                                        />
                                                        <div v-if="validation.country" class="form-error"
                                                             v-text="validation.country"/>
                                                    </div>

                                                    <div class="">
                                                        <label for="billing_email"
                                                               class="control-label required">Email</label>
                                                        <input
                                                            type="email"
                                                            class="form-control"
                                                            :class="{'has-error': !!validation.email}"
                                                            name="billing_email"
                                                            id="billing_email"
                                                            v-model.trim="billing.email"
                                                            autocomplete="off"
                                                            required
                                                            @input="delete validation.email"
                                                        >
                                                        <div v-if="validation.email" class="form-error"
                                                             v-text="validation.email"/>
                                                    </div>
                                                </fieldset>
                                                <fieldset v-if="cart.totals.total > 0"
                                                          class="w-full grid grid-cols-1 gap-2"
                                                          :disabled="busy">
                                                    <div class="">
                                                        <label for="billing_cardholder" class="control-label required">Cardholder
                                                            Name</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            :class="{'has-error': !!validation.name}"
                                                            id="billing_cardholder"
                                                            name="billing_name"
                                                            v-model.trim="billing.name"
                                                            autocomplete="off"
                                                            required
                                                            @input="delete validation.name"
                                                        >
                                                        <div v-if="validation.name" class="form-error"
                                                             v-text="validation.name"/>
                                                    </div>

                                                    <div class="">
                                                        <label for="billing_number" class="control-label required">Cardholder
                                                            Number</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            :class="{'has-error': !!validation.number}"
                                                            id="billing_number"
                                                            v-model.trim="card.number"
                                                            autocomplete="off"
                                                            required
                                                            @input="delete validation.number"
                                                        >
                                                        <div v-if="validation.number" class="form-error"
                                                             v-text="validation.number"/>
                                                    </div>

                                                    <div class="">
                                                        <label for="billing_ccv"
                                                               class="control-label required">CVV</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            :class="{'has-error': !!validation.cvv2}"
                                                            id="billing_ccv"
                                                            v-model.trim="card.cvv2"
                                                            autocomplete="off"
                                                            required
                                                            @input="delete validation.cvv2"
                                                        >
                                                        <div v-if="validation.cvv2" class="form-error"
                                                             v-text="validation.cvv2"/>
                                                    </div>

                                                    <div class="ginput_container ginput_date">
                                                        <label for="billing_month" class="control-label required">Expiration
                                                            Date</label>
                                                        <div class="flex flex-justify-stretch flex-items-start gap-2">
                                                            <div class="w-1/2">
                                                                <label for="billing_month" class="sr-only">Expiration
                                                                    Month</label>
                                                                <month-select id="billing_month" class="form-control"
                                                                              v-model.number="card.month" required
                                                                              :class="{'has-error': !!validation.expires}"
                                                                              @input="delete validation.expires"
                                                                />
                                                            </div>
                                                            <div class="w-1/2">
                                                                <label for="billing_year" class="sr-only">Expiration
                                                                    Year</label>
                                                                <year-select id="billing_year" class="form-control"
                                                                             v-model.number="card.year"
                                                                             :class="{'has-error': !!validation.expires}"
                                                                             required
                                                                             @input="delete validation.expires"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div v-if="validation.expires" class="form-error"
                                                             v-text="validation.expires"/>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="payment-agree-wrapper check--agree">
                        <div>
                            <label for="payment-agree" class="form-label" :class="{'has-error': !!validation.agree}">
                                <div class="flex flex-justify-start flex-items-center text-left">
                                    <span class="form-checkbox">
                                    <input :disabled="busy" type="checkbox" id="payment-agree" v-model="agree"
                                           :class="{'has-error': !!validation.agree}">
                                </span>
                                    <span
                                        class="agree-label m-0">I have reviewed and understand the terms and conditions below</span>

                                </div>
                            </label>
                        </div>
                        <div>
                            <button :disabled="busy" type="submit" class="btn-submit"
                                    id="next-3">
                                Pay & Confirm
                                <i v-show="processing" class="fa fa-refresh fa-spin fa-fw"></i>
                            </button>
                        </div>
                    </div>
                    <div v-if="validation.agree" class="form-error text-center" v-text="validation.agree"/>
                    <div
                        v-if="result.message"
                        class="payment-result"
                        :class="{'form-success': result.success, 'form-error': !result.success}"
                        v-text="result.message"
                    />
                    <CheckoutPaymentTerms :terms="terms"/>
                </form>

            </div>
        </div>
    </section>
</template>
<style scoped>
.payment-form {
    padding-bottom: 80px;
}

.payment-agree-wrapper {
    margin-top: 20px;
    display: flex;
    justify-content: end;
    align-items: center;
}

.agree-label {
    font-size: 18px;
    font-weight: bold;
}

.btn-submit {
    font-weight: bold;
    margin-left: 100px;
    font-size: 18px;
}

.check--onfile {
    margin: 0;
    padding: 20px 0;

}

.payment-form .control-label {
    display: block;
}

.payment-form input[type=text],
.payment-form input[type=email],
.payment-form input[type=tel],
.payment-form select {
    width: 100%;
    background-color: transparent;
}

.payment-result {
    font-family: "montserratregular", sans-serif;
    font-size: 22px;
    text-align: center;
    margin-top: 20px;
    background-color: white;
    border: solid 1px black;
    padding: 20px;
}

.payment-result.form-success {
    border-color: darkgreen;
}

.payment-result.form-error {
    border-color: darkred;
}
</style>
