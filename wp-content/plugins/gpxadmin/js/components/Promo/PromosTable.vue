<script setup>
import {ref, onMounted, computed, watch} from 'vue'
import {watchDebounced} from '@vueuse/core'
import TablePagination from '../TablePagination.vue'
import SortableColumn from '../SortableColumn.vue'
import keyBy from 'lodash/keyBy';
import mapValues from 'lodash/mapValues';

const props = defineProps({
    initalSearch: {
        type: Object,
        default: () => {
            return {};
        }
    },
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
    pname: null,
    slug: null,
    availability: null,
    travel: null,
    coupon: null,
    active: 'yes',
    ...props.initalSearch
});
const columns = ref([
    {key: 'type', label: 'Type', enabled: true},
    {key: 'id', label: 'ID', enabled: true},
    {key: 'pname', label: 'Name', enabled: true},
    {key: 'slug', label: 'Slug', enabled: true},
    {key: 'availability', label: 'Availability', enabled: true},
    {key: 'travel_start', label: 'Travel Start Date', enabled: true, toggle: false},
    {key: 'travel_end', label: 'Travel End Date', enabled: true, toggle: false},
    {key: 'active', label: 'Active', enabled: true},
    {key: 'coupon', label: 'Redeemed Coupon', enabled: true},
]);
const columnStatus = computed(() => {
    return mapValues(keyBy(columns.value, 'key'), 'enabled');
})
const activeColumns = computed(() => {
    return columns.value.filter(column => column.enabled).length + 1;
})
const pagination = ref({
    page: props.initalSearch.pg || 1,
    limit: props.initalSearch.limit || 20,
    total: 0,
    first: 0,
    last: 0,
    pages: 0,
    prev: null,
    next: null,
    elements: [],
})
const records = ref([])
const details = ref(null);
const cancellation = ref(null);
const guest = ref(null);

const load = async () => {
    if (busy.value) return;
    busy.value = true
    try {
        const response = await axios.get('/gpxadmin/promos_search/', {params: search.value})
        records.value = response.data.records;
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
        search.value.dir = ['ai', 'active'].includes(column) ? 'desc' : 'asc';
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

watch(
    () => [
        search.value.type, search.value.active, search.value.availability, search.value.travel,
    ],
    (value) => {
        searchUpdate()
    },
    {debounce: 500, maxWait: 1000, deep: false},
)
watchDebounced(
    () => [
        search.value.id, search.value.pname, search.value.slug, search.value.coupon,
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
                                    <li v-for="column in columns.filter(column => column.toggle !== false)" :key="column">
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
                        <td v-show="columnStatus.type">
                            <select v-model="search.type" name="type" autocomplete="off">
                                <option value=""></option>
                                <option value="coupon">Coupon</option>
                                <option value="promo">Promo</option>
                            </select>
                        </td>
                        <td v-show="columnStatus.id">
                            <input type="search" name="id" v-model.trim="search.id" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.pname">
                            <input type="search" name="pname" v-model.trim="search.pname" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.slug">
                            <input type="search" name="slug" v-model.trim="search.slug" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.availability">
                            <select v-model="search.availability" name="availability" autocomplete="off">
                                <option value=""></option>
                                <option value="landing">Landing Page</option>
                                <option value="site">Site-wide</option>
                            </select>
                        </td>
                        <td v-show="columnStatus.travel_start || columnStatus.travel_end" colspan="2">
                            <input type="date" name="travel" v-model.trim="search.travel" autocomplete="off">
                        </td>
                        <td v-show="columnStatus.active">
                            <select v-model="search.active" name="active" autocomplete="off">
                                <option value=""></option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </td>
                        <td v-show="columnStatus.coupon">
                            <input type="search" name="coupon" v-model.trim="search.coupon" autocomplete="off" class="w-full">
                        </td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-show="busy" class="active">
                        <td :colspan="activeColumns" class="text-center">
                            <i class="fa fa-spinner fa-spin fa-3x"></i>
                        </td>
                    </tr>

                    <tr v-show="!busy" v-for="record in records" :key="record.id">
                        <td style="white-space:nowrap">
                            <a :href="record.edit" class="btn btn-default btn-plain" style="margin-right:5px;" >
                                <i class="fa fa-pencil"></i>
                            </a>
                        </td>
                        <td v-show="columnStatus.type" v-text="record.type"></td>
                        <td v-show="columnStatus.id" v-text="record.id"></td>
                        <td v-show="columnStatus.pname" v-text="record.name"></td>
                        <td v-show="columnStatus.slug">
                            <a :href="record.page" target="_blank">
                                {{ record.slug }}
                            </a>
                        </td>
                        <td v-show="columnStatus.availability" v-text="record.availability"></td>
                        <td v-show="columnStatus.travel_start" v-text="record.travel_start"></td>
                        <td v-show="columnStatus.travel_end" v-text="record.travel_end"></td>
                        <td v-show="columnStatus.active">
                            {{ record.active ? 'Yes' : 'No' }}
                        </td>
                        <td v-show="columnStatus.coupon" v-text="record.coupon"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <table-pagination v-if="pagination.total > 0" :busy="busy" :pagination="pagination" @paginate="paginate"
                              @limit="limit"/>
        </div>


    </div>
</template>
