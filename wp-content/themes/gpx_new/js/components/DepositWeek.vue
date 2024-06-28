<script setup>
import {ref, computed, watch, nextTick, onMounted, onUnmounted} from 'vue'
import LateFee from "@js/classes/LateFee";
import {parseISO, isPast} from 'date-fns';

const loading = ref(false);
const loaded = ref(false);
const busy = ref(false);
const ownerships = ref([]);
const credit = ref(0);
const fees = new LateFee();
const late_fee_asked = ref(false);
const late_fee = ref(0);
const tp_fee = ref(0);
const tp_days = ref(0);
const tp_allowed = ref(false);
const is_agent = ref(false);
const late_open = ref(false);

const deposit = ref({
    id: null,
    checkin: null,
    reservation_number: null,
    unit_type: null,
    coupon: null,
    is_third_party: false,
    gpr: true,
    waive_late_fee: false,
    waive_tp_fee: false,
    waive_tp_date: false,
});

const resetData = () => {
    deposit.value = {
        id: null,
        checkin: null,
        reservation_number: null,
        unit_type: null,
        coupon: null,
        is_third_party: false,
        gpr: true,
        waive_late_fee: false,
        waive_tp_fee: false,
        waive_tp_date: false,
    };
    loaded.value = false;
    ownerships.value = [];
    credit.value = 0;
    late_fee_asked.value = false;
    late_fee.value = 0;
    late_open.value = false;
};

const modal = new ModalManager();
onMounted(() => {
    modal.add('modal-deposit');
    modal.get('modal-deposit').el.addEventListener('closed', () => {
        loaded.value = false;
        late_fee_asked.value = false;
        ownerships.value = [];
    });
})

onUnmounted(() => {
    modal.clear();
    window.modals.remove('modal-latefee');
})

const load = async () => {
    loading.value = true;
    modal.activate('modal-deposit');
    let response = await axios.get('/wp-admin/admin-ajax.php?action=gpx_load_deposit_form');
    deposit.value = {
        id: null,
        checkin: null,
        reservation_number: null,
        unit_type: null,
        coupon: null,
        is_third_party: false,
        gpr: true,
        waive_late_fee: false,
        waive_tp_fee: false,
        waive_tp_date: false,
    };
    ownerships.value = response.data.ownerships;
    credit.value = response.data.credit;
    fees.setFees(response.data.fees);
    late_fee_asked.value = false;
    is_agent.value = response.data.is_agent;
    loading.value = false;
    loaded.value = true;
};

const selectDeposit = id => {
    let ownership = ownerships.value.find(o => o.id === id);
    deposit.value.id = ownership?.id || null;
    deposit.value.checkin = null;
    deposit.value.reservation_number = null;
    deposit.value.unit_type = null;
    deposit.value.coupon = null;
    deposit.value.is_third_party = ownership?.third_party_deposit_fee_enabled || false;
    deposit.value.gpr = ownership?.gpr || true;
    deposit.value.waive_late_fee = false;
    deposit.value.waive_tp_fee = false;
    deposit.value.waive_tp_date = false;
    late_fee_asked.value = false;
    nextTick(() => {
        let datePicker = document.querySelector(`#ownership-wrapper-${id} .deposit-checkin`);
        if (datePicker) datePicker.showPicker();
    });
};

watch(
    () => deposit.value.id,
    (value) => {
        selectDeposit(value);
    }
)

const deposit_allowed = computed(() => {
    if (tp_fee.value <= 0 || tp_allowed.value) return true;
    if (is_agent.value && deposit.value.waive_tp_date) return true;
    return false;
});

const validate = () => {
    if (!loaded.value || busy.value) return false;
    let valid = true;
    const errors = [];
    if (!deposit.value.id) {
        window.alertModal.alert('Please select a week to deposit');
        return false;
    }
    let ownership = ownerships.value.find(o => o.id === deposit.value.id);
    if (!ownership) {
        window.alertModal.alert('Please select a week to deposit');
        return false;
    }
    if (!deposit.value.checkin) {
        valid = false;
        errors.push('Check in date Required!');
    } else if (!is_agent.value) {
        let checkin = parseISO(deposit.value.checkin);
        if (isPast(checkin)) {
            valid = false;
            errors.push('Checkin date must be in the future');
        }
    }

    if (!ownership.gpr && !deposit.value.reservation_number) {
        valid = false;
        errors.push('Reservation number is required');
    }
    if (ownership.needs_unit_type && !deposit.value.unit_type) {
        valid = false;
        errors.push('Unit Type Required!');
    }
    if (!valid) {
        window.alertModal.alert(errors.join('<br>'), true);
    }

    return valid;
};


const submit = async () => {
    if (!loaded.value || busy.value) return;
    if (!validate()) {

        return;
    }
    late_fee.value = fees.calculate(deposit.value.checkin);
    tp_days.value = fees.thirdPartyDays();
    tp_fee.value = fees.thirdPartyFee(deposit.value.is_third_party);
    tp_allowed.value = deposit.value.is_third_party ? fees.isThirdPartyAllowed(deposit.value.checkin) : true;
    let has_late_fee = late_fee.value > 0;
    let has_tp_fee = tp_fee.value > 0;
    if (!late_fee_asked.value && (has_late_fee || has_tp_fee || !tp_allowed.value)) {
        // ask for fee
        deposit.value.waive_late_fee = false;
        deposit.value.waive_tp_fee = false;
        deposit.value.waive_tp_date = false;
        late_fee_asked.value = true;
        window.modals.add('modal-latefee');
        window.modals.get('modal-latefee').el.addEventListener('closed', () => {
            window.modals.remove('modal-latefee');
            late_open.value = false;
            late_fee_asked.value = false;
            deposit.value.waive_late_fee = false;
            deposit.value.waive_tp_fee = false;
            deposit.value.waive_tp_date = false;
        });
        window.modals.activate('modal-latefee');
        late_open.value = true;

        return;
    }

    busy.value = true;
    late_fee_asked.value = false;
    try {
        let response = await axios.post('/wp-admin/admin-ajax.php?action=gpx_add_deposit_to_cart', {type: 'DepositWeek', ...deposit.value});
        if (response.data.success) {
            if (response.data.redirect) {
                window.location.href = response.data.redirect;
                return;
            }
            window.modals.closeAll();
            modal.closeAll();
            resetData();
        }
        if (response.data.message) {
            window.alertModal.alert(response.data.message);
        }
        busy.value = false;
    } catch (error) {
        busy.value = false;
        const message = error.response?.data?.message || 'An error occurred. Please try again later.';
        window.alertModal.alert(message);
    }
};

const waive_text = computed(() => {
    let late = (late_fee.value > 0 && !deposit.value.waive_late_fee) ? late_fee.value : 0.00;
    let tp = (tp_fee.value > 0 && !deposit.value.waive_tp_fee) ? tp_fee.value : 0.00;
    if (late + tp > 0) {
        return 'Add to Cart';
    }
    return 'Submit Deposit';
});

const minDate = computed(() => {
    if (is_agent.value) return null;
    return new Date().toISOString().split('T')[0];
});

defineExpose({load})
</script>

<template>
    <div>
        <i v-if="loading" class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
        <div v-if="loaded">
            <div v-if="ownerships.length === 0">
                <h2>Your ownership ID is not valid.</h2>
            </div>
            <div v-if="ownerships.length > 0">
                <h2>Deposit Week</h2>
                <h5>Current Credit: <span v-text="credit"/></h5>
                <p>Float reservations must be made with your home resort prior to deposit.</p>
                <form method="post" @submit.prevent="submit" novalidate>
                    <fieldset :disabled="busy">
                        <div class="checkout__exchangelist checkout__exchangelist--deposit">
                            <label
                                :id="`ownership-wrapper-${ownership.id}`"
                                :for="`ownership-select-${ownership.id}`"
                                v-for="ownership in ownerships"
                                :key="ownership.id"
                                class="checkout__exchangelist__item"
                                :class="{'selected': ownership.id === deposit.id}"
                            >
                                <h3 v-text="ownership.ResortName"/>
                                <div v-if="ownership.is_delinquent" class="mb-2">
                                    <strong>Please contact us at <a href="tel:+18775667519">(877) 566-7519</a> for
                                        assistance.</strong>
                                </div>
                                <div v-else class="mb-4">
                                    <span type="button" class="btn btn-blue btn-sm">
                                        Select
                                    </span>
                                    <input
                                        :id="`ownership-select-${ownership.id}`"
                                        type="radio"
                                        class="hidden"
                                        :value="ownership.id"
                                        v-model="deposit.id"
                                    />
                                </div>
                                <div class="mb-4">
                                    <div v-if="ownership.needs_unit_type">
                                        Unit Type:
                                        <select
                                            v-model="deposit.unit_type"
                                            :class="{'invisible': deposit.id !== ownership.id}"
                                        >
                                            <option :value="null">Please Select</option>
                                            <option value="studio">Studio</option>
                                            <option value="1">1br</option>
                                            <option value="2">2br</option>
                                            <option value="3">3br</option>
                                        </select>
                                    </div>
                                    <div v-else>Unit Type: {{ ownership.Room_Type__c }}</div>
                                    <div v-if="ownership.interval?.Usage__c">
                                        Ownership Type:
                                        {{ ownership.interval.Usage__c }}
                                    </div>
                                    <div>
                                        Resort Member Number: {{ ownership.interval?.UnitWeek__c }}
                                    </div>
                                    <div v-if="ownership.deposit_year">
                                        Last Year Banked:
                                        {{ ownership.deposit_year }}
                                    </div>
                                </div>
                                <div v-if="!ownership.is_delinquent" class="mb-2">
                                    <input
                                        type="date"
                                        placeholder="Check In Date"
                                        class="deposit-checkin"
                                        :class="{'invisible': deposit.id !== ownership.id}"
                                        required
                                        :min="minDate"
                                        v-model="deposit.checkin"
                                    />
                                </div>
                                <div>
                                    <input
                                        type="text"
                                        placeholder="Reservation Number"
                                        :class="{'invisible': deposit.id !== ownership.id}"
                                        v-model="deposit.reservation_number"
                                        :required="!ownership.gpr"
                                    />
                                </div>
                                <div v-if="is_agent" class="mt-2">
                                    <input
                                        type="text"
                                        placeholder="Coupon Code"
                                        :class="{'invisible': deposit.id !== ownership.id}"
                                        v-model="deposit.coupon"
                                    />
                                </div>
                            </label>
                        </div>
                        <div>
                            <button type="submit" class="btn-will-bank dgt-btn" :disabled="busy">
                                Submit
                                <i v-show="busy" class="fa fa-refresh fa-spin fa-fw"></i>
                            </button>
                        </div>
                    </fieldset>
                </form>

                <div class="dgt-container g-w-modal">
                    <div class="dialog__overlay" :class="{open: late_open}">
                        <div id="modal-latefee" class="dialog" style="top:250px;" :class="{open: late_open}"
                             data-width="400" data-close-button="true" data-move-to-body="true"
                             data-close-on-outside-click="true">
                            <div class="w-modal" style="margin-top:30px;">
                                <form @submit.prevent="submit" class="flex flex-col gap-em">
                                    <div v-if="late_fee > 0">
                                        You will be required to pay a late deposit fee of $<span
                                        v-text="late_fee"></span>
                                        to complete this transaction.
                                    </div>
                                    <div v-if="tp_fee > 0">
                                        You will be required to pay a third party deposit fee of $<span
                                        v-text="tp_fee"></span>
                                        to complete this transaction.
                                    </div>
                                    <div v-if="!is_agent && !deposit_allowed">
                                        Cannot deposit a third party week less than <span v-text="tp_days"></span> days
                                        from check in.
                                    </div>
                                    <div v-if="is_agent" class="flex flex-col gap-em">
                                        <div v-if="late_fee > 0" class="check" style="padding:0;margin:0;">
                                            <input type="checkbox" v-model="deposit.waive_late_fee" id="waive-late-fee"
                                                   class="mr-2">
                                            <label for="waive-late-fee">Waive Late Deposit Fee</label>
                                        </div>
                                        <div v-if="tp_fee > 0" class="check" style="padding:0;margin:0;">
                                            <input type="checkbox" v-model="deposit.waive_tp_fee" id="waive-tp-fee"
                                                   class="mr-2">
                                            <label for="waive-tp-fee">Waive Third Party Deposit Fee</label>
                                        </div>
                                        <div v-if="tp_fee > 0 && !tp_allowed" class="check"
                                             style="padding:0;margin:0 0 10px 0;">
                                            <input type="checkbox" v-model="deposit.waive_tp_date" id="waive-tp-date"
                                                   class="mr-2">
                                            <label for="waive-tp-date">Waive Third Party Deposit Dates</label>
                                        </div>
                                    </div>
                                    <div
                                        class="flex flex-col flex-justify-center flex-align-stretch usw-button max-w-none gap-em">
                                        <button type="submit" class="dgt-btn w-full"
                                                :class="{disabled: !deposit_allowed}" :disabled="!deposit_allowed"
                                                v-text="waive_text"/>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
