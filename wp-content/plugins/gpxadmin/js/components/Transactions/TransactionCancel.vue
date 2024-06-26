<script setup lang="ts">
import {ref, computed, watch} from 'vue';
import type {TransactionDetails, TransactionFees} from "@js/types/transaction";
import type {FormErrors} from "@js/types/forms";
import {currency, round} from "@js/formatters/numbers";

interface Props {
    transaction: TransactionDetails,
    busy: boolean,
}

enum CancelOptions {
    Cancel = 'cancel',
    Refund = 'refund',
    Both = 'both',
}

interface RefundOptions {
    cancel: CancelOptions,
    booking: boolean,
    booking_amount: number,
    cpo: boolean,
    cpo_amount: number,
    upgrade: boolean,
    upgrade_amount: number,
    guest: boolean,
    guest_amount: number,
    late: boolean,
    late_amount: number,
    third_party: boolean,
    third_party_amount: number,
    extension: boolean,
    extension_amount: number,
    tax: boolean,
    tax_amount: number,
    amount: number,
}

interface RefundTotals {
    total: number,
    refund: number,
    credit: number,
}

const props = withDefaults(defineProps<Props>(), {
    busy: false,
    transaction: null,
});

const emit = defineEmits(['updated', 'busy']);
const fees = ref<TransactionFees>(props.transaction.fees);
const loaded = ref(false);
const message = ref('');
const errors = ref<FormErrors>({});
const refund = ref<RefundOptions>({
    cancel: props.transaction.cancelled ? CancelOptions.Refund : CancelOptions.Both,
    booking: props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex && props.transaction.fees.booking.balance > 0,
    booking_amount: props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex ? props.transaction.fees.booking.balance : 0.00,
    cpo: false,
    cpo_amount: 0.00,
    upgrade: props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex && props.transaction.fees.upgrade.balance > 0,
    upgrade_amount: props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex ? props.transaction.fees.upgrade.balance : 0.00,
    guest: props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex && props.transaction.fees.guest.balance > 0,
    guest_amount: props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex ? props.transaction.fees.guest.balance : 0.00,
    late: false,
    late_amount: 0.00,
    third_party: false,
    third_party_amount: 0.00,
    extension: false,
    extension_amount: 0.00,
    tax: false,
    tax_amount: 0.00,
    amount: 0.00,
});
const refunds_sum = computed<number>(() => {
    return round((refund.value.booking ? Math.min(fees.value.booking.balance, refund.value.booking_amount) : 0.00) +
        (refund.value.cpo ? Math.min(fees.value.cpo.balance, refund.value.cpo_amount) : 0.00) +
        (refund.value.upgrade ? Math.min(fees.value.upgrade.balance, refund.value.upgrade_amount) : 0.00) +
        (refund.value.guest ? Math.min(fees.value.guest.balance, refund.value.guest_amount) : 0.00) +
        (refund.value.late ? Math.min(fees.value.late.balance, refund.value.late_amount) : 0.00) +
        (refund.value.third_party ? Math.min(fees.value.third_party.balance, refund.value.third_party_amount) : 0.00) +
        (refund.value.extension ? Math.min(fees.value.extension.balance, refund.value.extension_amount) : 0.00) +
        (refund.value.tax ? Math.min(fees.value.tax.balance, refund.value.tax_amount) : 0.00), 2);
});
const max_refund = computed<number>(() => {
    return round((props.transaction.is_admin && !props.transaction.is_partner) ? Math.min(fees.value.max_refund, refunds_sum.value) : 0.00, 2);
});

const total = computed<RefundTotals>(() => {
    let total = Math.min(fees.value.balance, refunds_sum.value);
    let refund_amount = Math.min(max_refund.value, refund.value.amount);
    let credit_amount = round(total - refund_amount, 2);
    return {
        total: round(refund_amount + credit_amount, 2),
        refund: refund_amount,
        credit: credit_amount,
    };
});

const buttonText = computed<string>(() => {
    let type = props.transaction.is_booking ? 'Booking' : 'Transaction';
    let action = props.transaction.cancelled ? 'Refund' : 'Refund / Cancel';
    return `${action} ${type}`;
});

const resetRefund = (field?: string) => {
    if (field === 'booking') {
        refund.value.booking_amount = refund.value.booking ? props.transaction.fees.booking.balance : 0.00;
    }
    if (field === 'cpo') {
        refund.value.cpo_amount = refund.value.cpo ? props.transaction.fees.cpo.balance : 0.00;
    }
    if (field === 'upgrade') {
        refund.value.upgrade_amount = refund.value.upgrade ? props.transaction.fees.upgrade.balance : 0.00;
    }
    if (field === 'guest') {
        refund.value.guest_amount = refund.value.guest ? props.transaction.fees.guest.balance : 0.00;
    }
    if (field === 'late') {
        refund.value.late_amount = refund.value.late ? props.transaction.fees.late.balance : 0.00;
    }
    if (field === 'third_party') {
        refund.value.third_party_amount = refund.value.third_party ? props.transaction.fees.third_party.balance : 0.00;
    }
    if (field === 'extension') {
        refund.value.extension_amount = refund.value.extension ? props.transaction.fees.extension.balance : 0.00;
    }
    if (field === 'tax') {
        refund.value.tax_amount = refund.value.tax ? props.transaction.fees.tax.balance : 0.00;
    }
    refund.value.amount = max_refund.value;
}

const reset = () => {
    message.value = '';
    errors.value = {};
    refund.value.booking = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex && fees.value.balance > 0 && fees.value.booking.balance > 0;
    refund.value.booking_amount = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex ? props.transaction.fees.booking.balance : 0.00;
    refund.value.cpo = false;
    refund.value.cpo_amount = 0.00;
    refund.value.upgrade = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex && fees.value.balance > 0 && fees.value.upgrade.balance > 0;
    refund.value.upgrade_amount = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex ? props.transaction.fees.upgrade.balance : 0.00;
    refund.value.guest = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex && fees.value.balance > 0 && fees.value.guest.balance > 0;
    refund.value.guest_amount = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex ? props.transaction.fees.guest.balance : 0.00;
    refund.value.late = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex && fees.value.balance > 0 && fees.value.late.balance > 0;
    refund.value.late_amount = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex ? props.transaction.fees.late.balance : 0.00;
    refund.value.third_party = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex && fees.value.balance > 0 && fees.value.third_party.balance > 0;
    refund.value.third_party_amount = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex ? props.transaction.fees.third_party.balance : 0.00;
    refund.value.extension = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex && fees.value.balance > 0 && fees.value.extension.balance > 0;
    refund.value.extension_amount = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex ? props.transaction.fees.extension.balance : 0.00;
    refund.value.tax = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex && fees.value.balance > 0 && fees.value.tax.balance > 0;
    refund.value.tax_amount = props.transaction.is_admin && !props.transaction.is_partner && props.transaction.has_flex ? props.transaction.fees.tax.balance : 0.00;
    refund.value.amount = max_refund.value;
}


const open = () => {
    emit('busy', true);
    reset();
    jQuery('#transaction-cancel-modal').modal('show');
    axios.get('/gpxadmin/transactions/details/', {params: {transaction: props.transaction.id}})
        .then(response => {
            if (response.data.success) {
                fees.value = response.data.transaction.fees;
                reset();
                loaded.value = true;
            } else {
                message.value = response.data.message || 'An error occurred while retrieving the transaction details.';
            }
            emit('busy', false);
        })
        .catch(error => {
            message.value = error.response?.data.message || 'An error occurred while retrieving the transaction details.';
            emit('busy', false);
        });
}

const close = () => {
    jQuery('#transaction-cancel-modal').modal('hide')
    reset();
    loaded.value = false;
}

const cancel = () => {
    if (props.busy) return;
    message.value = '';
    errors.value = {};
    emit('busy', true);
    axios.post('/gpxadmin/transactions/cancel', {transaction: props.transaction.id, refund: refund.value})
        .then(response => {
            if (response.data.success) {
                close();
                emit('updated');
                return;
            }
            if (response.data.errors) {
                errors.value = response.data.errors;
                message.value = response.data.message;
            } else {
                message.value = response.data.message || 'An error occurred while updating the guest details.';
            }
            emit('busy', false);
        })
        .catch(error => {
            message.value = error.response?.data.message || 'An error occurred while updating the guest details.';
            if (error.response?.data.errors) {
                errors.value = error.response.data.errors;
            }
            emit('busy', false);
        });
};

watch(() => refund.value.cancel, () => {
    if (refund.value.cancel === CancelOptions.Cancel) {
        refund.value.booking = false;
        refund.value.booking_amount = 0.0;
        refund.value.cpo = false;
        refund.value.cpo_amount = 0.0;
        refund.value.upgrade = false;
        refund.value.upgrade_amount = 0.0;
        refund.value.guest = false;
        refund.value.guest_amount = 0.0;
        refund.value.late = false;
        refund.value.late_amount = 0.0;
        refund.value.third_party = false;
        refund.value.third_party_amount = 0.0;
        refund.value.extension = false;
        refund.value.extension_amount = 0.0;
        refund.value.tax = false;
        refund.value.tax_amount = 0.0;
    }
});

</script>

<template>
    <div class="transaction-cancel">
        <button type="button" @click.prevent="open" class="btn btn-danger transaction-cancel__button" :disabled="busy"
                v-text="buttonText"/>
        <div class="modal fade text-left" id="transaction-cancel-modal" tabindex="-1" role="dialog"
             aria-labelledby="transaction-cancel-modal-label">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="transaction-cancel-modal" v-text="buttonText"/>
                    </div>
                    <div class="modal-body">
                        <div v-if="busy">
                            <i class="fa fa-spinner fa-spin" style="font-size:30px;"></i>
                        </div>
                        <div v-if="!busy">
                            <div v-if="message" class="alert alert-danger" v-text="message"/>
                        </div>

                        <div v-if="!transaction.cancelled">
                            <div><strong>Should the {{ transaction.is_booking ? 'Booking' : 'Transaction' }} be
                                cancelled?</strong></div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="refund[cancel]" value="both" v-model="refund.cancel"
                                           :disabled="busy"/>
                                    Refund and Cancel
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="refund[cancel]" value="refund" v-model="refund.cancel"
                                           :disabled="busy"/>
                                    Refund Only
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="refund[cancel]" value="cancel" v-model="refund.cancel"
                                           :disabled="busy"/>
                                    Cancel Only
                                </label>
                            </div>
                        </div>

                        <table class="table table-details">
                            <thead>
                            <tr>
                                <th>Fee</th>
                                <th class="text-right">Amount</th>
                                <th>Refund / Credit</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <th>
                                    Exchange/Rental Fee:
                                </th>
                                <td class="text-right">
                                    <del style="margin-right:2px;" v-if="fees.booking.amount">
                                        {{ currency(fees.booking.original) }}
                                    </del>
                                    <span>{{ currency(fees.booking.balance) }}</span>
                                </td>
                                <td v-if="transaction.is_admin && fees.booking.balance > 0 && fees.balance > 0">
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[booking]" :value="true"
                                                   v-model="refund.booking" :disabled="busy || refund.cancel === 'cancel'"
                                                   @change="resetRefund('booking')"/>
                                            Yes
                                        </label>
                                    </div>
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[booking]" :value="false"
                                                   v-model="refund.booking" :disabled="busy || refund.cancel === 'cancel'"
                                                   @change="resetRefund('booking')"/>
                                            No
                                        </label>
                                    </div>
                                    <div class="input-group"
                                         style="display:inline-flex;justify-content:start;align-items:stretch;margin-left:10px;">
                                        <span class="input-group-addon"
                                              style="display:flex;justify-content:center;align-items:center;">$</span>
                                        <input class="form-control w-auto" type="number"
                                               v-model.number="refund.booking_amount" @change="resetRefund()"
                                               :disabled="busy || !refund.booking" :max="fees.booking.balance"
                                               min="0.00" step=".01"/>
                                    </div>
                                </td>
                                <td v-else>
                                    <span
                                        v-if="fees.booking.original && fees.booking.balance <= 0">Already Refunded</span>
                                    <span v-if="fees.booking.original <= 0">N/A</span>
                                    <span v-else>{{ refund.booking ? 'Yes' : 'No' }}</span>
                                </td>
                            </tr>
                            <tr v-if="fees.cpo.original">
                                <th>Flex Booking Fee:</th>
                                <td class="text-right">
                                    <del style="margin-right:2px;" v-if="fees.cpo.amount">
                                        {{ currency(fees.cpo.original) }}
                                    </del>
                                    <span>{{ currency(fees.cpo.balance) }}</span>
                                </td>
                                <td v-if="transaction.is_admin  && fees.cpo.balance > 0 && fees.balance > 0">
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[cpo]" :value="true" v-model="refund.cpo"
                                                   :disabled="busy || refund.cancel === 'cancel'" @change="resetRefund('cpo')"/>
                                            Yes
                                        </label>
                                    </div>
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[cpo]" :value="false" v-model="refund.cpo"
                                                   :disabled="busy || refund.cancel === 'cancel'" @change="resetRefund('cpo')"/>
                                            No
                                        </label>
                                    </div>
                                    <div class="input-group"
                                         style="display:inline-flex;justify-content:start;align-items:stretch;margin-left:10px;">
                                        <span class="input-group-addon"
                                              style="display:flex;justify-content:center;align-items:center;">$</span>
                                        <input class="form-control w-auto" type="number"
                                               v-model.number="refund.cpo_amount" @change="resetRefund()"
                                               :disabled="busy || !refund.cpo" :max="fees.cpo.balance" min="0.00"
                                               step=".01"/>
                                    </div>
                                </td>
                                <td v-else>
                                    <span v-if="fees.cpo.balance <= 0">Already Refunded</span>
                                    <span v-else>{{ refund.cpo ? 'Yes' : 'No' }}</span>
                                </td>
                            </tr>
                            <tr v-if="fees.upgrade.original">
                                <th>Upgrade Fee:</th>
                                <td class="text-right">
                                    <del style="margin-right:2px;" v-if="fees.upgrade.amount">
                                        {{ currency(fees.upgrade.original) }}
                                    </del>
                                    <span>{{ currency(fees.upgrade.balance) }}</span>
                                </td>
                                <td v-if="transaction.is_admin  && fees.upgrade.balance > 0 && fees.balance > 0">
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[upgrade]" :value="true"
                                                   v-model="refund.upgrade" :disabled="busy || refund.cancel === 'cancel'"
                                                   @change="resetRefund('upgrade')"/>
                                            Yes
                                        </label>
                                    </div>
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[upgrade]" :value="false"
                                                   v-model="refund.upgrade" :disabled="busy || refund.cancel === 'cancel'"
                                                   @change="resetRefund('upgrade')"/>
                                            No
                                        </label>
                                    </div>
                                    <div class="input-group"
                                         style="display:inline-flex;justify-content:start;align-items:stretch;margin-left:10px;">
                                        <span class="input-group-addon"
                                              style="display:flex;justify-content:center;align-items:center;">$</span>
                                        <input class="form-control w-auto" type="number"
                                               v-model.number="refund.upgrade_amount" @change="resetRefund()"
                                               :disabled="busy || !refund.upgrade" :max="fees.upgrade.balance"
                                               min="0.00" step=".01"/>
                                    </div>
                                </td>
                                <td v-else>
                                    <span v-if="fees.upgrade.balance <= 0">Already Refunded</span>
                                    <span v-else>{{ refund.upgrade ? 'Yes' : 'No' }}</span>
                                </td>
                            </tr>
                            <tr v-if="fees.guest.original">
                                <th>Guest Fee:</th>
                                <td class="text-right">
                                    <del style="margin-right:2px;" v-if="fees.guest.amount">
                                        {{ currency(fees.guest.original) }}
                                    </del>
                                    <span>{{ currency(fees.guest.balance) }}</span>
                                </td>
                                <td v-if="transaction.is_admin  && fees.guest.balance > 0 && fees.balance > 0">
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[guest]" :value="true"
                                                   v-model="refund.guest" :disabled="busy || refund.cancel === 'cancel'"
                                                   @change="resetRefund('guest')"/>
                                            Yes
                                        </label>
                                    </div>
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[guest]" :value="false"
                                                   v-model="refund.guest" :disabled="busy || refund.cancel === 'cancel'"
                                                   @change="resetRefund('guest')"/>
                                            No
                                        </label>
                                    </div>
                                    <div class="input-group"
                                         style="display:inline-flex;justify-content:start;align-items:stretch;margin-left:10px;">
                                        <span class="input-group-addon"
                                              style="display:flex;justify-content:center;align-items:center;">$</span>
                                        <input class="form-control w-auto" type="number"
                                               v-model.number="refund.guest_amount" @change="resetRefund()"
                                               :disabled="busy || !refund.guest" :max="fees.guest.balance" min="0.00"
                                               step=".01"/>
                                    </div>
                                </td>
                                <td v-else>
                                    <span v-if="fees.guest.balance <= 0">Already Refunded</span>
                                    <span v-else>{{ refund.guest ? 'Yes' : 'No' }}</span>
                                </td>
                            </tr>
                            <tr v-if="fees.late.original">
                                <th>Late Deposit Fee:</th>
                                <td class="text-right">
                                    <del style="margin-right:2px;" v-if="fees.late.amount">
                                        {{ currency(fees.late.original) }}
                                    </del>
                                    <span>{{ currency(fees.late.balance) }}</span>
                                </td>
                                <td v-if="transaction.is_admin  && fees.late.balance > 0 && fees.balance > 0">
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[late]" :value="true" v-model="refund.late"
                                                   :disabled="busy || refund.cancel === 'cancel'" @change="resetRefund('late')"/>
                                            Yes
                                        </label>
                                    </div>
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[late]" :value="false" v-model="refund.late"
                                                   :disabled="busy || refund.cancel === 'cancel'" @change="resetRefund('late')"/>
                                            No
                                        </label>
                                    </div>
                                    <div class="input-group"
                                         style="display:inline-flex;justify-content:start;align-items:stretch;margin-left:10px;">
                                        <span class="input-group-addon"
                                              style="display:flex;justify-content:center;align-items:center;">$</span>
                                        <input class="form-control w-auto" type="number"
                                               v-model.number="refund.late_amount" @change="resetRefund()"
                                               :disabled="busy || !refund.late" :max="fees.late.balance" min="0.00"
                                               step=".01"/>
                                    </div>
                                </td>
                                <td v-else>
                                    <span v-if="fees.late.balance <= 0">Already Refunded</span>
                                    <span>{{ refund.late ? 'Yes' : 'No' }}</span>
                                </td>
                            </tr>
                            <tr v-if="fees.third_party.original">
                                <th>Third Party Deposit Fee:</th>
                                <td class="text-right">
                                    <del style="margin-right:2px;" v-if="fees.third_party.amount">
                                        {{ currency(fees.third_party.original) }}
                                    </del>
                                    <span>{{ currency(fees.third_party.balance) }}</span>
                                </td>
                                <td v-if="transaction.is_admin  && fees.third_party.balance > 0 && fees.balance > 0">
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[third_party]" :value="true" v-model="refund.third_party"
                                                   :disabled="busy || refund.cancel === 'cancel'" @change="resetRefund('third_party')"/>
                                            Yes
                                        </label>
                                    </div>
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[third_party]" :value="false" v-model="refund.third_party"
                                                   :disabled="busy || refund.cancel === 'cancel'" @change="resetRefund('third_party')"/>
                                            No
                                        </label>
                                    </div>
                                    <div class="input-group"
                                         style="display:inline-flex;justify-content:start;align-items:stretch;margin-left:10px;">
                                        <span class="input-group-addon"
                                              style="display:flex;justify-content:center;align-items:center;">$</span>
                                        <input class="form-control w-auto" type="number"
                                               v-model.number="refund.third_party_amount" @change="resetRefund()"
                                               :disabled="busy || !refund.third_party" :max="fees.third_party.balance" min="0.00"
                                               step=".01"/>
                                    </div>
                                </td>
                                <td v-else>
                                    <span v-if="fees.third_party.balance <= 0">Already Refunded</span>
                                    <span>{{ refund.third_party ? 'Yes' : 'No' }}</span>
                                </td>
                            </tr>
                            <tr v-if="fees.extension.original">
                                <th>Credit Extension Fee:</th>
                                <td class="text-right">
                                    <del style="margin-right:2px;" v-if="fees.extension.amount">
                                        {{ currency(fees.extension.original) }}
                                    </del>
                                    <span>{{ currency(fees.extension.balance) }}</span>
                                </td>
                                <td v-if="transaction.is_admin  && fees.extension.balance > 0 && fees.balance > 0">
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[extension]" :value="true"
                                                   v-model="refund.extension" :disabled="busy || refund.cancel === 'cancel'"
                                                   @change="resetRefund('extension')"/>
                                            Yes
                                        </label>
                                    </div>
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[extension]" :value="false"
                                                   v-model="refund.extension" :disabled="busy || refund.cancel === 'cancel'"
                                                   @change="resetRefund('extension')"/>
                                            No
                                        </label>
                                    </div>
                                    <div class="input-group"
                                         style="display:inline-flex;justify-content:start;align-items:stretch;margin-left:10px;">
                                        <span class="input-group-addon"
                                              style="display:flex;justify-content:center;align-items:center;">$</span>
                                        <input class="form-control w-auto" type="number"
                                               v-model.number="refund.extension_amount" @change="resetRefund()"
                                               :disabled="busy || !refund.extension" :max="fees.extension.balance"
                                               min="0.00" step=".01"/>
                                    </div>
                                </td>
                                <td v-else>
                                    <span v-if="fees.extension.balance <= 0">Already Refunded</span>
                                    <span>{{ refund.extension ? 'Yes' : 'No' }}</span>
                                </td>
                            </tr>
                            <tr v-if="fees.coupon">
                                <th>Coupon:</th>
                                <td class="text-right">
                                    <span>{{ currency(fees.coupon) }}</span>
                                </td>
                                <td></td>
                            </tr>
                            <tr v-if="fees.tax.original">
                                <th>Tax Charged:</th>
                                <td class="text-right">
                                    <del style="margin-right:2px;" v-if="fees.tax.amount">
                                        {{ currency(fees.tax.original) }}
                                    </del>
                                    <span>{{ currency(fees.tax.balance) }}</span>
                                </td>
                                <td v-if="transaction.is_admin && fees.balance > 0">
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[tax]" :value="true" v-model="refund.tax"
                                                   :disabled="busy || refund.cancel === 'cancel'" @change="resetRefund('tax')"/>
                                            Yes
                                        </label>
                                    </div>
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="refund[tax]" :value="false" v-model="refund.tax"
                                                   :disabled="busy || refund.cancel === 'cancel'" @change="resetRefund('tax')"/>
                                            No
                                        </label>
                                    </div>
                                    <div class="input-group"
                                         style="display:inline-flex;justify-content:start;align-items:stretch;margin-left:10px;">
                                        <span class="input-group-addon"
                                              style="display:flex;justify-content:center;align-items:center;">$</span>
                                        <input class="form-control w-auto" type="number"
                                               v-model.number="refund.tax_amount" @change="resetRefund()"
                                               :disabled="busy || !refund.tax" :max="fees.tax.balance" min="0.00"
                                               step=".01"/>
                                    </div>
                                </td>
                                <td v-else>
                                    <span v-if="fees.tax.balance <= 0">Already Refunded</span>
                                    <span>{{ refund.tax ? 'Yes' : 'No' }}</span>
                                </td>
                            </tr>
                            <tr v-if="fees.occoupon">
                                <th>Monetary Credit:</th>
                                <td class="text-right">
                                    <span>{{ currency(fees.occoupon) }}</span>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>{{ transaction.is_partner ? 'Paid' : 'Credit Card' }}:</th>
                                <td class="text-right">
                                    <span>{{ currency(fees.paid) }}</span>
                                </td>
                                <td></td>
                            </tr>
                            <tr v-if="fees.refunded">
                                <th>Previously Refunded:</th>
                                <td class="text-right">
                                    <span>{{ currency(fees.refunded) }}</span>
                                </td>
                                <td></td>
                            </tr>
                            <tr v-if="fees.refunded">
                                <th>Current Balance:</th>
                                <td class="text-right">
                                    <span>{{ currency(fees.balance) }}</span>
                                </td>
                                <td></td>
                            </tr>

                            </tbody>
                        </table>
                        <h3>Refunds</h3>

                        <div v-if="transaction.is_admin && max_refund" class="alert alert-info">
                            {{ currency(max_refund) }} of this refund can be refunded to credit card.
                        </div>
                        <div v-if="transaction.is_admin && !fees.balance" class="alert alert-danger">Cannot refund to
                            credit card for transactions with $0 balance.
                        </div>
                        <div v-if="transaction.is_partner" class="alert alert-info">Transactions for a trade partner can
                            only be refunded as credit.
                        </div>

                        <table class="table table-details">
                            <tbody>
                            <tr v-if="max_refund">
                                <th>Refund to Credit Card</th>
                                <td class="text-right" colspan="2">
                                    <div class="input-group text-right"
                                         style="display:inline-flex;justify-content:start;align-items:stretch;">
                                        <span class="input-group-addon"
                                              style="display:flex;justify-content:center;align-items:center;">$</span>
                                        <input class="form-control w-auto" type="number" v-model="refund.amount"
                                               :disabled="busy" :max="max_refund" min="0.00" step=".01"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>Credit to {{ transaction.is_partner ? 'Partner' : 'Owner' }}</th>
                                <td class="text-right" colspan="2">
                                    {{ currency(total.credit) }}
                                </td>
                            </tr>
                            <tr v-if="max_refund">
                                <th>Total Refund</th>
                                <td class="text-right" colspan="2">
                                    {{ currency(total.total) }}
                                </td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" @click.prevent="cancel"
                                :disabled="busy || !loaded">Submit
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
