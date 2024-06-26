<script setup>
import {ref, onMounted} from 'vue'

const emit = defineEmits(['updated']);
const busy = ref(false);
const loaded = ref(false);
const message = ref('');
const errors = ref({});
const hold = ref({
    id: null,
});
const refund = ref({
    type: 'cancel',
    action: 'credit',
    amount: 0,
});

const reset = () => {
    loaded.value = false;
    message.value = '';
    errors.value = {};
    hold.value = {
        id: null,
    };
    refund.value = {
        type: 'cancel',
        action: 'credit',
        amount: 0,
    };
}

const open = (hold_id) => {
    busy.value = true;
    reset();
    hold.value.id = hold_id;
    jQuery('#hold-details-modal').modal('show')
    load();
};

const load = () => {
    busy.value = true;
    axios.get('/gpxadmin/room/holds/details/', {params: {id: hold.value.id}})
        .then(response => {
            if (response.data.success) {
                hold.value = response.data.hold;
                loaded.value = true;
            } else {
                message.value = response.data.message || 'An error occurred while retrieving the hold details.';
            }
            busy.value = false;
        })
        .catch(error => {
            message.value = error.response?.data.message || 'An error occurred while retrieving the hold details.';
            busy.value = false;
        });
};

const close = () => {
    jQuery('#hold-details-modal').modal('hide')
    reset();
}

onMounted(() => {
    jQuery('#hold-details-modal')
        .on('hidden.bs.modal', (e) => reset());
})

defineExpose({open, close})
</script>

<template>
    <div class="modal fade" id="hold-details-modal" tabindex="-1" role="dialog"
         aria-labelledby="hold-details-modal-label">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="hold-details-modal-label">
                        Hold Details
                    </h4>
                </div>
                <div class="modal-body">
                    <div v-if="busy && !loaded">
                        <i class="fa fa-spinner fa-spin" style="font-size:30px;"></i>
                    </div>
                    <div>
                        <div v-if="message" class="alert alert-danger" v-text="message"/>
                        <div v-if="loaded" class="details">
                            <ul>
                                <li><strong>Owner:</strong> {{ hold.owner}}</li>
                                <li><strong>Week:</strong> {{ hold.week }}</li>
                                <li><strong>Resort:</strong> {{ hold.resort }}</li>
                                <li><strong>Room:</strong> {{ hold.room_size }}</li>
                                <li><strong>Check In:</strong> {{ hold.checkin }}</li>
                                <li><strong>Activity:</strong></li>
                                <ul style="margin-left: 20px;">
                                    <li v-for="activity in hold.activity" :key="activity.time" >
                                        <strong>{{ activity.time }}</strong> {{ activity.action }} by {{ activity.user }}
                                    </li>
                                </ul>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
