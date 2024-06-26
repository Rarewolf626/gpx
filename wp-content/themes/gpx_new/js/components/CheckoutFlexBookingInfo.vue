<script setup>
import {ref} from 'vue';
import currency from "@js/helpers/currency";

const props = defineProps({
    fee: Number,
})

const message = ref('message');

const show = () => {
    window.alertModal.alert(message.value.innerHTML, true);
};

</script>

<template>
    <button type="button" class="btn-plain" @click.prevent="show">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
    </button>

    <div ref="message" style="display: none;">
        <p>Grand Pacific Exchange offers the option of purchasing Flex Booking for an additional fee of
            {{ currency(fee) }} paid
            concurrently when booking an Exchange provided that the Exchange is made more than 45 days from the Check-In
            Date.
            If purchased, Flex Booking covers you in the event you are not able to utilize your reserved vacation and
            need to
            request cancellation of your Exchange. Provided your cancellation is submitted more than forty-five (45)
            days prior
            to the Confirmed Exchange Check-In date, under Flex Booking (i) the original Exchange Credit will be
            returned to the
            your GPX Account and will expire two (2) years from the original Exchange Credit start date; and (ii) the
            Exchange and
            any Upgrade Fees paid for the Confirmed Exchange will be refunded in the form of a coupon code valid for one
            year that
            may be applied towards your next Exchange or Rental Booking. If at the time of the new booking, the cost
            exceeds the
            amount of the coupon code, the GPX Member must pay the incremental increase. Flex Booking may be purchased
            for any Exchange.
            No monetary refunds are distributed for cancellations at any time.</p>
        <p>Flex Booking cannot be used to cancel a Confirmed Exchange and then re-book the same Resort Week as either a
            Rental Week or
            Additional Benefit.</p>
        <p>Flex Booking is optional. Members who decline Flex Booking in connection with a Confirmed Exchange will
            forfeit their Exchange
            Fee upon cancellation of a Confirmed Exchange, which includes any change in dates, unit type, vacation area
            or Resorts. Flex
            Booking is not available for Rental Weeks including, without limitation, special or promotional offers.</p>
    </div>
</template>
