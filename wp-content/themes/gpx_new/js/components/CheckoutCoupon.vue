<script setup>
import {ref, computed} from 'vue';
import {storeToRefs} from 'pinia';
import {useCheckoutStore} from "@js/stores/CheckoutStore";

const checkoutStore = useCheckoutStore();
const {busy} = storeToRefs(checkoutStore);

const code = ref('');
const message = ref(null);
const success = ref(false);

const valid = computed(() => code.value !== null);

const submit = () => {
    if (busy.value || !valid.value) return;
    message.value = null;
    checkoutStore.setBusy(true);
    axios.post('/wp-admin/admin-ajax.php?action=gpx_cart_add_coupon', {coupon: code.value}).then(response => {
        success.value = response.data.success || false;
        if (response.data.success) {
            checkoutStore.setCart(response.data.cart);
            message.value = response.data.message;
            code.value = '';
        } else {
            message.value = response.data.message || 'This coupon is invalid';
        }
        checkoutStore.setBusy(false);
    }).catch(error => {
        success.value = false;
        message.value = error?.response.data.message || 'This coupon is invalid';
        checkoutStore.setBusy(false);
    })
};

</script>

<template>
    <div class="promotional">
        <div class="bk-path-headline"><h3>Coupon Code</h3></div>
        <form action="" class="w-cnt" @submit.prevent="submit">
            <div class="flex flex-col flex-sm-row flex-justify-center flex-items-center gap-4">
                <div class="field-wrapper">
                    <label for="couponCode" class="sr-only">Coupon Code</label>
                    <input type="text" id="couponCode" name="couponCode" placeholder="Enter a Coupon Code"
                           v-model.trim="code" required>
                </div>
                <button :disabled="!valid" type="submit" class="dgt-btn">
                    Submit
                </button>
            </div>
            <div class="loading text-center mt-2" v-show="busy">
                <i class="fa fa-spinner fa-spin "></i>
            </div>
            <div v-if="message" class="text-center mt-2" :class="{'form-error': !success, 'form-success': success}" v-text="message"/>
        </form>
    </div>
</template>
<style scoped>
input[type=text] {
    padding: 8px 15px;
    width: 225px;
    color: #333;
}

button {
    width: 225px;
    padding: 5px 75px;
    border: 1px solid #333;
    color: #333;
    background-color: transparent;
    font-size: 16px;
    transition: all .45s ease;
    text-align: center;
}

button:hover {
    background-color: #009ad6;
    border: 1px solid #009ad6;
    color: #fff;
}

.loading {
    font-size: 22px;
}
</style>
