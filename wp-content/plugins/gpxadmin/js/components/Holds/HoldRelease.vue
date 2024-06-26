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

const reset = () => {
    loaded.value = false;
    message.value = '';
    errors.value = {};
    hold.value = {
        id: null,
    };
}

const open = (hold_id) => {
    busy.value = true;
    reset();
    hold.value.id = hold_id;
    jQuery('#hold-release-modal').modal('show')
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

const submit = () => {
    busy.value = true;
    axios.post(`${window.ajaxurl}?action=gpx_release_week`, {
        id: hold.value.id,
    })
        .then(response => {
            if (response.data.success) {
                emit('updated');
                close();
            } else {
                message.value = response.data.message || 'An error occurred while releasing the hold.';
            }
            busy.value = false;
        })
        .catch(error => {
            message.value = error.response?.data.message || 'An error occurred while releasing the hold.';
            busy.value = false;
        });
};

const close = () => {
    jQuery('#hold-release-modal').modal('hide')
    reset();
}

onMounted(() => {
    jQuery('#hold-release-modal')
        .on('hidden.bs.modal', (e) => reset());
})

defineExpose({open, close})
</script>

<template>
    <div class="modal fade" id="hold-release-modal" tabindex="-1" role="dialog"
         aria-labelledby="hold-release-modal-label">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="hold-release-modal-label">
                        Release Hold
                    </h4>
                </div>
                <div class="modal-body">
                    <div v-if="busy && !loaded">
                        <i class="fa fa-spinner fa-spin" style="font-size:30px;"></i>
                    </div>
                    <div>
                        <div v-if="message" class="alert alert-danger" v-text="message"/>
                        <div v-if="loaded" class="details">
                            Are you sure you want to release the hold?
                        </div>
                    </div>
                </div>
                <form class="modal-footer" @submit.prevent="submit">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Release Hold</button>
                </form>
            </div>
        </div>
    </div>
</template>
