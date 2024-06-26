<script setup>
import {ref} from 'vue'
import TransactionGuest from "@js/components/Transactions/TransactionGuest.vue";

const props = defineProps({
    transaction: {
        type: Object,
        required: true
    },
    readonly: {
        type: Boolean,
        default: false
    },
    busy: {
        type: Boolean,
        default: false
    },
})
const emit = defineEmits(['updated','busy']);

const guest = ref(null);
const showGuestData = () => {
    guest.value.open(props.transaction.id);
};
const updated = () => {
    emit('updated');
};

</script>

<template>
    <div class="well">
        <h3 v-text="transaction.has_guest ? 'Guest Info' : 'Member Info'" />
        <table class="table table-details w-auto">
            <tbody>
            <tr>
                <th>Member Number:</th>
                <td v-text="transaction.user_id"/>
            </tr>
            <tr>
                <th>Member Name:</th>
                <td v-text="transaction.member"/>
            </tr>
            <tr v-if="transaction.has_guest">
                <th>Guest Name:</th>
                <td>
                    <button v-if="!readonly && transaction.is_booking" class="btn btn-default btn-plain" type="button" @click.prevent="showGuestData">
                        <i class="fa fa-edit" style="margin-right:5px;"></i>
                        <span v-text="transaction.guest" />
                    </button>
                    <span v-else v-text="transaction.guest" />
                </td>
            </tr>
            <tr v-if="transaction.has_guest">
                <th>Adults:</th>
                <td v-text="transaction.adults"/>
            </tr>
            <tr v-if="transaction.has_guest">
                <th>Children:</th>
                <td v-text="transaction.children"/>
            </tr>
            <tr v-if="transaction.special_request">
                <th>Special Request:</th>
                <td>
                    <div v-text="transaction.special_request" style="width:100%;white-space:pre-wrap;overflow:auto;"></div>
                </td>
            </tr>
            </tbody>
        </table>

        <transaction-guest ref="guest" v-if="!readonly" @updated="updated" />
    </div>
</template>
