<script setup>
import {storeToRefs} from 'pinia';
import CheckoutGuestSummary from "@js/components/CheckoutGuestSummary.vue";
import CheckoutExchangeSummary from "@js/components/CheckoutExchangeSummary.vue";
import {useCheckoutStore} from "@js/stores/CheckoutStore";

const {busy, cart, isExchange} = storeToRefs(useCheckoutStore());
</script>

<template>
    <div class="view-detail px-6 pb-6 pt-2 booking-summary">
        <div class="ml-auto mr-1 mb-4 text-right">
            <a :href="`/booking-path/?book=${cart.weekid}&type=${cart.type}`" :disabled="busy" class="checkout-summary-back btn-plain">
                <i class="fa fa-edit"></i>
                Change Details
            </a>
        </div>
        <div class="flex flex-col flex-md-row flex-items-start flex-items-md-stretch gap-md-10 mx-10" :class="{'flex-justify-center': isExchange, 'flex-justify-start' : !isExchange}">
            <checkout-exchange-summary v-if="isExchange" />
            <checkout-guest-summary />
        </div>
    </div>
</template>
