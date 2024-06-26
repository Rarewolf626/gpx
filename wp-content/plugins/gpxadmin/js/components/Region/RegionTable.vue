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
    sort: "gpx",
    dir: "desc",
    gpx: null,
    region: null,
    display: null,
    parent: null,
    ...props.initalSearch
});
const columns = ref([
    {key: 'gpx', label: 'GPX Sub Region', enabled: true},
    {key: 'region', label: 'Region', enabled: true},
    {key: 'display', label: 'Display Name', enabled: true},
    {key: 'parent', label: 'Parent', enabled: true},
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
        const response = await axios.get('/gpxadmin/regions/search/', {params: search.value})
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
        search.value.dir = ['gpx'].includes(column) ? 'desc' : 'asc';
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
        search.value.gpx,
    ],
    (value) => {
        searchUpdate()
    },
    {debounce: 500, maxWait: 1000, deep: false},
)
watchDebounced(
    () => [
        search.value.region, search.value.display, search.value.parent,
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
                        <th class="dropdown-column" style="width:45px;">
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
                        <td v-show="columnStatus.gpx">
                            <select v-model="search.gpx" name="gpx" autocomplete="off" class="w-full">
                                <option :value="null"></option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </td>
                        <td v-show="columnStatus.region">
                            <input type="search" name="region" v-model.trim="search.region" autocomplete="off" class="w-full">
                        </td>
                        <td v-show="columnStatus.display">
                            <input type="search" name="display" v-model.trim="search.display" autocomplete="off" class="w-full">
                        </td>

                        <td v-show="columnStatus.parent">
                            <input type="search" name="parent" v-model.trim="search.parent" autocomplete="off" class="w-full">
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
                            <a :href="record.edit" class="btn btn-default btn-plain" >
                                <i class="fa fa-pencil"></i>
                            </a>
                        </td>
                        <td v-show="columnStatus.gpx" v-text="record.gpx"></td>
                        <td v-show="columnStatus.region" v-text="record.region"></td>
                        <td v-show="columnStatus.display" v-text="record.display"></td>
                        <td v-show="columnStatus.parent" v-text="record.parent"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <table-pagination v-if="pagination.total > 0" :busy="busy" :pagination="pagination" @paginate="paginate"
                              @limit="limit"/>
        </div>
    </div>
</template>
