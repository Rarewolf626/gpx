<script setup>
import {storeToRefs} from 'pinia';
import {useCheckoutStore} from "@js/stores/CheckoutStore";
import formatDate from "@js/helpers/date";

const checkoutStore = useCheckoutStore();
const {week, checkin, checkout} = storeToRefs(checkoutStore);

</script>

<template>
    <div v-if="week.week_id" class="view">
        <div class="view-cnt">
            <img :src="week.image.thumbnail" :alt="week.image.alt"
                 :title="week.image.title">
        </div>
        <div class="view-cnt">
            <div class="descrip">
                <hgroup>
                    <h2 v-text="week.ResortName"/>
                    <span>{{ week.city }}, {{ week.region }}</span>
                </hgroup>
                <p>Check-In {{ formatDate(checkin) }}</p>
                <p>Check-Out {{ formatDate(checkout) }}</p>
            </div>
            <div class="w-status">
                <div class="result"></div>
                <ul class="status">
                    <li>
                        <div
                            :class="{'status-exchange': week.WeekType === 'ExchangeWeek', 'status-rental': week.WeekType === 'BonusWeek'}"></div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
