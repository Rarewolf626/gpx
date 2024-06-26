<script setup>
import {ref, onMounted, computed, watch} from 'vue'
import {watchDebounced} from '@vueuse/core'
import TablePagination from '../TablePagination.vue'
import SortableColumn from '../SortableColumn.vue'
import TransactionPreview from '../Transactions/TransactionPreview.vue'
import keyBy from 'lodash/keyBy';
import mapValues from 'lodash/mapValues';
import TransactionGuest from "@js/components/Transactions/TransactionGuest.vue";

const props = defineProps({
    owner_id: {
        type: Number,
        required: true
    }
})

const busy = ref(false)
const loaded = ref(false)
const search = ref({
    pg: 1,
    limit: 20,
    sort: "id",
    dir: "asc",
    id: null,
    type: null,
    user: null,
    resort: null,
    room: null,
    deposit: null,
    week: null,
    week_type: null,
    checkin: null,
    amount: null,
    date: null,
    cancelled: null,
});
const columns = ref([
    {key: 'id', label: 'Transaction ID', enabled: true},
    {key: 'type', label: 'Transaction Type', enabled: true},
    {key: 'user', label: 'Member Number', enabled: false},
    {key: 'member', label: 'Member Name', enabled: false},
    {key: 'owner', label: 'Owned By', enabled: false},
    {key: 'guest', label: 'Guest Name', enabled: true},
    {key: 'adults', label: 'Adults', enabled: false},
    {key: 'children', label: 'Children', enabled: false},
    {key: 'upgrade', label: 'Upgrade Fee', enabled: false},
    {key: 'cpo', label: 'CPO', enabled: false},
    {key: 'cpo_fee', label: 'CPO Fee', enabled: false},
    {key: 'resort', label: 'Resort Name', enabled: true},
    {key: 'room', label: 'Room Type', enabled: false},
    {key: 'week_type', label: 'Week Type', enabled: true},
    {key: 'balance', label: 'Balance', enabled: false},
    {key: 'resort_id', label: 'Resort ID', enabled: false},
    {key: 'deposit', label: 'Deposit ID', enabled: true},
    {key: 'week', label: 'WeekID', enabled: true},
    {key: 'sleeps', label: 'Sleeps', enabled: false},
    {key: 'bedrooms', label: 'Bedrooms', enabled: false},
    {key: 'nights', label: 'Nights', enabled: false},
    {key: 'checkin', label: 'Check In', enabled: true},
    {key: 'paid', label: 'Paid', enabled: true},
    {key: 'processed', label: 'Processed By', enabled: false},
    {key: 'promo', label: 'Promo Name', enabled: false},
    {key: 'discount', label: 'Discount', enabled: false},
    {key: 'coupon', label: 'Coupon', enabled: false},
    {key: 'occoupon', label: 'Owner Credit Coupon ID', enabled: false},
    {key: 'ocdiscount', label: 'Owner Credit Coupon Amount', enabled: false},
    {key: 'date', label: 'Transaction Date', enabled: true},
    {key: 'cancelled', label: 'Cancelled', enabled: true},
]);
const columnStatus = computed(() => {
    return mapValues(keyBy(columns.value, 'key'), 'enabled');
})
const activeColumns = computed(() => {
    return columns.value.filter(column => column.enabled).length + 1;
})
const pagination = ref({
    page: 1,
    limit: 20,
    total: 0,
    first: 0,
    last: 0,
    pages: 0,
    prev: null,
    next: null,
    elements: [],
})
const transactions = ref([])
const details = ref(null);
const cancellation = ref(null);
const guest = ref(null);

const load = async () => {
    if (busy.value) return;
    busy.value = true
    try {
        const response = await axios.get('/gpxadmin/transactions/search/', {params: {...search.value, owner_id: props.owner_id}})
        transactions.value = response.data.transactions;
        pagination.value = response.data.pagination;
        loaded.value = true;
        busy.value = false;
    } catch (error) {
        console.error(error)
    }
}
const searchUpdate = () => {
    search.value.pg = 1;
    load();
};
const sort = (column) => {
    if (column === search.value.sort) {
        search.value.dir = search.value.dir === 'asc' ? 'desc' : 'asc';
    } else {
        search.value.sort = column;
        search.value.dir = ['id', 'date', 'checkin'].includes(column) ? 'desc' : 'asc';
    }
    load();
};
const paginate = ({page}) => {
    if (!page || busy.value) return;
    search.value.pg = page;
    load();
};
const limit = (limit) => {
    if (!limit || busy.value) return;
    search.value.limit = limit;
    search.value.pg = 1;
    load();
};
const toggleColumn = (column) => {
    columns.value = columns.value.map(col => {
        if (col.key === column) {
            col.enabled = !col.enabled;
        }
        return col;
    })
    if (search.value[column] !== '' && search.value[column] !== null) {
        search.value[column] = null;
    }
};

const showDetails = (transaction) => {
    details.value.open(transaction);
};

const showGuestData = (transaction) => {
    guest.value.open(transaction);
};

watch(
    () => [
        search.value.type, search.value.adults, search.value.children, search.value.cpo, search.value.week_type,
        search.value.bedrooms, search.value.checkin, search.value.date, search.value.cancelled,
    ],
    (value) => {
        searchUpdate()
    },
    {debounce: 500, maxWait: 1000, deep: false},
)
watchDebounced(
    () => [
        search.value.id, search.value.user, search.value.owner, search.value.upgrade, search.value.resort,
        search.value.cpo_fee, search.value.room, search.value.balance, search.value.resort_id, search.value.week,
        search.value.sleeps, search.value.nights, search.value.paid, search.value.processed, search.value.promo,
        search.value.discount, search.value.coupon, search.value.occoupon, search.value.ocdiscount, search.value.deposit,
    ],
    (value) => {
        searchUpdate()
    },
    {debounce: 500, maxWait: 1000, deep: false},
)

onMounted(() => {
    load()
})



</script>

<template>
    <div class="gpxadmin-datatable">
        <div v-if="!loaded">
            <i class="fa fa-spinner fa-spin" style="font-size:30px;"></i>
        </div>
        <div v-if="loaded">
            <div class="table-responsive">
                <table class="table data-table table-bordered table-condensed table-left">
                    <thead>
                    <tr>
                        <th class="dropdown-column">
                            <div class="columns-dropdown dropdown">
                                <button class="btn btn-link btn-xs dropdown-toggle" type="button" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="true" title="Show columns">
                                    <i class="fa fa-th"></i>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li v-for="column in columns" :key="column">
                                        <a href="#" @click.prevent="toggleColumn(column.key)">
                                            <i class="fa fa-check" v-show="columnStatus[column.key]"></i>
                                            <i class="fa fa-square-o" v-show="!columnStatus[column.key]"></i>
                                            <span v-text="column.label"></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>


                        </th>
                        <sortable-column v-for="column in columns" :key="column.key" v-show="column.enabled"
                                         :column="column.key" :selected="search.sort" :dir="search.dir" @sort="sort"
                                         :label="column.label"/>
                    </tr>
                    <tr>
                        <td></td>
                        <td v-show="columnStatus.id">
                            <input type="search" name="id" v-model.trim="search.id" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.type">
                            <select v-model="search.type" name="type" autocomplete="off">
                                <option :value="null"></option>
                                <option value="booking">Booking</option>
                                <option value="deposit">Deposit</option>
                                <option value="extension">Extension</option>
                                <option value="credit_donation">Credit Donation</option>
                                <option value="credit_transfer">Credit Transfer</option>
                                <option value="pay_debit">Pay Debit</option>
                                <option value="guest">Guest</option>
                            </select>
                        </td>
                        <td v-show="columnStatus.user">
                            <input type="search" name="user" v-model.trim="search.user" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.member"></td>
                        <td v-show="columnStatus.owner">
                            <input type="search" name="owner" v-model.trim="search.owner" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.guest"></td>
                        <td v-show="columnStatus.adults">
                            <input type="number" name="adults" v-model.number="search.adults" autocomplete="off" min="0"
                                   step="1" class="w-full">
                        </td>
                        <td v-show="columnStatus.children">
                            <input type="number" name="children" v-model.number="search.children" autocomplete="off"
                                   min="0"
                                   step="1" class="w-full">
                        </td>
                        <td v-show="columnStatus.upgrade">
                            <input type="number" name="upgrade" v-model.number="search.upgrade" autocomplete="off"
                                   min="0"
                                   step="1" class="w-full">
                        </td>
                        <td v-show="columnStatus.cpo">
                            <select v-model="search.cpo" name="cpo" autocomplete="off">
                                <option :value="null"></option>
                                <option value="taken">Taken</option>
                                <option value="nottaken">Not Taken</option>
                                <option value="na">Not Applicable</option>
                            </select>
                        </td>
                        <td v-show="columnStatus.cpo_fee">
                            <input type="number" name="cpo_fee" v-model.number="search.cpo_fee" autocomplete="off" class="w-full"
                                   min="0"
                                   step="1">
                        </td>
                        <td v-show="columnStatus.resort">
                            <input type="search" name="resort" v-model.trim="search.resort" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.room">
                            <input type="search" name="room" v-model.trim="search.room" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.week_type">
                            <select v-model="search.week_type" name="week_type" autocomplete="off">
                                <option :value="null"></option>
                                <option value="rental">Rental</option>
                                <option value="exchange">Exchange</option>
                            </select>
                        </td>
                        <td v-show="columnStatus.deposit">
                            <input type="search" name="deposit" v-model.trim="search.deposit" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.balance">
                            <input type="number" name="balance" v-model.number="search.balance" autocomplete="off"
                                   min="0" class="w-full"
                                   step=".01">
                        </td>
                        <td v-show="columnStatus.resort_id">
                            <input type="search" name="resort_id" v-model.trim="search.resort_id" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.week">
                            <input type="search" name="week" v-model.trim="search.week" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.sleeps">
                            <input type="number" name="sleeps" v-model.number="search.sleeps" autocomplete="off" min="0"
                                   class="w-full"   step="1">
                        </td>
                        <td v-show="columnStatus.bedrooms">
                            <input type="search" name="bedrooms" v-model.trim="search.bedrooms" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.nights">
                            <input type="number" name="nights" v-model.number="search.nights" autocomplete="off" min="0"
                                   class="w-full"   step="1">
                        </td>
                        <td v-show="columnStatus.checkin">
                            <input type="date" name="checkin" v-model.trim="search.checkin" autocomplete="off">
                        </td>
                        <td v-show="columnStatus.paid">
                            <input type="number" name="paid" v-model.number="search.paid" autocomplete="off" min="0"
                                   class="w-full"  step=".01">
                        </td>
                        <td v-show="columnStatus.processed">
                            <input type="search" name="processed" v-model.trim="search.processed" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.promo">
                            <input type="search" name="promo" v-model.trim="search.promo" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.discount">
                            <input type="number" name="discount" v-model.number="search.discount" autocomplete="off"
                                   min="0"
                                   class="w-full"  step=".01">
                        </td>
                        <td v-show="columnStatus.coupon">
                            <input type="number" name="coupon" v-model.number="search.coupon" autocomplete="off" min="0"
                                   class="w-full"  step=".01">
                        </td>
                        <td v-show="columnStatus.occoupon">
                            <input type="search" name="occoupon" v-model.trim="search.occoupon" autocomplete="off">
                        </td>
                        <td v-show="columnStatus.ocdiscount">
                            <input type="number" name="ocdiscount" v-model.number="search.ocdiscount" autocomplete="off"
                                   class="w-full"  min="0" step=".01">
                        </td>
                        <td v-show="columnStatus.date">
                            <input type="date" name="date" v-model.trim="search.date" autocomplete="off">
                        </td>
                        <td v-show="columnStatus.cancelled">
                            <select v-model="search.cancelled" name="cancelled" autocomplete="off">
                                <option :value="null"></option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-show="busy" class="active">
                        <td :colspan="activeColumns" class="text-center">
                            <i class="fa fa-spinner fa-spin fa-3x"></i>
                        </td>
                    </tr>

                    <tr v-show="!busy" v-for="transaction in transactions" :key="transaction.id">
                        <td style="white-space:nowrap">
                            <a :href="transaction.view" target="_blank" class="btn btn-default btn-plain" style="margin-right:5px;" >
                                <i class="fa fa-external-link"></i>
                            </a>
                            <button type="button" class="btn btn-default btn-plain" @click.prevent="showDetails(transaction.id)">
                                <i class="fa fa-eye"></i>
                            </button>
                        </td>
                        <td v-show="columnStatus.id" v-text="transaction.id" style="width:125px;"></td>
                        <td v-show="columnStatus.type" v-text="transaction.type"></td>
                        <td v-show="columnStatus.user" v-text="transaction.user" style="width:138px;"></td>
                        <td v-show="columnStatus.member" v-text="transaction.member"></td>
                        <td v-show="columnStatus.owner" v-text="transaction.owner"></td>
                        <td v-show="columnStatus.guest">
                            <button v-if="transaction.is_booking" class="btn btn-default btn-plain" type="button" @click.prevent="showGuestData(transaction.id)">
                                <i class="fa fa-edit" style="margin-right:5px;"></i>
                                <span v-text="transaction.guest"></span>
                            </button>
                        </td>
                        <td v-show="columnStatus.adults" v-text="transaction.adults" style="width:80px;"></td>
                        <td v-show="columnStatus.children" v-text="transaction.children" style="width:80px;"></td>
                        <td v-show="columnStatus.upgrade" v-text="transaction.upgrade" style="width:105px;"></td>
                        <td v-show="columnStatus.cpo" v-text="transaction.cpo"></td>
                        <td v-show="columnStatus.cpo_fee" v-text="transaction.cpo_fee" style="width:100px;"></td>
                        <td v-show="columnStatus.resort" v-text="transaction.resort"></td>
                        <td v-show="columnStatus.room" v-text="transaction.room" style="width:105px;"></td>
                        <td v-show="columnStatus.week_type" v-text="transaction.week_type"></td>
                        <td v-show="columnStatus.balance" v-text="transaction.balance" style="width:100px;"></td>
                        <td v-show="columnStatus.resort_id" v-text="transaction.resort_id" style="width:100px;"></td>
                        <td v-show="columnStatus.deposit" v-text="transaction.deposit" style="width:120px;"></td>
                        <td v-show="columnStatus.week" v-text="transaction.week" style="width:120px;"></td>
                        <td v-show="columnStatus.sleeps" v-text="transaction.sleeps" style="width:80px;"></td>
                        <td v-show="columnStatus.bedrooms" v-text="transaction.bedrooms" style="width:80px;"></td>
                        <td v-show="columnStatus.nights" v-text="transaction.nights" style="width:80px;"></td>
                        <td v-show="columnStatus.checkin" v-text="transaction.checkin"></td>
                        <td v-show="columnStatus.paid" v-text="transaction.paid" style="width:105px;"></td>
                        <td v-show="columnStatus.processed" v-text="transaction.processed"></td>
                        <td v-show="columnStatus.promo" v-text="transaction.promo"></td>
                        <td v-show="columnStatus.discount" v-text="transaction.discount" style="width:100px;"></td>
                        <td v-show="columnStatus.coupon" v-text="transaction.coupon" style="width:100px;"></td>
                        <td v-show="columnStatus.occoupon" v-text="transaction.occoupon" ></td>
                        <td v-show="columnStatus.ocdiscount" v-text="transaction.ocdiscount" style="width:100px;"></td>
                        <td v-show="columnStatus.date" v-text="transaction.date"></td>
                        <td v-show="columnStatus.cancelled">
                            {{ transaction.cancelled ? 'Yes' : 'No' }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <table-pagination v-if="pagination.total > 0" :busy="busy" :pagination="pagination" @paginate="paginate"
                              @limit="limit"/>
        </div>

        <transaction-guest ref="guest" @updated="load"  />
        <transaction-preview ref="details" @updated="load" />
    </div>

</template>

<style scoped>

</style>
