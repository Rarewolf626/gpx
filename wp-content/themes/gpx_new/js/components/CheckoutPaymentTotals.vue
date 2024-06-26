<script setup>
import {computed} from 'vue';
import {storeToRefs} from 'pinia';
import currency from "@js/helpers/currency";
import CheckoutFlexBookingInfo from "@js/components/CheckoutFlexBookingInfo.vue";
import CheckoutRemoveCoupon from "@js/components/CheckoutRemoveCoupon.vue";
import {useCheckoutStore} from "@js/stores/CheckoutStore";

const checkoutStore = useCheckoutStore();
const {cart, week, isBooking, fees} = storeToRefs(checkoutStore);

const emit = defineEmits(['removeFlexBooking']);

const total = computed(() => {
    return cart.value.totals.price
        + (fees.value.resort_fee.enabled && fees.value.resort_fee.fee > 0 ? fees.value.resort_fee.total : 0)
        + (cart.value.flex ? cart.value.totals.flex : 0)
        + cart.value.totals.upgrade
        + cart.value.totals.extension
        + cart.value.totals.guest
        + cart.value.totals.late
        + cart.value.totals.third_party
        + cart.value.totals.tax
        - cart.value.totals.coupon
        - cart.value.totals.occredit
        - cart.value.totals.credit
        ;
});

</script>

<template>
    <ul class="w-list-details">
        <li>
            <div class="gtitle">
                <span>Payment Details</span>
            </div>
        </li>
        <li v-if="isBooking">
            <p>Booking <strong v-text="week.ResortName" /></p>
        </li>
        <li v-if="isBooking">
            <div class="result">
                <p>
                    Room Rate:
                    <del v-if="cart.totals.price != week.WeekPrice">{{ currency(week.WeekPrice) }}</del>
                    {{ currency(cart.totals.price) }}
                </p>
            </div>
        </li>
        <li v-if="cart.totals.credit">
            <div class="result">
                <p>Account Credit {{ currency(cart.totals.credit) }}</p>
            </div>
        </li>
        <li v-if="cart.flex">
            <div class="result">
                <p>
                    <button type="button" class="btn-plain" title="Remove Flex Booking" @click.prevent="emit('removeFlexBooking')"><strong>remove</strong></button>
                    Flex Booking
                    {{ currency(cart.totals.flex) }}
                    <checkout-flex-booking-info :fee="cart.totals.flex" />
                </p>
            </div>
        </li>
        <li v-if="cart.totals.upgrade">
            <div class="result">
                <p>
                    Upgrade Fee
                    {{ currency(cart.totals.upgrade) }}
                </p>
            </div>
        </li>
        <li v-if="cart.totals.extension">
            <div class="result">
                <p>
                    Credit Extension Fee
                    {{ currency(cart.totals.extension) }}
                </p>
            </div>
        </li>
        <li v-if="cart.totals.guest">
            <div class="result">
                <p>
                    Guest Fee:
                    {{ currency(cart.totals.guest) }}
                </p>
            </div>
        </li>
        <li v-if="cart.totals.late">
            <div class="result">
                <p>
                    Late Deposit Fee:
                    {{ currency(cart.totals.late) }}
                </p>
            </div>
        </li>
        <li v-if="cart.totals.third_party">
            <div class="result">
                <p>
                    Third Party Deposit Fee:
                    {{ currency(cart.totals.third_party) }}
                </p>
            </div>
        </li>
        <li v-if="cart.totals.coupon">
            <div class="result">
                <p>
                    Discount: {{ currency(cart.totals.coupon) }}
                    <CheckoutRemoveCoupon type="coupon" />
                </p>
            </div>
        </li>
        <li v-if="cart.totals.tax">
            <div class="result">
                <p>
                    Tax: {{ currency(cart.totals.tax) }}
                </p>
            </div>
        </li>
        <li v-if="cart.totals.occredit">
            <div class="result">
                <p>
                    Credit Used: {{ currency(cart.totals.occredit) }}
                    <CheckoutRemoveCoupon type="owner" />
                </p>
            </div>
        </li>
        <li v-if="fees.resort_fee?.enabled && fees.resort_fee?.fee > 0">
            <div class="result">
                <p style="margin-bottom:0;">
                    Required Resort Fees: {{ currency(fees.resort_fee.total) }}
                </p>
                <p style="margin-top:0;font-size:14px;font-style:italic;">Payable at the resort</p>
            </div>
        </li>
        <li>
            <div class="result">
                <p>Total: {{ currency(total) }}</p>
            </div>
        </li>
        <li v-if="isBooking && fees.resort_fee?.enabled && fees.resort_fee?.fee > 0">
            <div class="result noline">
                <p>Total includes required fees due at the resort, calculated for your convenience.</p>
            </div>
        </li>
        <li>
            <div class="result total" style="text-align:center;">
                <p style="font-size:20px;margin-bottom:0;">Due Today to Confirm Booking:</p>
                <p style="margin-top:0;">{{ currency(cart.totals.total) }}</p>
            </div>
        </li>
        <li v-if="cart.totals.total > 0">
            <div class="message">
                <p>This charge will show on your credit card statement as <strong>Grand Pacific Exchange</strong></p>
            </div>
        </li>
    </ul>
</template>

