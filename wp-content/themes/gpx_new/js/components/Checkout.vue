<script setup>
import {ref, computed, onMounted} from 'vue';
import {storeToRefs} from 'pinia';
import {useCheckoutStore} from "@js/stores/CheckoutStore";
import CheckoutReview from "@js/components/CheckoutReview.vue";
import CheckoutExchange from "@js/components/CheckoutExchange.vue";
import CheckoutPayment from "@js/components/CheckoutPayment.vue";
import CheckoutNotAvailable from "@js/components/CheckoutNotAvailable.vue";

const props = defineProps({
    payment: Boolean,
    week: Object,
    user: Object,
    owners: Array,
    cart: Object,
    exchange: Object,
    hold: String,
    terms: {
        type: Array,
        default: () => [],
    },
    ownerships: Array,
    credits: Array,
    fees: Object,
    error: String,
    alert: String,
})

const available = computed(() => {
    return props.error !== 'notavailable';
});

const checkoutStore = useCheckoutStore();
const {step} = storeToRefs(checkoutStore);

checkoutStore.setFees(props.fees);
checkoutStore.setWeek(props.week);
checkoutStore.setCart(props.cart);
checkoutStore.setHold(props.hold);

onMounted(() => {
    if (props.alert) {
        window.alertModal.alert(props.alert);
    }
})

</script>
<template>
    <div>
        <div v-if="!payment">
            <checkout-not-available
                v-if="!available"
                :terms="terms"
                :error="!!alert"
            />
            <div v-if="available">
                <checkout-review
                    v-if="step === 'review'"
                    :terms="terms"
                    :error="!!alert"
                />
                <checkout-exchange
                    v-if="step === 'exchange'"
                    :user="user"
                    :owners="owners"
                    :credits="credits"
                    :ownerships="ownerships"
                    :latefees="fees"
                    :error="error"
                />
            </div>
        </div>
        <checkout-payment
            v-if="payment"
            :user="user"
            :terms="terms"
            :error="!!error"
        />
    </div>
</template>

