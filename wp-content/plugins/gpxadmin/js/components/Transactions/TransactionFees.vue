<script setup>
import {ref, onMounted, computed} from 'vue'
import {currency} from "@js/formatters/numbers";

const props = defineProps({
    transaction: {
        type: Object,
        required: true
    },
    readonly: {
        type: Boolean,
        default: true
    },
    busy: {
        type: Boolean,
        default: false
    },
})
const emit = defineEmits(['updated', 'busy']);

const loaded = ref(false);
const message = ref('');
const fee = ref({
    type: '',
    original: 0.00,
    amount: 0.00,
    action: 'credit',
    label: '',
});

const open = (type) => {
    if (props.readonly) return;
    message.value = '';
    switch (type) {
        case 'booking':
            fee.value = {
                type: 'booking',
                original: props.transaction.fees.booking.original,
                refunded: props.transaction.fees.booking.amount,
                balance: props.transaction.fees.booking.balance,
                amount: Math.min(props.transaction.fees.booking.balance, props.transaction.fees.balance),
                action: 'credit',
                label: 'Exchange/Rental Fee',
            };
            break;
        case 'cpo':
            fee.value = {
                type: 'cpo',
                original: props.transaction.fees.cpo.original,
                refunded: props.transaction.fees.cpo.amount,
                balance: props.transaction.fees.cpo.balance,
                amount: props.transaction.fees.cpo.balance,
                action: 'credit',
                label: 'Flex Booking Fee',
            };
            break;
        case 'upgrade':
            fee.value = {
                type: 'upgrade',
                original: props.transaction.fees.upgrade.original,
                refunded: props.transaction.fees.upgrade.amount,
                balance: props.transaction.fees.upgrade.balance,
                amount: props.transaction.fees.upgrade.balance,
                action: 'credit',
                label: 'Upgrade Fee',
            };
            break;
        case 'guest':
            fee.value = {
                type: 'guest',
                original: props.transaction.fees.guest.original,
                refunded: props.transaction.fees.guest.amount,
                balance: props.transaction.fees.guest.balance,
                amount: props.transaction.fees.guest.balance,
                action: 'credit',
                label: 'Guest Fee',
            };
            break;
        case 'late':
            fee.value = {
                type: 'late',
                original: props.transaction.fees.late.original,
                refunded: props.transaction.fees.late.amount,
                balance: props.transaction.fees.late.balance,
                amount: props.transaction.fees.late.balance,
                action: 'credit',
                label: 'Late Deposit Fee',
            };
            break;
        case 'third_party':
            fee.value = {
                type: 'third_party',
                original: props.transaction.fees.third_party.original,
                refunded: props.transaction.fees.third_party.amount,
                balance: props.transaction.fees.third_party.balance,
                amount: props.transaction.fees.third_party.balance,
                action: 'credit',
                label: 'Third Party Deposit Fee',
            };
            break;
        case 'extension':
            fee.value = {
                type: 'extension',
                original: props.transaction.fees.extension.original,
                refunded: props.transaction.fees.extension.amount,
                balance: props.transaction.fees.extension.balance,
                amount: props.transaction.fees.extension.balance,
                action: 'credit',
                label: 'Credit Extension Fee',
            };
            break;
        case 'tax':
            fee.value = {
                type: 'tax',
                original: props.transaction.fees.tax.original,
                refunded: props.transaction.fees.tax.amount,
                balance: props.transaction.fees.tax.balance,
                amount: props.transaction.fees.tax.balance,
                action: 'credit',
                label: 'Tax Charged',
            };
            break;
        default:
            reset();
            throw new Error('Invalid fee type');
    }
    jQuery('#transaction-fee-modal').modal('show');
}

const close = () => {
    jQuery('#transaction-fee-modal').modal('hide');
    message.value = '';
    reset();
}

const reset = () => {
    fee.value = {
        type: '',
        original: 0.00,
        refunded: 0.00,
        balance: 0.00,
        amount: 0.00,
        action: 'credit',
        label: '',
    };
}

const valid = computed(() => {
    if (fee.value.type === '') return false;
    if (fee.value.amount <= 0) return false;
    if (fee.value.amount > fee.value.balance) return false;
    return true;
});

const submit = () => {
    if (props.readonly || !valid.value || props.busy) return;
    emit('busy', true);
    message.value = '';
    axios.post('/gpxadmin/transactions/fee/refund/', {
        transaction: props.transaction.id,
        type: fee.value.type,
        amount: fee.value.amount,
        action: fee.value.action
    })
        .then(response => {
            if (response.data.success) {
                emit('updated', response.data.transaction);
                close();
            } else {
                message.value = response.data.message || 'An error occurred while updating the fee.';
                emit('busy', false);
            }
        })
        .catch(error => {
            message.value = error.response?.data.message || 'An error occurred while updating the fee.';
            emit('busy', false);
        });
}

onMounted(() => {
    jQuery('#transaction-fee-modal')
        .on('hidden.bs.modal', (e) => reset());
})

</script>

<template>
    <div class="well">
        <h3>Fees</h3>
        <div v-if="transaction.fees.refunds.other > 0" class="alert alert-danger">
            Please note that refunds completed before 06/27/2024 will show refunds/credits to total paid values but will not reflect refunds at the line item level.
        </div>
        <table class="table table-details w-auto">
            <thead>
            <tr>
                <th>Fee</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Refunded</th>
                <th class="text-right">Balance</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th>
                    Exchange/Rental Fee:
                </th>
                <td class="text-right">
                    {{ currency(transaction.fees.booking.original) }}
                </td>
                <td class="text-right">{{ currency(transaction.fees.booking.amount * -1) }}</td>
                <td class="text-right">
                    <button
                        v-if="!readonly && transaction.is_admin && transaction.fees.booking.balance > 0 && transaction.fees.balance > 0"
                        @click.prevent="open('booking')" type="button" class="btn btn-plain"><i
                        class="fa fa-pencil"></i></button>
                    {{ currency(transaction.fees.booking.balance) }}
                </td>
            </tr>

            <tr v-if="transaction.fees.cpo.original">
                <th>Flex Booking Fee:</th>
                <td class="text-right">
                    {{ currency(transaction.fees.cpo.original) }}
                </td>
                <td class="text-right">
                    {{ currency(transaction.fees.cpo.amount * -1) }}
                </td>
                <td class="text-right">
                    <button
                        v-if="!readonly && transaction.is_admin && transaction.fees.cpo.balance > 0 && transaction.fees.balance > 0"
                        @click.prevent="open('cpo')" type="button" class="btn btn-plain"><i
                        class="fa fa-pencil"></i></button>
                    {{ currency(transaction.fees.cpo.balance) }}
                </td>
            </tr>
            <tr v-if="transaction.fees.upgrade.original">
                <th>Upgrade Fee:</th>
                <td class="text-right">
                    {{ currency(transaction.fees.upgrade.original) }}
                </td>
                <td class="text-right">
                    {{ currency(transaction.fees.upgrade.amount * -1) }}
                </td>
                <td class="text-right">
                    <button
                        v-if="!readonly && transaction.is_admin && transaction.fees.upgrade.balance > 0 && transaction.fees.balance > 0"
                        @click.prevent="open('upgrade')" type="button" class="btn btn-plain"><i
                        class="fa fa-pencil"></i></button>
                    {{ currency(transaction.fees.upgrade.balance) }}
                </td>
            </tr>
            <tr v-if="transaction.fees.guest.original">
                <th>Guest Fee:</th>
                <td class="text-right">
                    {{ currency(transaction.fees.guest.original) }}
                </td>
                <td class="text-right">
                    {{ currency(transaction.fees.guest.amount * -1) }}
                </td>
                <td class="text-right">
                    <button
                        v-if="!readonly && transaction.is_admin && transaction.fees.guest.balance > 0 && transaction.fees.balance > 0"
                        @click.prevent="open('guest')" type="button" class="btn btn-plain"><i
                        class="fa fa-pencil"></i></button>
                    {{ currency(transaction.fees.guest.balance) }}
                </td>
            </tr>
            <tr v-if="transaction.fees.late.original">
                <th>Late Deposit Fee:</th>
                <td class="text-right">
                    {{ currency(transaction.fees.late.original) }}
                </td>

                <td class="text-right">
                    {{ currency(transaction.fees.late.amount * -1) }}
                </td>

                <td class="text-right">
                    <button
                        v-if="!readonly && transaction.is_admin && transaction.fees.late.balance > 0 && transaction.fees.balance > 0"
                        @click.prevent="open('late')" type="button" class="btn btn-plain"><i
                        class="fa fa-pencil"></i></button>
                    {{ currency(transaction.fees.late.balance) }}
                </td>
            </tr>
            <tr v-if="transaction.fees.third_party.original">
                <th>Third Party Deposit Fee:</th>
                <td class="text-right">
                    {{ currency(transaction.fees.third_party.original) }}
                </td>

                <td class="text-right">
                    {{ currency(transaction.fees.third_party.amount * -1) }}
                </td>

                <td class="text-right">
                    <button
                        v-if="!readonly && transaction.is_admin && transaction.fees.third_party.balance > 0 && transaction.fees.balance > 0"
                        @click.prevent="open('third_party')" type="button" class="btn btn-plain"><i
                        class="fa fa-pencil"></i></button>
                    {{ currency(transaction.fees.third_party.balance) }}
                </td>
            </tr>
            <tr v-if="transaction.fees.extension.original">
                <th>Credit Extension Fee:</th>
                <td class="text-right">
                    {{ currency(transaction.fees.extension.original) }}
                </td>
                <td class="text-right">
                    {{ currency(transaction.fees.extension.amount * -1) }}
                </td>
                <td class="text-right">
                    <button
                        v-if="!readonly && transaction.is_admin && transaction.fees.extension.balance > 0 && transaction.fees.balance > 0"
                        @click.prevent="open('extension')" type="button" class="btn btn-plain"><i
                        class="fa fa-pencil"></i></button>
                    {{ currency(transaction.fees.extension.balance) }}
                </td>
            </tr>
            <tr v-if="transaction.fees.tax.original">
                <th>Tax Charged:</th>
                <td class="text-right">
                    {{ currency(transaction.fees.tax.original) }}
                </td>
                <td class="text-right">
                    {{ currency(transaction.fees.tax.amount * -1) }}
                </td>
                <td class="text-right">
                    <button
                        v-if="!readonly && transaction.is_admin && transaction.fees.tax.balance > 0 && transaction.fees.balance > 0"
                        @click.prevent="open('tax')" type="button" class="btn btn-plain"><i
                        class="fa fa-pencil"></i></button>
                    {{ currency(transaction.fees.tax.balance) }}
                </td>
            </tr>
            <tr v-if="transaction.fees.coupon">
                <th>Coupon:</th>
                <td class="text-right">
                    {{ currency(transaction.fees.coupon * -1) }}
                </td>
                <td class="text-right">
                    ----
                </td>
                <td class="text-right">
                    {{ currency(transaction.fees.coupon * -1) }}
                </td>
            </tr>
            <tr>
                <th>Total:</th>
                <td class="text-right">
                    {{ currency(transaction.fees.total) }}
                </td>
                <td class="text-right">
                    {{ currency(transaction.fees.refunded * -1) }}
                </td>
                <td class="text-right">
                    {{ currency(transaction.fees.balance) }}
                </td>
            </tr>
            </tbody>
        </table>
        <h4>Paid</h4>
        <table class="table table-details w-auto">
            <thead>
            <tr>
                <th></th>
                <th class="text-right">Paid</th>
                <th class="text-right">Refunded</th>
            </tr>
            </thead>
            <tbody>


            <tr>
                <th>Monetary Credit:</th>
                <td class="text-right">
                    {{ currency(transaction.fees.occoupon) }}
                </td>
                <td class="text-right">
                    {{ currency((transaction.refunds?.credits || 0) * -1) }}
                </td>

            </tr>
            <tr>
                <th>{{ transaction.is_partner ? 'Paid' : 'Credit Card' }}:</th>
                <td class="text-right">{{ currency(transaction.fees.paid) }}</td>
                <td class="text-right">
                    {{ currency((transaction.refunds?.refunds || 0) * -1) }}
                </td>
            </tr>
            <tr>
                <th>Total:</th>
                <td class="text-right">
                    {{ currency(transaction.fees.total) }}
                </td>
                <td class="text-right">
                    {{ currency(transaction.fees.refunded * -1) }}
                </td>

            </tr>
            <tr>
                <th>Balance</th>
                <td></td>
                <td class="text-right">
                    {{ currency(transaction.fees.balance) }}
                </td>
            </tr>
            </tbody>
        </table>
        <div v-if="transaction.is_partner" class="alert alert-info">
            Transaction was paid by partner balance.
        </div>

        <div class="modal fade" id="transaction-fee-modal" tabindex="-1" role="dialog"
             aria-labelledby="transaction-fee-modal-label" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                :disabled="busy"><span
                            aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="transaction-fee-modal-label">
                            Update {{ fee.label }}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div v-if="message" class="alert alert-danger" v-text="message"/>
                        <form id="transaction-fee-form" method="post" @submit.prevent="submit">
                            <div class="form-row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="transaction-fee-original" class="control-label">Original Fee</label>
                                        <div id="transaction-fee-original" class="form-control-static">
                                            {{ currency(fee.original) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="transaction-fee-refunded" class="control-label">Already
                                            Refunded</label>
                                        <div id="transaction-fee-refunded" class="form-control-static">
                                            {{ currency(fee.refunded) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="transaction-fee-refunded" class="control-label">Remaining
                                            Fee</label>
                                        <div id="transaction-fee-refunded" class="form-control-static">
                                            {{ currency(fee.balance) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-sm-6">
                                    <div class="form-group" :class="{'has-error': !valid}">
                                        <label for="transaction-fee-amount" class="control-label">Refund Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">$</span>
                                            <input type="number" step=".01" min="0.00"
                                                   :max="Math.min(fee.balance, transaction.fees.balance)"
                                                   id="transaction-fee-amount" class="form-control"
                                                   v-model.number="fee.amount" :disabled="busy" required/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div v-if="transaction.is_admin && !transaction.is_partner" class="form-group">
                                        <label for="transaction-fee-action" class="control-label">Action</label>
                                        <select id="transaction-fee-action" class="form-control" v-model="fee.action"
                                                :disabled="busy">
                                            <option value="credit">Credit Owner</option>
                                            <option value="refund">Refund Credit Card</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button form="transaction-fee-form" type="submit" class="btn btn-primary"
                                :disabled="busy || !valid">
                            Submit
                            <i v-show="busy" class="fa fa-spinner fa-spin" style="margin-left:2px;"></i>
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal" :disabled="busy">Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
