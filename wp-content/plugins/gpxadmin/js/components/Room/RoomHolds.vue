<script setup>
import {ref, onMounted} from 'vue'
import HoldPreview from '@js/components/Holds/HoldPreview.vue'
import HoldExtend from '@js/components/Holds/HoldExtend.vue'
import HoldRelease from '@js/components/Holds/HoldRelease.vue'

const props = defineProps({
    id: {
        type: Number,
        required: true
    }
});
const loaded = ref(false);
const holds = ref([]);
const details = ref(null);
const extend = ref(null);
const release = ref(null);

const load = () => {
    loaded.value = false;
    axios.get(`/gpxadmin/room/holds/?id=${props.id}`)
        .then(response => {
            holds.value = response.data;
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
        <div v-if="loaded && holds.length > 0" class="table-responsive">
            <table class="table data-table table-bordered table-condensed table-left">
                <thead>
                <tr>
                    <th></th>
                    <th>Owner Name</th>
                    <th>GPR ID</th>
                    <th>Week ID</th>
                    <th>Resort</th>
                    <th>Room Size</th>
                    <th>Check In</th>
                    <th>Release On</th>
                    <th>Released</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="hold in holds" :key="hold.id">
                    <td style="display:flex;justify-content:start;align-items:center;gap:5px;">
                        <button type="button" class="btn btn-default btn-plain" @click.prevent="details.open(hold.id)">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button v-show="hold.can_extend" type="button" class="btn btn-default btn-plain" @click.prevent="extend.open(hold.id)" title="Extend Week">
                            <i class="fa fa-calendar-plus-o"></i>
                        </button>
                        <button v-show="hold.can_release" type="button" class="btn btn-default btn-plain" @click.prevent="release.open(hold.id)" title="Release Week">
                            <i class="fa fa-calendar-times-o"></i>
                        </button>
                    </td>
                    <td>{{ hold.owner }}</td>
                    <td>{{ hold.user }}</td>
                    <td>{{ hold.week }}</td>
                    <td>{{ hold.resort }}</td>
                    <td>{{ hold.room_size }}</td>
                    <td>{{ hold.checkin }}</td>
                    <td>{{ hold.release_on }}</td>
                    <td>{{ hold.released }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <hold-preview ref="details" @updated="load" />
        <hold-extend ref="extend" @updated="load" />
        <hold-release ref="release" @updated="load" />
    </div>
</template>
