<script setup>
import {ref, onMounted, computed} from 'vue'

const emit = defineEmits(['updated']);
const busy = ref(false);
const loaded = ref(false);
const message = ref('');
const errors = ref({});
const extend_date = ref(null);
const today = new Date().toISOString().split('T')[0];
const hold = ref({
    id: null,
});

const reset = () => {
    loaded.value = false;
    message.value = '';
    errors.value = {};
    hold.value = {
        id: null,
    };
    extend_date.value = null;
}

const open = (hold_id) => {
    busy.value = true;
    reset();
    hold.value.id = hold_id;

    jQuery('#hold-extend-modal').modal('show')
    load();
};

const load = () => {
    busy.value = true;
    axios.get('/gpxadmin/room/holds/details/', {params: {id: hold.value.id}})
        .then(response => {
            if (response.data.success) {
                hold.value = response.data.hold;
                extend_date.value = null;
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

const submit = () => {
    if(!valid.value) return;
    busy.value = true;
    message.value = '';
    axios.post(`${window.ajaxurl}?action=gpx_extend_week`, {
        id: hold.value.id,
        newdate: extend_date.value,
    })
        .then(response => {
            if (response.data.success) {
                emit('updated');
                close();
            } else {
                message.value = response.data.error || 'An error occurred while releasing the hold.';
            }
            busy.value = false;
        })
        .catch(error => {
            message.value = error.response?.data.error || 'An error occurred while releasing the hold.';
            busy.value = false;
        });
};

const close = () => {
    jQuery('#hold-extend-modal').modal('hide')
    reset();
}

const valid = computed(() => {
    return !busy.value && loaded.value && extend_date.value;
});


onMounted(() => {
    jQuery('#hold-extend-modal')
        .on('hidden.bs.modal', (e) => reset());
})

defineExpose({open, close})
</script>

<template>
    <div class="modal fade" id="hold-extend-modal" tabindex="-1" role="dialog"
         aria-labelledby="hold-details-modal-label">
        <div class="modal-dialog" role="document">
            <form class="modal-content" @submit.prevent="submit">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="hold-extend-modal-label">
                        Extend Hold
                    </h4>
                </div>
                <div class="modal-body">
                    <div v-if="busy && !loaded">
                        <i class="fa fa-spinner fa-spin" style="font-size:30px;"></i>
                    </div>
                    <div>
                        <div v-if="message" class="alert alert-danger" v-text="message"/>
                        <div v-if="loaded" class="details">
                            <div>
                                Currently releases on {{ hold.release_on }}
                            </div>
                            <div class="form-group">
                                <label for="hold-extend-date">Release On</label>
                                <input type="date" class="form-control" id="hold-extend-date" :disabled="busy" :min="today" :max="hold.checkin_iso" v-model="extend_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" >
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" :disabled="!valid">Extend Hold</button>
                </div>
            </form>
        </div>
    </div>
</template>
