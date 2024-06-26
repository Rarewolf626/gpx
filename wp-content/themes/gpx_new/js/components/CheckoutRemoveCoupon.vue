<script setup>
import {storeToRefs} from 'pinia';
import {useCheckoutStore} from "@js/stores/CheckoutStore";

const checkoutStore = useCheckoutStore();
const {busy} = storeToRefs(checkoutStore);

const props = defineProps({
    type: {
        type: String,
        required: true,
        validator: (value) => ['coupon', 'owner'].includes(value)
    }
})

const submit = () => {
    if(busy.value) return;
    busy.value = true;
    checkoutStore.setBusy(true);
    axios.post('/wp-admin/admin-ajax.php?action=gpx_cart_remove_coupon', {type: props.type})
        .then(response => {
            if(response.data.success) {
                checkoutStore.setCart(response.data.cart);
            }
            checkoutStore.setBusy(false);
        })
        .catch(error => {
            checkoutStore.setBusy(false);
        });
}
</script>

<template>
    <form @submit.prevent="submit" style="display: inline-block;">
        <button type="submit" class="btn-plain">
            <i class="fa fa-times-circle" aria-hidden="true" :title="type === 'owner' ? 'Remove Credit' : 'Remove Coupon'"></i>
        </button>
    </form>
</template>

