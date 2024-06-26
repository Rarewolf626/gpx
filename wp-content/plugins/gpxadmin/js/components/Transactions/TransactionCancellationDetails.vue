<script setup>
import {ref, onMounted} from 'vue'
const busy = ref(false);
const message = ref('');
const details = ref(null);

const reset = () => {
    message.value = '';
    details.value = null;
}

const open = (transaction) => {
    busy.value = true;
    reset();
    jQuery('#transaction-cancelled-modal').modal('show')

    axios.get('/gpxadmin/transactions/cancellation/', {params: {transaction}})
        .then(response => {
            if(response.data.success){
                details.value = response.data.details;
            } else {
                message.value = response.data.message || 'An error occurred while retrieving the cancellation details.';
            }
            busy.value = false;
        })
        .catch(error => {
            message.value = error.response?.data.message || 'An error occurred while retrieving the cancellation details.';
            busy.value = false;
        });
}

const close = () => {
    jQuery('#transaction-cancelled-modal').modal('hide')
    reset();
}

onMounted(() => {
    jQuery('#transaction-cancelled-modal')
        .on('hidden.bs.modal', (e) => reset());
})

defineExpose({open, close})

</script>

<template>
    <div class="modal fade" id="transaction-cancelled-modal" tabindex="-1" role="dialog"
         :aria-labelledby="`transaction-cancelled-modal-label`">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="transaction-cancelled-modal">
                        Cancellation Details
                    </h4>
                </div>
                <div class="modal-body">
                    <div v-if="busy">
                        <i class="fa fa-spinner fa-spin" style="font-size:30px;"></i>
                    </div>
                    <div v-if="!busy">
                        <div v-if="message" class="alert alert-danger" v-text="message" />
                        <div v-if="details" class="details">
                            <div>
                                <div><strong>Cancelled By</strong></div>
                                <div v-text="details.name"></div>
                            </div>
                            <div>
                                <div><strong>Date</strong></div>
                                <div v-text="details.date"></div>
                            </div>
                            <div>
                                <div><strong>Refunded</strong></div>
                                <div v-text="details.amount"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.details {
    display:grid;
    grid-template-columns: 1fr;
    gap: 10px;
}
</style>
