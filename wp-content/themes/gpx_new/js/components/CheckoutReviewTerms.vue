<script setup>
import {ref} from 'vue';
import {storeToRefs} from 'pinia';
import {useCheckoutStore} from "@js/stores/CheckoutStore";
import {timestampToDate} from "@js/helpers/date";

const props = defineProps({
    terms: {
        type: Array,
        default: () => [],
    },
})

const checkoutStore = useCheckoutStore();
const {week} = storeToRefs(checkoutStore);

const active = ref(1);
const more = ref(0);
const tab1 = ref(null);
const tab2 = ref(null);

const toggle = (tab) => {
    let tabs = [null, tab1.value, tab2.value];

    if (more.value === tab) {
        more.value = 0;
        tab1.value.parentElement.style.maxHeight = '100px';
        tab2.value.parentElement.style.maxHeight = '100px';
    } else {
        more.value = tab;
        if (tab) {
            tabs[tab].parentElement.style.maxHeight = `${tabs[tab].offsetHeight}px`;
        }
    }
}


</script>

<template>
    <div class="tabs">
        <h2>Please Review Booking Policies Before Proceeding</h2>
        <div class="head-tab">
            <ul>
                <li>
                    <a href="#" data-id="tab-1" :class="{'head-active': active === 1}" @click.prevent="active = 1">Know
                        Before You Go</a>
                </li>
                <li>
                    <a href="#" data-id="tab-2" :class="{'head-active': active === 2}" @click.prevent="active = 2">Terms
                        & Conditions</a>
                </li>
            </ul>
            <br><br>
            <h2><strong>All transactions are non-refundable</strong></h2>
            <br><br>
        </div>
        <div class="content-tabs">
            <div id="tab-1" class="item-tab" :class="{'tab-active': active === 1}">
                <div class="item-tab-cnt" :class="{'expanded': more === 1}">
                    <div ref="tab1">
                        <ul v-if="week?.alert_notes?.length > 0" class="albullet">
                            <li v-for="note in week.alert_notes">
                                <strong>
                                    Beginning
                                    {{
                                        note.date.map(timestamp => timestampToDate(timestamp)).join(' Ending ')
                                    }}
                                    :</strong><br/>
                                <div style="white-space: pre-wrap;" v-text="note.desc"/>
                            </li>
                        </ul>
                        <div v-else-if="week.notes" style="white-space: pre-wrap;" v-text="week.notes"/>
                        <div v-if="week.AdditionalInfo" style="white-space: pre-wrap;" v-html="week.AdditionalInfo"/>
                    </div>
                </div>
                <div class="item-seemore">
                    <a href="#" class="seemore" @click.prevent="toggle(1)">
                        <span v-text="more === 1 ? 'See less' : 'See more'"/>
                        <i class="icon-arrow-down"></i>
                    </a>
                </div>
            </div>
            <div id="tab-2" class="item-tab" :class="{'tab-active': active === 2}">
                <div class="item-tab-cnt" :class="{'expanded': terms.length === 0 || more === 2}">
                    <div ref="tab2">
                        <div
                            v-if="terms.length > 0"
                            v-for="term in terms"
                            style="white-space: pre-wrap;margin-bottom:10px;"
                            v-html="term"
                        />
                    </div>
                </div>
                <div v-if="terms.length > 0" class="item-seemore">
                    <a href="#" class="seemore" @click.prevent="toggle(2)">
                        <span v-text="more === 1 ? 'See less' : 'See more'"/>
                        <i class="icon-arrow-down"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
.tabs .item-tab .item-tab-cnt {
    height: auto;
    max-height: 100px;
    transition: max-height .25s ease-out;
    overflow: hidden;
}
</style>
