<script setup>
const props = defineProps({
    transaction: {
        type: Object,
        required: true
    },
})
</script>

<template>
    <div  class="well">
        <h3>Resort / Room Info</h3>
        <table class="table table-details w-auto">
            <tbody>
            <tr>
                <th>Ref No:</th>
                <td v-text="transaction.week"/>
            </tr>
            <tr>
                <th>Resort Name:</th>
                <td v-text="transaction.resort"/>
            </tr>
            <tr>
                <th>Size:</th>
                <td v-text="transaction.size"/>
            </tr>
            <tr>
                <th>Check In:</th>
                <td v-text="transaction.checkin"/>
            </tr>
            <tr>
                <th>Nights:</th>
                <td v-text="transaction.nights"/>
            </tr>
            </tbody>
        </table>
    </div>
</template>

