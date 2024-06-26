<script setup>
import {storeToRefs} from 'pinia';
import {useCheckoutStore} from "@js/stores/CheckoutStore";

const checkoutStore = useCheckoutStore();
const {busy} = storeToRefs(checkoutStore);

</script>

<template>
    <form method="get" @submit.prevent="checkoutStore.removeFromCart">
        <button :disabled="busy" type="submit" class="btn-plain">
            <h3><span>Cancel and Start New Search </span> <i class="icon-close"></i></h3>
        </button>
    </form>
</template>

<style scoped>
h3 {
    color: black;
    text-align: right;
    font-size: 18px;
    font-weight: 100;
    font-family: 'ralewayregular', sans-serif;
    text-transform: none;
    display: inline-flex;
    justify-content: start;
    align-items: center;
}
.icon-close {
    font-size: 26px;
}
</style>
