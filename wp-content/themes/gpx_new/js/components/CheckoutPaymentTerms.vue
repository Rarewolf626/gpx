<script setup>
import {computed} from 'vue';
const props = defineProps({
    terms: {
        type: [Array, String],
        default: '',
    },
})
const content = computed(() => {
    if (Array.isArray(props.terms)) return props.terms.join("\n");
    return props.terms;
})
</script>

<template>
    <div class="payment-terms" v-if="content" v-text="content"></div>
</template>
<style scoped>
.payment-terms {
    font-size: 16px;
    margin-top: 60px;
    font-weight: bold;
}
</style>
