<script setup>
const props = defineProps({
    transaction: {
        type: Object,
        required: true
    },
})
</script>

<template>
    <div class="well">
        <h3>Deposit</h3>
        <table class="table table-details w-auto">
            <tbody>
            <tr>
                <th>Ref No:</th>
                <td v-text="transaction.deposit.id"/>
            </tr>
            <tr>
                <th>Resort Name:</th>
                <td v-text="transaction.deposit.resort"/>
            </tr>
            <tr>
                <th>Deposit Year:</th>
                <td v-text="transaction.deposit.year"/>
            </tr>
            <tr>
                <th>Unit:</th>
                <td v-text="transaction.deposit.unit"/>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>

</style>
