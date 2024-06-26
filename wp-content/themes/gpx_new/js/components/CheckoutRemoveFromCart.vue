<script setup>
import {storeToRefs} from 'pinia';
import {useCheckoutStore} from "@js/stores/CheckoutStore";

const checkoutStore = useCheckoutStore();
const {busy} = storeToRefs(checkoutStore);

</script>

<template>
    <form method="post" @submit.prevent="checkoutStore.removeFromCart">
        <button :disabled="busy" class="btn-plain checkout-remove-from-cart" type="submit">
            Remove From Cart <i class="icon-close"></i>
        </button>
    </form>
</template>
