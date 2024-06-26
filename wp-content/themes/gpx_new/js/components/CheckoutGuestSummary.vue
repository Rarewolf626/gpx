<script setup>
import {storeToRefs} from 'pinia';
import {useCheckoutStore} from "@js/stores/CheckoutStore";

const {cart, isExchange} = storeToRefs(useCheckoutStore());
</script>

<template>
    <div :class="{'w-full': isExchange}">
        <h3>Guest Information</h3>
        <ul class="detail-list m-0">
            <li>
                <p><strong>Name</strong></p>
                <p>{{ cart.guest.first_name }} {{ cart.guest.last_name }}</p>
            </li>
            <li v-if="cart.guest.email">
                <p><strong>Email</strong></p>
                <p class="whitespace-nowrap">{{ cart.guest.email }}</p>
            </li>
            <li v-if="cart.guest.phone">
                <p><strong>Phone</strong></p>
                <p class="whitespace-nowrap">{{ cart.guest.phone }}</p>
            </li>
            <li>
                <p><strong>Adults</strong></p>
                <p>{{ cart.guest.adults }}</p>
            </li>
            <li>
                <p><strong>Children</strong></p>
                <p>{{ cart.guest.children }}</p>
            </li>
            <li v-if="cart.guest.special_request">
                <p><strong>Special Request</strong></p>
                <div class="whitespace-prewrap">{{ cart.guest.special_request }}</div>
            </li>
        </ul>
    </div>
</template>

<style scoped>

</style>
