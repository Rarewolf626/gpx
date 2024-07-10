<script setup>
import {ref, computed, watch} from 'vue'
import formatDate from "@js/helpers/date";

const props = defineProps({
    action: {
        type: String,
        required: true,
        validator: (value) => ['donation', 'transfer'].includes(value)
    },
    credits: Array,
    ownerships: Array,
});

const busy = ref(false);
const agree = ref(false);
const deposit = ref({
    deposit: null,
    credit: null,
    type: null,
    checkin: null,
    reservation: null,
    unit_type: null,
    coupon: null,
});
const show_additional_weeks = ref(false);

const has_deposit = computed(() => {
    if (deposit.value.credit === null && deposit.value.deposit === null) {
        return false;
    }
    if (deposit.value.deposit) {
        if (!deposit.value.checkin) {
            return false;
        }
    }


    return true;
});

watch(
    () => deposit.value.credit,
    (value) => {
        if (value) {
            selectExchange(value, 'credit');
        }
    }
)

watch(
    () => deposit.value.deposit,
    (value) => {
        if (value) {
            selectExchange(value, 'deposit');
        }
    }
)

const selectExchange = (exchange_id, type) => {
    deposit.value.checkin = null;
    deposit.value.reservation = null;
    deposit.value.unit_type = null;
    deposit.value.coupon = null;
    if (!exchange_id) {
        deposit.value.credit = null;
        deposit.value.deposit = null;
        deposit.value.type = null;
        deposit.value.tp_fee_enabled = false;
    } else if (type === 'deposit') {
        deposit.value.credit = null;
        deposit.value.type = 'deposit';
    } else {
        deposit.value.deposit = null;
        deposit.value.type = 'credit';
    }
};

const submit = () => {
    if (deposit.value.credit === null && deposit.value.deposit === null) {
        window.alertModal.alert('Please select a deposit to exchange.');
        return;
    }
    if (deposit.value.deposit) {
        if (!deposit.value.checkin) {
            window.alertModal.alert('Please select a checkin date for your deposit.');
            return;
        }
    }
    if (!agree.value) {
        window.alertModal.alert('Please confirm that you agree to the terms and conditions.');
        return;
    }

    busy.value = true;
    axios.post(window.gpx_base.url_ajax + '?action=gpx_credit_action', {...deposit.value, action: props.action})
        .then(response => {
            if (response.data.success) {
                window.alertModal.alert('Deposit exchange successful.', false, () => {
                    window.location = response.data.redirect;
                });
            } else {
                window.alertModal.alert('Deposit exchange failed.');
                busy.value = false;
            }
        })
        .catch(error => {
            busy.value = false;
            let message = error.response.data.message || 'Could not complete the exchange.';
            window.alertModal.alert(message);
        })
}

</script>

<template>
    <form class="perks-exchange" @submit.prevent="submit" novalidate>
        <div>
            <div class="exchange-credit p-7">
                <div class="exchange-credit-content">
                    <div v-if="credits.length === 0" class="exchange-result">
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
                                :class="{'selected': deposit.credit === credit.id}"
                            >
                                <div class="bank-row checkout__exchangelist__item__selector">
                                    <input type="radio"
                                           class="exchange-credit-check if-perks-ownership"
                                           :value="credit.id"
                                           v-model.number="deposit.credit"
                                           name="deposit[credit]"
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
                            <a href="#useDeposit" style="color: #009ad6;"
                               @click.prevent="show_additional_weeks = !show_additional_weeks">
                                Click here
                            </a>
                            to <span v-text="show_additional_weeks ? 'hide' : 'show'"/>
                            additional weeks to deposit and use for this booking.
                        </div>
                    </div>
                    <div v-show="show_additional_weeks || credits.length === 0" class="mt-7">
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
                                    class="checkout__exchangelist__item"
                                    :class="{'selected': deposit.deposit === ownership.id}"
                                >
                                    <div>
                                        <div class="bank-row">
                                            <input type="radio"
                                                   class="exchange-credit-check if-perks-ownership"
                                                   :value="ownership.id"
                                                   v-model.number="deposit.deposit"
                                                   name="deposit[deposit]"
                                            />
                                        </div>
                                        <div class="bank-row">
                                            <h3 v-text="ownership.ResortName"/>
                                        </div>
                                        <strong v-if="ownership.is_delinquent">Please contact us at <a
                                            href="tel:+18775667519">(877) 566-7519</a> to use this
                                            deposit.</strong>
                                        <div v-else>
                                            <div class="bank-row" style="margin-bottom:5px;">
                                                <span class="dgt-btn bank-select">Select</span>
                                            </div>
                                        </div>
                                        <div class="bank-row">
                                            Unit Type:
                                            <select
                                                v-if="ownership.defaultUpgrade"
                                                name="deposit[unit_type]"
                                                class="sel_unit_type doe"
                                                v-model="deposit.unit_type"
                                                :class="{'invisible': deposit.deposit !== ownership.id}"
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
                                             :class="{'invisible': deposit.deposit !== ownership.id}">
                                            <input
                                                v-if="!ownership.is_delinquent"
                                                type="date"
                                                placeholder="Check In Date"
                                                class="form-control"
                                                name="deposit[checkin]"
                                                :min="ownership.next_year"
                                                v-model="deposit.checkin"
                                                required
                                            />
                                        </div>
                                        <div class="reswrap"
                                             :class="{'invisible': deposit.deposit !== ownership.id}">
                                            <input type="text"
                                                   placeholder="Reservation Number"
                                                   class="form-control"
                                                   name="deposit[reservation]"
                                                   v-model.trim="deposit.reservation"
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
            </div>


            <div class="exchange-submit p-7">
                <div class="exchange-submit-content">
                    <h2 class="text-center mb-7" v-if="action === 'transfer'">Let’s Get Started</h2>
                    <div v-if="action === 'donate'">
                        <h2 class="text-center mb-7">Revive & Thrive</h2>
                        <div class="wpb_text_column wpb_content_element ">
                            <div class="wpb_wrapper">
                                <p style="text-align: center;">Let’s get started on sending a survivor on vacation.
                                    Simply select the week to give to a family in need and then click submit.</p>

                            </div>
                        </div>
                    </div>
                    <div class="exchange-submit-grid">
                        <div v-if="action === 'transfer'" class="exchange-submit-text p-7">
                            <p>
                                Give us around 72 hours for your Savings Credits to show up in your account.
                            </p>
                            <p>
                                A request to convert a week Deposited to GPX Perks Savings Credits is pending
                                confirmation that the Maintenance Fee for the week deposited is paid in full. The amount
                                of Savings Credits awarded is 2x the value of your annual maintenance fee.
                            </p>
                            <p class="pb-7">
                                <a href="/perksterms/" target="_blank" rel="noopener noreferrer">Click here</a>
                                to learn more about the full terms and condition.
                            </p>
                        </div>
                        <div v-if="action === 'donate'" class="exchange-submit-text p-7">
                            After you click Submit, the request to donate the week selected will take up to 72 hours to
                            display in your Member Dashboard. We’ll confirm that the Maintenance Fee for this week is
                            paid in full. Once confirmed then the week will display as donated. Until that time, this
                            request will be in a pending status. (Click here for full terms and conditions.)
                        </div>
                        <div class="exchange-submit-agree p-7">
                            <p v-if="action === 'transfer'">Yes, let’s exchange my Deposit for Savings Credits.</p>
                            <p v-if="action === 'donate'">Yes, let’s donate this week to Revive & Thrive.</p>
                            <label for="ice-checkbox"><input type="checkbox" class="checkbox-agree" v-model="agree"
                                                             :disabled="!has_deposit">
                                I Agree.
                            </label>
                        </div>
                        <button class="exchange-submit-button p-7" type="submit">
                            <div>Submit</div>
                            <div>
                                <figure>
                                    <img
                                        decoding="async"
                                        src="/wp-content/uploads/2021/01/checkmark-60x60.png"
                                        width="60" height="60" alt="checkmark" title="checkmark"
                                        loading="lazy"/>
                                </figure>
                            </div>
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
