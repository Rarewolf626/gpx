<script setup>
import {storeToRefs} from 'pinia';
import CheckoutHeading from "@js/components/CheckoutHeading.vue";
import {useCheckoutStore} from "@js/stores/CheckoutStore";
import CheckoutResortDetails from "@js/components/CheckoutResortDetails.vue";
import CheckoutWeekDetails from "@js/components/CheckoutWeekDetails.vue";

const checkoutStore = useCheckoutStore();
const {busy, cart, week} = storeToRefs(checkoutStore);

</script>

<template>
    <section class="booking booking-active">
        <checkout-heading title="Week is no longer available"/>
        <div class="w-featured bg-gray-light w-result-home">
            <div class="w-list-view dgt-container">
                <div class="w-item-view filtered">
                    <checkout-resort-details/>
                    <checkout-week-details>
                        <template v-slot:remove>
                            <a
                                href="#"
                                class="btn btn--dark custom-request"
                                :data-pid="cart.weekid"
                                :data-cid="cart.cid"
                            >Submit Custom Request</a>
                        </template>
                    </checkout-week-details>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
a.btn--dark {
    color: #fff;
}
</style>
