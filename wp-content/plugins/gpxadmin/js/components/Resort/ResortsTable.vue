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
    sort: "resort",
    dir: "asc",
    id: null,
    resort: null,
    city: null,
    region: null,
    country: null,
    ai: null,
    trip_advisor: null,
    active: null,
    ...props.initalSearch
});
const columns = ref([
    {key: 'resort', label: 'Resort', enabled: true},
    {key: 'city', label: 'City', enabled: true},
    {key: 'region', label: 'State', enabled: true},
    {key: 'country', label: 'Country', enabled: true},
    {key: 'ai', label: 'AI', enabled: true},
    {key: 'trip_advisor', label: 'TripAdvisor ID', enabled: true},
    {key: 'active', label: 'Active', enabled: true},
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
const resorts = ref([])
const details = ref(null);
const cancellation = ref(null);
const guest = ref(null);

const load = async () => {
    if (busy.value) return;
    busy.value = true
    try {
        const response = await axios.get('/gpxadmin/resort/search/', {params: search.value})
        resorts.value = response.data.resorts;
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
        search.value.ai, search.value.active,
    ],
    (value) => {
        searchUpdate()
    },
    {debounce: 500, maxWait: 1000, deep: false},
)
watchDebounced(
    () => [
        search.value.id, search.value.resort, search.value.city, search.value.region, search.value.country, search.value.trip_advisor,
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
                        <td v-show="columnStatus.resort">
                            <input type="search" name="resort" v-model.trim="search.resort" autocomplete="off">
                        </td>
                        <td v-show="columnStatus.city">
                            <input type="search" name="city" v-model.trim="search.city" autocomplete="off">
                        </td>
                        <td v-show="columnStatus.region">
                            <input type="search" name="region" v-model.trim="search.region" autocomplete="off">
                        </td>
                        <td v-show="columnStatus.country">
                            <input type="search" name="country" v-model.trim="search.country" autocomplete="off">
                        </td>
                        <td v-show="columnStatus.ai">
                            <select v-model="search.ai" name="ai" autocomplete="off">
                                <option :value="null"></option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </td>
                        <td v-show="columnStatus.trip_advisor">
                            <input type="search" name="trip_advisor" v-model.trim="search.trip_advisor" autocomplete="off">
                        </td>
                        <td v-show="columnStatus.active">
                            <select v-model="search.active" name="active" autocomplete="off">
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

                    <tr v-show="!busy" v-for="resort in resorts" :key="resort.id">
                        <td style="white-space:nowrap">
                            <a :href="resort.view" class="btn btn-default btn-plain" style="margin-right:5px;" >
                                <i class="fa fa-pencil"></i>
                            </a>
                        </td>
                        <td v-show="columnStatus.resort" v-text="resort.resort"></td>
                        <td v-show="columnStatus.city" v-text="resort.city"></td>
                        <td v-show="columnStatus.region" v-text="resort.region"></td>
                        <td v-show="columnStatus.country" v-text="resort.country"></td>
                        <td v-show="columnStatus.ai">
                            {{ resort.ai ? 'Yes' : 'No' }}
                        </td>
                        <td v-show="columnStatus.trip_advisor" v-text="resort.trip_advisor"></td>
                        <td v-show="columnStatus.active">
                            {{ resort.active ? 'Yes' : 'No' }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <table-pagination v-if="pagination.total > 0" :busy="busy" :pagination="pagination" @paginate="paginate"
                              @limit="limit"/>
        </div>


    </div>
</template>
