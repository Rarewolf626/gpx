<script setup>
import CheckoutRemoveHold from "@js/components/CheckoutRemoveHold.vue";

const props = defineProps({
    title: {
        type: String,
        default: 'Your Next Vacation'
    },
});
</script>

<template>
    <div class="dgt-container flex flex-justify-between flex-items-center">
        <div>
            <h3 v-text="title" />
        </div>
        <div>
            <slot name="action">
                <checkout-remove-hold />
            </slot>
        </div>
    </div>
</template>

<style scoped>
.flex {
    padding: 40px 0 20px 0;
    font-size: 18px;
}
h3 {
    font-family: 'montserratregular', sans-serif;
    color: #152136;
    text-transform: capitalize;
    font-size: 22px;
    margin: 0;
    line-height: 40px;
}
</style>
