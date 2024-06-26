<script setup>
import {ref} from 'vue'

const props = defineProps({
    week_id: {
        type: Number,
        required: true
    }
})
const busy = ref(false);
const deleted = ref(false);
const message = ref('');
const modal = ref(null);

const confirm = () => {
    jQuery(modal.value).modal('show')
};

const close = () => {
    jQuery(modal.value).modal('hide')
}

const submit = () => {
    if (deleted.value || busy.value) {
        return;
    }
    busy.value = true;
    message.value = '';
    axios.delete('/gpxadmin/room/delete', {params: {id: props.week_id}})
        .then(response => {
            if (response.data.success) {
                message.value = response.data?.message || 'Room archived Successfully.';
                deleted.value = true;
                jQuery(modal.value).on('hidden.bs.modal', () => {
                    window.location = response.data.redirect || '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_all';
                });
            } else {
                message.value = response.data?.message || 'Failed to archive the room.';
                busy.value = false;
            }
        })
        .catch(error => {
            message.value = error.response?.data?.message || 'An error occurred while deleting the room.';
            busy.value = false;
        });
}

</script>

<template>
    <div>
        <button v-if="!deleted" type="button" class="btn btn-danger" @click.prevent="confirm">Delete Week</button>

        <div ref="modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" :class="{'modal-confirm': deleted}" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                        <div v-show="deleted" class="icon-box">
                            <i class="material-icons">&#xE876;</i>
                        </div>
                        <h4 class="modal-title" v-text="deleted ? 'Done!' : 'Delete Week'"></h4>
                    </div>
                    <div class="modal-body">
                        <p v-if="deleted" class="text-center" v-text="message"/>
                        <p v-else>Are you sure you want to remove this room. This action cannot be undone!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" :class="{'btn-success btn-block': deleted}"
                                data-dismiss="modal" v-text="deleted ? 'OK' : 'Close'"/>
                        <button v-if="!deleted" type="button" class="btn btn-danger" @click.prevent="submit"
                                :disabled="busy">Delete Week
                        </button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </div>
</template>

<style scoped>

</style>
