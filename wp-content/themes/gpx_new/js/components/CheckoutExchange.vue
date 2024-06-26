<script setup>
import {ref, nextTick, computed, watch} from 'vue';
import {storeToRefs} from 'pinia';
import LateFee from "@js/classes/LateFee";
import CheckoutResortDetails from "@js/components/CheckoutResortDetails.vue";
import {useCheckoutStore} from "@js/stores/CheckoutStore";
import CheckoutWeekDetails from "@js/components/CheckoutWeekDetails.vue";
import CheckoutHeading from "@js/components/CheckoutHeading.vue";
import CheckoutGuest from "@js/components/CheckoutGuest.vue";
import formatDate from "@js/helpers/date";

const props = defineProps({
    user: Object,
    ownerships: Array,
    owners: Array,
    credits: Array,
    latefees: Object,
    error: String,
})

const checkoutStore = useCheckoutStore();
const {busy, cart, week, isExchange} = storeToRefs(checkoutStore);

const exchange = ref({
    show_additional_weeks: props.credits.length === 0 || cart.value.exchange.type === 'deposit',
    error: '',
    deposit: {
        deposit: cart.value.exchange.deposit,
        credit: cart.value.exchange.credit,
        fee: cart.value.exchange.fee,
        late_fee_amount: 0,
        fee_asked: false,
        can_waive_fee: props.user.is_agent,
        waive_late_fee: !cart.value.exchange.fee,
        waive_tp_fee: false,
        waive_tp_date: false,
        tp_fee_amount: 0,
        tp_fee_enabled: cart.value.interval?.third_party_deposit_fee_enabled || false,
        date: cart.value.exchange.date,
        reservation: cart.value.exchange.reservation,
        unit_type: cart.value.exchange.unit_type,
        type: cart.value.exchange.type,
    },
});
const tp_allowed = ref(false);
const late_open = ref(false);

const valid = ref(true);
const errors = ref({});

const guest = ref({
    guest: cart.value.guest.fee || false,
    owner: cart.value.guest.owner || false,
    guest_asked: cart.value.guest.fee || false,
    first_name: cart.value.guest.fee ? cart.value.guest.first_name : props.user.first_name,
    last_name: cart.value.guest.fee ? cart.value.guest.last_name : props.user.last_name,
    email: cart.value.guest.email || props.user.email,
    phone: cart.value.guest.phone || props.user.phone,
    adults: cart.value.guest.adults || 1,
    children: cart.value.guest.children || 0,
    special_request: cart.value.guest.special_request || '',
});

const feeCalculator = new LateFee({
    days: props.latefees.late_days,
    extra_days: props.latefees.late_extra_days,
    fee: props.latefees.late_fee,
    extra_fee: props.latefees.late_extra_fee,
    tp_fee: props.latefees.third_party_fee,
    tp_days: props.latefees.third_party_days,
});


watch(
    () => exchange.value.deposit.credit,
    (value) => {
        if (value) {
            selectExchange(value, 'credit');
        }
    }
)

watch(
    () => exchange.value.deposit.deposit,
    (value) => {
        if (value) {
            selectExchange(value, 'deposit');
        }
    }
)

watch(
    () => exchange.value.deposit.date,
    () => {
        exchange.value.deposit.fee_asked = false;
    }
)

const has_guest_fee = computed(() => {
    return !!week.value.guestFeesEnabled;
});

const deposits = ref(null);

const calcLateFee = () => {
    if (!isExchange.value) return 0;
    if (!exchange.value.deposit.deposit || !exchange.value.deposit.date) {
        exchange.value.deposit.late_fee_amount = 0;
        return 0;
    }
    exchange.value.deposit.late_fee_amount = feeCalculator.calculate(exchange.value.deposit.date);

    return exchange.value.deposit.late_fee_amount;
};

const calcThirdPartyFee = () => {
    if (!isExchange.value) return 0;
    if (exchange.value.deposit.type !== 'deposit' || !exchange.value.deposit.tp_fee_enabled || !exchange.value.deposit.deposit || !exchange.value.deposit.date) {
        exchange.value.deposit.tp_fee_amount = 0;
        tp_allowed.value = true;
        return 0;
    }

    exchange.value.deposit.tp_fee_amount = feeCalculator.thirdPartyFee(exchange.value.deposit.tp_fee_enabled);
    tp_allowed.value = feeCalculator.isThirdPartyAllowed(exchange.value.deposit.date);

    return exchange.value.deposit.tp_fee_amount;
};

const validate = () => {
    errors.value = {};
    valid.value = true;
    if (guest.value.first_name.length === 0) {
        errors.value.first_name = 'First name is required';
        valid.value = false;
    }
    if (guest.value.last_name.length === 0) {
        errors.value.last_name = 'Last name is required';
        valid.value = false;
    }
    if (guest.value.email.length === 0) {
        errors.value.email = 'Email is required';
        valid.value = false;
    }
    if (guest.value.phone.length === 0) {
        errors.value.phone = 'Phone is required';
        valid.value = false;
    }
    if (guest.value.adults <= 0) {
        errors.value.adults = 'Must have at least one adult is required';
        valid.value = false;
    }
    if (isExchange.value) {
        if (!exchange.value.deposit.credit && !exchange.value.deposit.deposit) {
            errors.value['deposit.deposit'] = ['You must select an exchange credit.'];
            valid.value = false;
        }

        if (exchange.value.deposit.deposit) {
            if (!exchange.value.deposit.date) {
                errors.value['deposit.date'] = ['You must enter a check in date.'];
                valid.value = false;
            }

            let ownership = props.ownerships.find(ownership => ownership.id == exchange.value.deposit.deposit);
            if (ownership.defaultUpgrade && !exchange.value.deposit.unit_type) {
                errors.value['deposit.unit_type'] = ['You must select a unit type.'];
                valid.value = false;
            }
            if (!ownership.gpr && !exchange.value.deposit.reservation) {
                errors.value['deposit.reservation'] = ['You must enter a reservation number.'];
                valid.value = false;
            }
        }
    }
    if (!valid.value) {
        let messages = Object.values(errors.value)
        window.alertModal.alert(messages.join("<br>"), true);
    }

    return valid.value;
};

const deposit_allowed = computed(() => {
    if (!exchange.value.deposit.tp_fee_enabled || tp_allowed.value) return true;
    if (exchange.value.deposit.can_waive_fee && exchange.value.deposit.waive_tp_date) return true;
    return false;
});

const waive_text = computed(() => {
    if (exchange.value.deposit.late_fee_amount > 0 && (!exchange.value.deposit.can_waive_fee || !exchange.value.deposit.waive_late_fee)) {
        return 'Accept Fee';
    }
    if (exchange.value.deposit.tp_fee_amount > 0 && (!exchange.value.deposit.can_waive_fee || !exchange.value.deposit.waive_tp_fee)) {
        return 'Accept Fee';
    }
    return (exchange.value.deposit.late_fee_amount > 0 || exchange.value.deposit.tp_fee_amount > 0) ? 'Wave Fee' : 'Ok';
});

const selectExchange = (exchange_id, type) => {
    errors.value = {};
    exchange.value.deposit.fee = false;
    exchange.value.deposit.waive_late_fee = false;
    exchange.value.deposit.late_fee_amount = 0;
    exchange.value.deposit.fee_asked = false;
    exchange.value.deposit.waive_tp_fee = false;
    exchange.value.deposit.waive_tp_date = false;
    exchange.value.deposit.date = null;
    exchange.value.deposit.reservation = null;
    exchange.value.deposit.unit_type = null;
    if (!exchange_id) {
        exchange.value.deposit.credit = null;
        exchange.value.deposit.deposit = null;
        exchange.value.deposit.type = null;
        exchange.value.deposit.tp_fee_enabled = false;
    } else if (type === 'deposit') {
        let ownership = props.ownerships.find(ownership => ownership.id == exchange_id);
        exchange.value.deposit.credit = null;
        exchange.value.deposit.type = 'deposit';
        exchange.value.deposit.tp_fee_enabled = ownership?.third_party_deposit_fee_enabled || false;
        nextTick(() => {
            let datePicker = deposits.value.querySelector(`.checkout__exchangelist__item[data-id="${exchange_id}"] input[name="Check_In_Date__c"]`);
            if (datePicker) datePicker.showPicker();
        });
    } else {
        exchange.value.deposit.deposit = null;
        exchange.value.deposit.type = 'credit';
        exchange.value.deposit.tp_fee_enabled = false;
    }
};

const submitGuestInfo = () => {
    if (busy.value || props.alert) return;
    if (!validate()) return;
    let late_fee = calcLateFee();
    let has_late_fee = late_fee > 0;
    let tp_fee = calcThirdPartyFee();
    let has_tp_fee = tp_fee > 0;
    if (isExchange.value && !exchange.value.deposit.fee_asked && (has_late_fee || has_tp_fee || !tp_allowed.value)) {
        // ask for fee
        exchange.value.deposit.waive_late_fee = false;
        exchange.value.deposit.waive_tp_fee = false;
        exchange.value.deposit.waive_tp_date = false;
        exchange.value.deposit.fee_asked = true;
        window.modals.add('modal-late-fee');
        window.modals.get('modal-late-fee').el.addEventListener('closed', () => {
            window.modals.remove('modal-late-fee');
            late_open.value = false;
            exchange.value.deposit.fee_asked = false;
            exchange.value.deposit.waive_late_fee = false;
            exchange.value.deposit.waive_tp_fee = false;
            exchange.value.deposit.waive_tp_date = false;
        });
        window.modals.activate('modal-late-fee');
        late_open.value = true;

        return;
    }

    checkoutStore.setBusy(true);
    axios.post('/wp-admin/admin-ajax.php?action=gpx_checkout_add_to_cart', {
        week: week.value.week_id,
        type: week.value.WeekType,
        guest: {
            fee: guest.value.guest,
            first_name: guest.value.first_name,
            last_name: guest.value.last_name,
            email: guest.value.email,
            phone: guest.value.phone,
            adults: guest.value.adults,
            children: guest.value.children,
            special_request: guest.value.special_request,
        },
        deposit: {
            type: exchange.value.deposit.type,
            deposit: exchange.value.deposit.deposit,
            credit: exchange.value.deposit.credit,
            waive_late_fee: exchange.value.deposit.waive_late_fee,
            waive_tp_fee: exchange.value.deposit.waive_tp_fee,
            waive_tp_date: exchange.value.deposit.waive_tp_date,
            date: exchange.value.deposit.date,
            reservation: exchange.value.deposit.reservation,
            unit_type: exchange.value.deposit.unit_type,
        }
    })
        .then(response => {
            if (response.data.success) {
                checkoutStore.setCart(response.data.cart);
                checkoutStore.setBusy(false);
                window.location.href = response.data.redirect;
            }
        })
        .catch(error => {
            if (error?.response.data?.errors) {
                errors.value = error.response.data.errors;
                let messages = [];
                ['deposit.credit', 'deposit.deposit', 'deposit.date', 'deposit.unit_type', 'deposit.reservation'].forEach(field => {
                    if (errors.value[field]) {
                        messages.push(errors.value[field].join("<br>"));
                    }
                });
                if (messages.length > 0) {
                    window.alertModal.alert(messages.join("<br>"), true);
                }
                checkoutStore.setBusy(false);
                return;
            }
            if (error?.response.data?.message) {
                let callback = error?.response.data?.redirect ? () => {
                    window.location.href = error.response.data.redirect;
                } : null;
                window.alertModal.alert(error.response.data.message, false, callback);
            } else if (error?.response.data?.redirect) {
                window.location.href = error.response.data.redirect;
                return;
            }
            checkoutStore.setBusy(false);
        });
};


</script>

<template>
    <section class="booking-exchange">
        <checkout-heading/>
        <div class="w-featured bg-gray-light w-result-home">
            <div class="w-list-view dgt-container">
                <div class="w-item-view filtered">
                    <checkout-resort-details/>
                    <checkout-week-details/>
                    <div v-if="week.isExchange">
                        <div class="exchange-credit">
                            <div v-if="!error">
                                <div v-if="credits.length === 0">
                                    <div class="exchange-result">
                                        <h2>Exchange Credit</h2>
                                        <p>
                                            Our records indicate that you do not have a current deposit with GPX;
                                            however this exchange will be performed, in good faith, and in-lieu of a
                                            deposit/banking of a week. Please select Deposit A Week from your Dashboard
                                            after your booking is complete. If you have already deposited your week it
                                            can take up to 48-72 hours for our team to verify the transaction. Should
                                            GPX have questions we will contact you within 24 business hours. Please
                                            note: if a deposit cannot be completed in 5 business days this exchange
                                            transaction will be cancelled.
                                        </p>
                                    </div>
                                </div>
                                <div v-else>
                                    <hgroup>
                                        <h2>Exchange Credit</h2>
                                        <p>Choose an exchange credit to use for this exchange booking.</p>
                                    </hgroup>

                                    <div class="checkout__exchangelist checkout__exchangelist--deposit">
                                        <label
                                            v-for="credit in credits"
                                            :key="credit.id"
                                            :data-id="credit.id"
                                            class="checkout__exchangelist__item"
                                            :class="{'selected': exchange.deposit.credit === credit.id}"
                                        >
                                            <div class="bank-row checkout__exchangelist__item__selector">
                                                <input type="radio"
                                                       class="exchange-credit-check if-perks-ownership"
                                                       :value="credit.id"
                                                       v-model.number="exchange.deposit.credit"
                                                       name="deposit" :data-creditweekid="credit.id"
                                                       :aria-describedby="`exchange-credit-label-${credit.id}`"
                                                >
                                                <span class="checkout__exchangelist__item__label"
                                                      :id="`exchange-credit-label-${credit.id}`">Apply Credit</span>
                                            </div>
                                            <ul class="checkout__exchangelist__item__details">
                                                <li><strong>{{ credit.resort }}</strong></li>
                                                <li><strong>Expires:</strong> {{ formatDate(credit.expires) }}</li>
                                                <li><strong>Entitlement Year:</strong> {{ credit.year }}</li>
                                                <li><strong>Size:</strong> {{ credit.size }}</li>
                                            </ul>
                                            <div v-if="credit.upgradeFee > 0"
                                                 class="checkout__exchangelist__item__upgradefee">
                                                <div><strong>Please note:</strong></div>
                                                <div style="font-size:15px;">This booking requires an upgrade fee.</div>
                                            </div>

                                        </label>
                                    </div>

                                    <div v-if="ownerships.length > 0">
                                        Don't see the credit you want to use?
                                        <a href="#useDeposit"
                                           style="color: #009ad6;"
                                           @click.prevent="exchange.show_additional_weeks = !exchange.show_additional_weeks"
                                        >
                                            Click here
                                        </a>
                                        to <span v-text="exchange.show_additional_weeks ? 'hide' : 'show'"/>
                                        additional weeks to deposit and use for this booking.
                                    </div>
                                </div>
                                <div v-show="exchange.show_additional_weeks" style="margin-top: 35px;">
                                    <hgroup>
                                        <h2>Use New Deposit</h2>
                                        <p>Select the week you would like to deposit as credit for this exchange.</p>
                                    </hgroup>
                                    <div name="exchangendeposit" id="exchangendeposit">
                                        <div
                                            ref="deposits"
                                            class="checkout__exchangelist checkout__exchangelist--deposit">
                                            <label
                                                v-for="ownership in ownerships"
                                                :key="ownership.id"
                                                :data-id="ownership.id"
                                                class="checkout__exchangelist__item"
                                                :class="{'selected': exchange.deposit.deposit === ownership.id}"
                                            >
                                                <div>
                                                    <div class="bank-row">
                                                        <input type="radio"
                                                               class="exchange-credit-check if-perks-ownership"
                                                               :value="ownership.id"
                                                               v-model.number="exchange.deposit.deposit"
                                                               name="deposit" data-creditweekid="deposit">
                                                    </div>
                                                    <div class="bank-row">
                                                        <h3 v-text="ownership.ResortName"/>
                                                    </div>
                                                    <strong v-if="ownership.is_delinquent">Please contact us at <a
                                                        href="tel:+18775667519">(877) 566-7519</a> to use this
                                                        deposit.</strong>
                                                    <div v-else>
                                                        <div class="bank-row">
                                                            <span class="dgt-btn bank-select">Select</span>
                                                        </div>
                                                        <input type="hidden" name="Year" class="disswitch"
                                                               disabled="disabled">
                                                        <input type="hidden" name="OwnershipID" class="switch-deposit"
                                                               :value="week.ResortName">
                                                    </div>
                                                    <div class="bank-row">
                                                        Unit Type:
                                                        <select
                                                            v-if="ownership.defaultUpgrade"
                                                            name="Unit_Type__c"
                                                            class="sel_unit_type doe"
                                                            v-model="exchange.deposit.unit_type"
                                                            :class="{'invisible': exchange.deposit.deposit !== ownership.id}"
                                                        >
                                                            <option value="studio">
                                                                Studio
                                                            </option>
                                                            <option value="1">1br</option>
                                                            <option value="2">2br</option>
                                                            <option value="3">3br</option>
                                                        </select>
                                                        <span v-else v-text="ownership.Room_Type__c"/>
                                                    </div>
                                                    <div v-if="ownership.Week_Type__c" class="bank-row">
                                                        Week Type: {{ ownership.Week_Type__c }}
                                                    </div>
                                                    <div v-if="ownership.Contract_ID__c" class="bank-row">
                                                        Resort Member Number: {{ ownership.Contract_ID__c }}
                                                    </div>
                                                    <div v-if="ownership.Year_Last_Banked__c" class="bank-row">
                                                        Last Year Banked: {{ ownership.Year_Last_Banked__c }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="bank-row" style="margin-top:10px;"
                                                         :class="{'invisible': exchange.deposit.deposit !== ownership.id}">
                                                        <input
                                                            v-if="!ownership.is_delinquent"
                                                            type="date"
                                                            placeholder="Check In Date"
                                                            name="Check_In_Date__c"
                                                            class="form-control"
                                                            :min="ownership.next_year"
                                                            v-model="exchange.deposit.date"
                                                            required
                                                        />
                                                    </div>
                                                    <div class="reswrap"
                                                         :class="{'invisible': exchange.deposit.deposit !== ownership.id}">
                                                        <input type="text" name="Reservation__c"
                                                               placeholder="Reservation Number"
                                                               class="form-control"
                                                               v-model.trim="exchange.deposit.reservation"
                                                               :required="!ownership.gpr"
                                                        />
                                                    </div>

                                                    <div
                                                        v-if="ownership.upgradeFee > 0 || ownership.defaultUpgrade"
                                                    >
                                                        <div><strong>Please note:</strong></div>
                                                        <div style="font-size:15px;">This booking requires an upgrade
                                                            fee.
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <p id="floatDisc" style="font-size: 18px; margin-top: 35px;">
                                        *Float reservations must be made with your home resort prior to deposit. Deposit
                                        transactions will automatically
                                        be system verified. Unverified deposits may result in the cancellation of
                                        exchange
                                        reservations.
                                    </p>
                                </div>
                            </div>
                            <div v-if="error === 'nocredit'">
                                <h2>Exchange weeks are not available.</h2>
                            </div>
                            <div v-if="error === 'deposit'">
                                <div class="exchange-result exchangeNotOK">
                                    <h2>Ready to donate? <a href="#modal-deposit"
                                                            class="dgt-btn deposit-modal"
                                                            aria-label="Deposit Week">Deposit a week now</a> to get
                                        started
                                    </h2>
                                </div>
                            </div>
                            <div v-if="error === 'notavailable'">
                                <h2>This week is no longer available.</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <checkout-guest
                    :week="week"
                    :user="user"
                    :owners="owners"
                    :errors="errors"
                    :has_guest_fee="has_guest_fee"
                    v-model="guest"
                    @submit="submitGuestInfo"
                />
            </div>
        </div>
        <div class="dgt-container g-w-modal">
            <div class="dialog__overlay" :class="{open: late_open}">
                <div id="modal-late-fee" class="dialog" :class="{open: late_open}"
                     data-width="400" data-close-button="true" data-move-to-body="false"
                     data-close-on-outside-click="true">
                    <div class="w-modal" style="margin-top:30px;">
                        <form @submit.prevent="submitGuestInfo" class="flex flex-col gap-em">
                            <div v-if="exchange.deposit.late_fee_amount > 0">
                                You will be required to pay a late deposit fee of $<span
                                v-text="exchange.deposit.late_fee_amount"></span>
                                to complete this transaction.
                            </div>
                            <div v-if="exchange.deposit.tp_fee_amount > 0">
                                You will be required to pay a third party deposit fee of $<span
                                v-text="exchange.deposit.tp_fee_amount"></span>
                                to complete this transaction.
                            </div>
                            <div v-if="!exchange.deposit.can_waive_fee && !deposit_allowed">
                                Cannot deposit a third party week less than <span
                                v-text="latefees.third_party_days"></span> days
                                from check in.
                            </div>
                            <div v-if="exchange.deposit.can_waive_fee" class="flex flex-col gap-em">
                                <div v-if="exchange.deposit.late_fee_amount > 0" class="check"
                                     style="padding:0;margin:0;">
                                    <input type="checkbox" v-model="exchange.deposit.waive_late_fee" id="waive-late-fee"
                                           class="mr-2">
                                    <label for="waive-late-fee">Waive Late Deposit Fee</label>
                                </div>
                                <div v-if="exchange.deposit.tp_fee_amount > 0" class="check"
                                     style="padding:0;margin:0;">
                                    <input type="checkbox" v-model="exchange.deposit.waive_tp_fee" id="waive-tp-fee"
                                           class="mr-2">
                                    <label for="waive-tp-fee">Waive Third Party Deposit Fee</label>
                                </div>
                                <div v-if="exchange.deposit.tp_fee_amount > 0 && !tp_allowed" class="check"
                                     style="padding:0;margin:0 0 10px 0;">
                                    <input type="checkbox" v-model="exchange.deposit.waive_tp_date" id="waive-tp-date"
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
    </section>
</template>

