<script setup>
import {computed} from 'vue';
import {storeToRefs} from 'pinia';
import CheckoutFlexBookingInfo from "@js/components/CheckoutFlexBookingInfo.vue";
import CheckoutRemoveFromCart from "@js/components/CheckoutRemoveFromCart.vue";
import currency from "@js/helpers/currency";
import formatDate from "@js/helpers/date";
import {useCheckoutStore} from "@js/stores/CheckoutStore";
import CheckoutHoldCountdown from "@js/components/CheckoutHoldCountdown.vue";

const {busy, cart, week, checkin, checkout, fees} = storeToRefs(useCheckoutStore());

const props = defineProps({
    showFlex: Boolean,
    showCountdown: Boolean,
})

const emit = defineEmits(['removeFlexBooking','addFlexBooking']);

const feeTotal = computed(() => {
    return cart.value.totals.price + (props.showFlex && fees.value.flex ? cart.value.totals.flex : 0) + cart.value.totals.upgrade + (fees.value.resort_fee.enabled && fees.value.resort_fee.fee > 0 ? fees.value.resort_fee.total : 0);
})


</script>

<template>
    <div class="checkout-week-details ">
        <div
            class="flex flex-col flex-md-row flex-justify-center flex-items-start flex-items-md-stretch gap-md-10 mx-10">
            <ul class="detail-list m-0 w-full">
                <li>
                    <p><strong>Select Week Number</strong></p>
                    <p v-text="week.week_id"/>
                </li>
                <li>
                    <p><strong>Week Type</strong></p>
                    <p v-text="week.week_type_display"/>
                </li>
                <li>
                    <p><strong v-text="week.priceorfee"/></p>
                    <p>
                        <del v-if="cart.totals.price != week.WeekPrice">{{ currency(week.WeekPrice, true) }}</del>
                        {{ currency(cart.totals.price, true) }}
                    </p>
                </li>
                <li v-if="showFlex && fees.flex">
                    <p><strong>Flex Booking</strong></p>
                    <p v-if="cart.flex">
                        <button :disabled="busy" @click.prevent="emit('removeFlexBooking')"
                                class="removeIndCPO btn-plain" title="Remove Flex Booking"><strong>remove</strong>
                        </button>

                        {{ currency(cart.totals.flex, true) }}

                        <checkout-flex-booking-info :fee="cart.totals.flex"/>
                    </p>
                    <p v-else>
                        <button :disabled="busy" @click.prevent="emit('addFlexBooking')" class="addIndCPO btn-plain"
                                title="Add Flex Booking"><strong class="mr-1">add</strong></button>

                        <checkout-flex-booking-info :fee="fees.flex"/>
                    </p>
                </li>

                <li v-if="cart.totals.upgrade">
                    <p><strong>Upgrade Fee</strong></p>
                    <p>{{ currency(cart.totals.upgrade, true) }}</p>
                </li>

                <li v-if="fees.resort_fee?.enabled && fees.resort_fee?.fee > 0">
                    <p><strong>Required Resort Fees</strong><br><em style="font-size:14px;">Payable at the Resort</em></p>
                    <p>{{ currency(fees.resort_fee.total, true) }}</p>
                </li>
                <li style="border-top:solid 1px #000;padding-top:10px;">
                    <p><strong>Total</strong></p>
                    <p style="font-weight:bold;font-size:120%;">{{ currency(feeTotal, true) }}</p>
                </li>


            </ul>
            <ul class="detail-list m-0 w-full">
                <li>
                    <p><strong>Nights</strong></p>
                    <p v-text="week.no_nights"/>
                </li>
                <li>
                    <p><strong>Bedrooms</strong></p>
                    <p v-text="week.bedrooms"/>
                </li>
                <li>
                    <p><strong>Sleep</strong></p>
                    <p v-text="week.sleeps"/>
                </li>
                <li>
                    <p><strong>Check In</strong></p>
                    <p>{{ formatDate(checkin) }}</p>
                </li>
                <li>
                    <p><strong>Check Out</strong></p>
                    <p>{{ formatDate(checkout) }}</p>
                </li>
            </ul>
        </div>
        <checkout-hold-countdown/>
    </div>
</template>
<style scoped>
.detail-list li {
    display: grid;
    grid-template-columns: 60% 40%;
}
</style>
