<script setup>
import {ref, onMounted} from 'vue'
import TransactionCancellationDetails from "@js/components/Transactions/TransactionCancellationDetails.vue";
import TransactionPreview from '@js/components/Transactions/TransactionPreview.vue'
import TransactionGuest from "@js/components/Transactions/TransactionGuest.vue";

const props = defineProps({
    id: {
        type: Number,
        required: true
    }
});
const loaded = ref(false);
const transactions = ref([]);
const cancellation = ref(null);
const details = ref(null);
const guest = ref(null);

const showCancelledData = (transaction) => {
    cancellation.value.open(transaction);
};

const showDetails = (transaction) => {
    details.value.open(transaction);
};

const showGuestData = (transaction) => {
    guest.value.open(transaction);
};

const load = () => {
    loaded.value = false;
    axios.get(`/gpxadmin/room_transactions/?id=${props.id}`)
        .then(response => {
            transactions.value = response.data;
            loaded.value = true;
        });
}
onMounted(() => {
    load();
});

</script>

<template>
    <div>
        <div v-if="!loaded">
            <i class="fa fa-spinner fa-spin" style="font-size:30px;"></i>
        </div>
        <div v-if="loaded && transactions.length > 0" class="table-responsive">
            <table class="table data-table table-bordered table-condensed table-left">
                <thead>
                <tr>
                    <th></th>
                    <th>Transaction ID</th>
                    <th>Member Number</th>
                    <th>Member Name</th>
                    <th>Owned By</th>
                    <th>Guest Name</th>
                    <th>Resort Name</th>
                    <th>Week Type</th>
                    <th>Check In</th>
                    <th>Paid</th>
                    <th>Transaction Date</th>
                    <th>Cancelled</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="transaction in transactions" :key="transaction.id">
                    <td style="white-space:nowrap">
                        <a :href="transaction.view" target="_blank" class="btn btn-default btn-plain" style="margin-right:5px;" >
                            <i class="fa fa-external-link"></i>
                        </a>
                        <button type="button" class="btn btn-default btn-plain" @click.prevent="showDetails(transaction.id)">
                            <i class="fa fa-eye"></i>
                        </button>
                    </td>
                    <td>{{ transaction.id }}</td>
                    <td>{{ transaction.user }}</td>
                    <td>{{ transaction.member }}</td>
                    <td>{{ transaction.owner }}</td>
                    <td>
                        <button v-if="transaction.is_booking" class="btn btn-default btn-plain" type="button" @click.prevent="showGuestData(transaction.id)">
                            <i class="fa fa-edit" style="margin-right:5px;"></i>
                            <span v-text="transaction.guest"></span>
                        </button>
                    </td>
                    <td>{{ transaction.resort }}</td>
                    <td>{{ transaction.week_type }}</td>
                    <td>{{ transaction.checkin }}</td>
                    <td>{{ transaction.paid }}</td>
                    <td>{{ transaction.date }}</td>
                    <td>
                        <button v-if="transaction.cancelled" type="button" class="btn btn-default btn-plain"
                                data-target="#transaction-cancelled-modal"
                                @click.prevent="showCancelledData(transaction.id)">
                            <i class="fa fa-eye" style="margin-right:1px;"></i>
                            Yes
                        </button>
                        <span v-show="!transaction.cancelled">No</span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <transaction-cancellation-details ref="cancellation" />
        <transaction-preview ref="details" @updated="load" />
        <transaction-guest ref="guest" @updated="load"  />
    </div>
</template>
