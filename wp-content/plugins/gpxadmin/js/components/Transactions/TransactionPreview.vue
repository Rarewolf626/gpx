<script setup>
import {ref, onMounted} from 'vue'
import {currency} from "@js/formatters/numbers";
import TransactionDetailsRoom from "@js/components/Transactions/TransactionDetailsRoom.vue";
import TransactionDetailsGuest from "@js/components/Transactions/TransactionDetailsGuest.vue";
import TransactionDetailsDeposit from "@js/components/Transactions/TransactionDetailsDeposit.vue";
import TransactionFees from "@js/components/Transactions/TransactionFees.vue";
import TransactionDetailsGuestModification from "@js/components/Transactions/TransactionDetailsGuestModification.vue";

const emit = defineEmits(['updated']);
const busy = ref(false);
const loaded = ref(false);
const message = ref('');
const errors = ref({});
const transaction = ref({
    id: null,
});
const refund = ref({
    type: 'cancel',
    action: 'credit',
    amount: 0,
});

const reset = () => {
    loaded.value = false;
    message.value = '';
    errors.value = {};
    transaction.value = {
        id: null,
    };
    refund.value = {
        type: 'cancel',
        action: 'credit',
        amount: 0,
    };
}

const open = (transaction_id) => {
    busy.value = true;
    reset();
    transaction.value.id = transaction_id;
    jQuery('#transaction-details-modal').modal('show')
    load();
};

const load = () => {
    busy.value = true;
    axios.get('/gpxadmin/transactions/details/', {params: {transaction: transaction.value.id}})
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

const close = () => {
    jQuery('#transaction-details-modal').modal('hide')
    reset();
}

onMounted(() => {
    jQuery('#transaction-details-modal')
        .on('hidden.bs.modal', (e) => reset());
})

defineExpose({open, close})
</script>

<template>
    <div class="modal fade" id="transaction-details-modal" tabindex="-1" role="dialog"
         aria-labelledby="transaction-details-modal-label">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="transaction-details-modal-label">
                        View Transaction
                    </h4>
                </div>
                <div class="modal-body">
                    <div v-if="busy && !loaded">
                        <i class="fa fa-spinner fa-spin" style="font-size:30px;"></i>
                    </div>
                    <div>
                        <div v-if="message" class="alert alert-danger" v-text="message"/>
                        <div v-if="loaded" class="details">
                            <div class="row details-header">
                                <div class="col-md-6 ">
                                    <h3>Transaction {{ transaction.id }}</h3>
                                    <div v-text="transaction.date"/>
                                </div>
                                <div class="col-md-6">
                                    <div style="text-align:right;">
                                        <div v-if="transaction.cancelled" style="display:inline-block;width:auto;margin-left:auto;">
                                            <h4>Cancelled</h4>
                                            <div>{{ transaction.cancelled_date }}
                                                <span v-if="transaction.cancelled_by">by {{ transaction.cancelled_by }}</span>
                                            </div>
                                            <div v-if="transaction.cancelled_data.origin">cancelled on {{ transaction.cancelled_data.origin }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <transaction-details-guest readonly :transaction="transaction"/>
                                </div>
                                <div class="col-md-4">
                                    <transaction-details-room v-if="transaction.is_booking" :transaction="transaction"/>
                                    <transaction-details-guest-modification v-if="transaction.is_guest" :transaction="transaction"/>
                                    <transaction-details-deposit v-if="transaction.deposit" :transaction="transaction"/>
                                </div>
                                <div class="col-md-4">
                                    <transaction-fees readonly :transaction="transaction"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
