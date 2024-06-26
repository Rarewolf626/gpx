<script setup lang="ts">
import {ref, onMounted} from 'vue'
import type {TransactionDetails} from "@js/types/transaction";
import TransactionDetailsRoom from "@js/components/Transactions/TransactionDetailsRoom.vue";
import TransactionDetailsGuest from "@js/components/Transactions/TransactionDetailsGuest.vue";
import TransactionDetailsDeposit from "@js/components/Transactions/TransactionDetailsDeposit.vue";
import TransactionFees from "@js/components/Transactions/TransactionFees.vue";
import TransactionCancel from "@js/components/Transactions/TransactionCancel.vue";
import TransactionDetailsGuestModification from "@js/components/Transactions/TransactionDetailsGuestModification.vue";
import TransactionDetailsRelated from "@js/components/Transactions/TransactionDetailsRelated.vue";

const props = defineProps({
    transaction_id: {
        type: Number,
        required: true
    },
})
const busy = ref(false);
const loaded = ref(false);
const transaction = ref<TransactionDetails>();
const message = ref('');

const setBusy = (value) => {
    busy.value = value;
};


const load = () => {
    busy.value = true;
    axios.get(`/gpxadmin/transactions_details/`, {params: {transaction: props.transaction_id}})
        .then(response => {
            if (response.data.success) {
                transaction.value = response.data.transaction;
                loaded.value = true;
            } else {
                message.value = response.data.message || 'An error occurred while retrieving the transaction details.';
            }
            busy.value = false;
        })
        .catch(error => {
            message.value = error.response?.data.message || 'An error occurred while retrieving the transaction details.';
            busy.value = false;
        });
};

onMounted(() => {
    load();
})

</script>

<template>
    <div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h2 class="panel-title" style="font-size:20px;">Transaction {{ transaction_id }}</h2>
                    <div v-text="transaction?.date"/>
                </div>

                <div v-if="loaded" class="transaction-details__status">
                    <div v-if="transaction.cancelled" class="transaction-details__cancelled">
                        <h4>Cancelled</h4>
                        <div>{{ transaction.cancelled_date }} by {{ transaction.cancelled_by }}</div>
                    </div>
                    <div class="transaction-details__refund">
                        <transaction-cancel :transaction="transaction" :busy="busy" @busy="setBusy" @updated="load" />
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div v-if="!loaded && busy" class="text-center">
                <i style="font-size:100px;" class="fa fa-spinner fa-spin"></i>
            </div>
            <div v-if="loaded" class="row">
                <div class="row">
                    <div class="col-md-4">
                      <transaction-details-guest :readonly="false" :transaction="transaction" :busy="busy" @busy="setBusy" @updated="load" />
                    </div>
                    <div class="col-md-4">
                        <transaction-details-room v-if="transaction.is_booking" :transaction="transaction"/>
                        <transaction-details-guest-modification v-if="transaction.is_guest" :transaction="transaction"/>
                        <transaction-details-deposit v-if="transaction.deposit" :transaction="transaction"/>
                    </div>
                    <div class="col-md-4">
                        <transaction-fees :readonly="false" :busy="busy" @busy="setBusy" :transaction="transaction" @updated="load" />
                    </div>
                </div>
            </div>
        </div>
    </div>

        <transaction-details-related v-if="loaded && transaction.related_transaction_count > 0" :transaction_id="transaction_id" />
    </div>
</template>
