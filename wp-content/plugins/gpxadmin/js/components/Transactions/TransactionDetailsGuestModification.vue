<script setup>
const props = defineProps({
    transaction: {
        type: Object,
        required: true
    },
})
</script>

<template>
    <div v-if="transaction.is_guest" class="well">
        <h3>Original Transaction</h3>
        <div style="margin-bottom:10px;">
            This is a guest fee charged for modifying the guest for another transaction.
        </div>
        <div v-if="transaction.parent_transaction">
            <a class="btn btn-info" :href="transaction.parent_transaction_url">View Original Transaction</a>
        </div>
        <div v-else class="alert alert-error">
            Original Booking Information not available. Transaction prior to checkup upgrade.
        </div>
    </div>
</template>

