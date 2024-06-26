<script setup>
import {ref} from 'vue';
import {storeToRefs} from 'pinia';
import CheckoutWeekDetails from "@js/components/CheckoutWeekDetails.vue";
import CheckoutReviewTerms from "@js/components/CheckoutReviewTerms.vue";
import CheckoutResortDetails from "@js/components/CheckoutResortDetails.vue";
import {useCheckoutStore} from "@js/stores/CheckoutStore";
import CheckoutHeading from "@js/components/CheckoutHeading.vue";

const props = defineProps({
    terms: {
        type: Array,
        default: () => [],
    },
    error: Boolean,
})

const checkoutStore = useCheckoutStore();
const {busy, cart, type, week, checkin, checkout} = storeToRefs(checkoutStore);

const review = ref({
    agree: false,
    error: false,
    message: '',
});

const hold = () => {
    if (busy.value || props.error) return;
    if (review.value.error) return;
    if (!review.value.agree) {
        review.value.error = true;
        return;
    }
    review.value.error = false;
    review.value.message = '';

    checkoutStore.setBusy(true);
    axios.post('/wp-admin/admin-ajax.php', new URLSearchParams({
        action: 'gpx_checkout_hold',
        pid: week.value.week_id,
        weekType: week.value.WeekType,
    }))
        .then(response => {
            if (!response.data.success) {
                const error = new Error(response.data.message || 'Could not put a hold on requested week.');
                error.response = response;
                throw error;
            }
            checkoutStore.setHold(response.data.release_on);
            checkoutStore.setBusy(false);
            checkoutStore.setStep('exchange', true);
        })
        .catch(error => {
            if (!error.response) {
                review.value.message = 'Could not put a hold on requested week.';
            } else if (error.response.data.login) {
                modals.open('modal-login');
            } else if (error.response.data.inactive) {
                alertModal.modal.el.addEventListener('closed', event => window.location.href = '/', {once: true})
                alertModal.alert(error.response.data.message);
            } else if (error.response.data.message) {
                review.value.message = error.response.data.message;
            } else {
                review.value.message = 'Could not put a hold on requested week.';
            }
            checkoutStore.setBusy(false);
        });
};

</script>

<template>
    <section class="booking booking-path booking-active" id="booking-1">
        <checkout-heading />
        <div class="w-featured bg-gray-light w-result-home">
            <div class="w-list-view dgt-container">
                <div class="w-item-view filtered">
                    <checkout-resort-details  />
                    <checkout-week-details  />
                    <dialog v-if="week.special_desc" class="modal-special" :id="`spDesc${week.week_id}`"
                            data-close-on-outside-click="false">
                        <div class="w-modal stupidbt-reset">
                            <p v-html="week.special_desc"/>
                        </div>
                    </dialog>
                </div>

                <checkout-review-terms :terms="terms" />

                <form class="check"
                      @submit.prevent="hold"
                      :class="{'error': !review.agree && review.error}"
                >
                    <div v-if="review.message" class="hold-error" v-html="review.message"></div>
                    <div class="cnt">
                        <input type="checkbox" id="chk_terms" v-model="review.agree"
                               :disabled="error"
                               @input="review.error = false">
                        <label for="chk_terms" :class="{'gpx-disabled': error}">
                            I have reviewed and understand the terms and conditions above
                        </label>
                    </div>
                    <div class="cnt">
                        <button type="submit" href="" class="dgt-btn btn-next"
                                :disabled="busy || error || review.error"
                                :class="{'gpx-disabled': error}"
                                data-id="booking-2">
                            Next
                            <i v-show="busy" class="fa fa-refresh fa-spin fa-fw"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>

