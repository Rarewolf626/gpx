<script setup lang="ts">
import {ref} from 'vue'
import DashboardAlert from './DashboardAlert.vue'
import DashboardBooking from './DashboardBooking.vue'
import DashboardHoldTime from './DashboardHoldTime.vue'
import DashboardHoldMessage from './DashboardHoldMessage.vue'
import DashboardHoldError from './DashboardHoldError.vue'
import DashboardFeeMinRental from './DashboardFeeMinRental.vue'
import DashboardFeeFlex from './DashboardFeeFlex.vue'
import DashboardFeeLateDeposit from './DashboardFeeLateDeposit.vue'
import DashboardFeeLateDepositWithin from './DashboardFeeLateDepositWithin.vue'
import DashboardThirdPartyDepositFee from './DashboardThirdPartyDepositFee.vue'
import DashboardThirdPartyDepositFeeDays from './DashboardThirdPartyDepositFeeDays.vue'
import DashboardFeeExtension from './DashboardFeeExtension.vue'
import DashboardFeeExchange from './DashboardFeeExchange.vue'
import DashboardFeeExchangeLegacy from './DashboardFeeExchangeLegacy.vue'
import DashboardFeeGuest from './DashboardFeeGuest.vue'
import DashboardTax from './DashboardTax.vue'

const props = defineProps<{
    alert: {
        enabled: boolean,
        message: string,
    },
    booking: {
        disabled: boolean,
        message: string
    },
    hold: {
        time: number,
        message: string,
        error: string
    },
    fees: {
        min_rental: number,
        flex: number,
        late_deposit: number,
        late_deposit_within: number,
        third_party_deposit: number,
        third_party_deposit_days: number,
        extension: number,
        exchange: number,
        exchange_legacy: number,
        guest: {
            enabled: boolean,
            amount: number,
        },
    },
    tax: {
        bonus: boolean,
        exchange: boolean,
    }
}>()

const alert = ref(props.alert);
const booking = ref(props.booking);
const hold = ref(props.hold);
const fees = ref(props.fees);
const tax = ref(props.tax);

</script>

<template>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="sections">
                <div class="row">
                    <div class="col-md-6">
                        <dashboard-alert v-model="alert"/>
                    </div>
                    <div class="col-md-6">
                        <dashboard-booking v-model="booking"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <dashboard-hold-time v-model.number="hold.time"/>
                    </div>
                    <div class="col-md-5">
                        <dashboard-hold-message v-model="hold.message"/>
                    </div>
                    <div class="col-md-5">
                        <dashboard-hold-error v-model="hold.error"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <dashboard-fee-min-rental v-model.number="fees.min_rental" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <dashboard-fee-flex v-model.number="fees.flex" />
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-3">
                        <dashboard-fee-late-deposit v-model.number="fees.late_deposit" />
                    </div>
                    <div class="col-md-3">
                        <dashboard-fee-late-deposit-within v-model.number="fees.late_deposit_within" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <dashboard-third-party-deposit-fee v-model.number="fees.third_party_deposit" />
                    </div>
                    <div class="col-md-3">
                        <dashboard-third-party-deposit-fee-days v-model.number="fees.third_party_deposit_days" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <dashboard-fee-extension v-model.number="fees.extension" />
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-3">
                        <dashboard-fee-exchange v-model.number="fees.exchange" />
                    </div>
                    <div class="col-md-3">
                        <dashboard-fee-exchange-legacy v-model.number="fees.exchange_legacy" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <dashboard-fee-guest v-model="fees.guest" />
                    </div>
                </div>

                <dashboard-tax :bonus="tax.bonus" :exchange="tax.exchange" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.sections {
    display: flex;
    flex-direction: column;
    justify-content: stretch;
    gap: 10px;
}
</style>
