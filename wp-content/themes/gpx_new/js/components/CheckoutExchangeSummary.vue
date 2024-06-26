<script setup>
import {storeToRefs} from 'pinia';
import {useCheckoutStore} from "@js/stores/CheckoutStore";
import formatDate from "@js/helpers/date";

const {cart} = storeToRefs(useCheckoutStore());

</script>

<template>
    <div class="w-full">
        <div v-if="cart.exchange.type === 'credit'">
            <h3>Exchange Credit</h3>

            <ul class="detail-list m-0">
                <li>
                    <p><strong>Resort</strong></p>
                    <p>{{ cart.credit.resort }}</p>
                </li>
                <li>
                    <p><strong>Expires</strong></p>
                    <p>{{ formatDate(cart.credit.expires) }}</p>
                </li>
                <li>
                    <p><strong>Entitlement Year</strong></p>
                    <p>{{ cart.credit.year }}</p>
                </li>
                <li>
                    <p><strong>Size</strong></p>
                    <p>{{ cart.credit.size }}</p>
                </li>
            </ul>
        </div>
        <div v-if="cart.exchange.type === 'deposit'">
            <h3>Exchange New Deposit</h3>

            <ul class="detail-list m-0 w-full">
                <li>
                    <p><strong>Resort</strong></p>
                    <p>{{ cart.interval.resort }}</p>
                </li>
                <li>
                    <p><strong>Unit Type</strong></p>
                    <p>{{ cart.exchange.unit_type || cart.interval.unit_type }}</p>
                </li>
                <li v-if="cart.interval.week_type">
                    <p><strong>Week Type</strong></p>
                    <p>{{ cart.interval.week_type }}</p>
                </li>
                <li v-if="cart.interval.contract">
                    <p><strong>Resort Member Number</strong></p>
                    <p>{{ cart.interval.contract }}</p>
                </li>
                <li v-if="cart.interval.year">
                    <p><strong>Last Year Banked</strong></p>
                    <p>{{ cart.interval.year }}</p>
                </li>
                <li>
                    <p><strong>Check In Date</strong></p>
                    <p>{{ formatDate(cart.exchange.date) }}</p>
                </li>
                <li v-if="cart.exchange.reservation">
                    <p><strong>Reservation Number</strong></p>
                    <p>{{ cart.exchange.reservation }}</p>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>

</style>
