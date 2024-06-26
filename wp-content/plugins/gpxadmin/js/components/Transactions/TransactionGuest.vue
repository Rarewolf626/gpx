<script setup>
import {ref, onMounted} from 'vue'

const props = defineProps({

})
const emit = defineEmits(['updated']);

const busy = ref(false);
const loaded = ref(false);
const message = ref('');
const errors = ref({});
const guest = ref({
    id: null,
    cancelled: false,
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    adults: 1,
    children: 0,
    owner: '',
});

const reset = () => {
    loaded.value = false;
    message.value = '';
    errors.value = {};
    guest.value = {
        id: null,
        cancelled: false,
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        adults: 1,
        children: 0,
        owner: '',
    };
}

const open = (transaction) => {
    busy.value = true;
    reset();
    jQuery('#transaction-guest-modal').modal('show')

    axios.get('/gpxadmin/transactions/guest/', {params: {transaction}})
        .then(response => {
            if (response.data.success) {
                guest.value = response.data.guest;
                loaded.value = true;
            } else {
                message.value = response.data.message || 'An error occurred while retrieving the guest details.';
            }
            busy.value = false;
        })
        .catch(error => {
            message.value = error.response?.data.message || 'An error occurred while retrieving the guest details.';
            busy.value = false;
        });
}

const close = () => {
    jQuery('#transaction-guest-modal').modal('hide')
    reset();
}
const submit = () => {
    if (busy.value || !loaded.value || guest.value.cancelled) return;
    message.value = '';
    errors.value = {};
    busy.value = true;
    axios.post('/gpxadmin/transactions/guest/update/', guest.value)
        .then(response => {
            if (response.data.success) {
                close();
                emit('updated');
            } else if (response.data.errors) {
                errors.value = response.data.errors;
                message.value = response.data.message;
            } else {
                message.value = response.data.message || 'An error occurred while updating the guest details.';
            }
            busy.value = false;
        })
        .catch(error => {
            message.value = error.response?.data.message || 'An error occurred while updating the guest details.';
            if(error.response?.data.errors) {
                errors.value = error.response.data.errors;
            }
            busy.value = false;
        });
};

onMounted(() => {
    jQuery('#transaction-guest-modal')
        .on('hidden.bs.modal', (e) => reset());
})

defineExpose({open, close})

</script>

<template>
    <div class="modal fade" id="transaction-guest-modal" tabindex="-1" role="dialog"
         :aria-labelledby="`transaction-guest-modal-label`">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="transaction-guest-modal">
                        Guest Details
                    </h4>
                </div>
                <div class="modal-body">
                    <div v-if="busy && !loaded">
                        <i class="fa fa-spinner fa-spin" style="font-size:30px;"></i>
                    </div>
                    <div>
                        <div v-if="message" class="alert alert-danger" v-text="message"/>
                        <div v-if="guest.cancelled" class="alert alert-warning">This transaction has been cancelled.</div>
                        <form id="transaction-guest-form" v-if="loaded" class="details" @submit.prevent="submit">
                            <fieldset class="row" :disabled="busy">
                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-error': errors.first_name}">
                                        <label for="transaction-guest-first_name" class="control-label">
                                            First Name
                                        </label>
                                        <input type="text" class="form-control" id="transaction-guest-first_name"
                                               v-model.trim="guest.first_name" autocomplete="off"
                                               :readonly="guest.cancelled"
                                        />
                                        <span v-if="errors.first_name" class="help-block" v-text="errors.first_name[0]" />
                                    </div>
                                    <div class="form-group" :class="{'has-error': errors.last_name}">
                                        <label for="transaction-guest-last_name" class="control-label">Last Name</label>
                                        <input type="text" class="form-control" id="transaction-guest-last_name"
                                               v-model.trim="guest.last_name" autocomplete="off"
                                               :readonly="guest.cancelled"
                                        />
                                        <span v-if="errors.last_name" class="help-block" v-text="errors.last_name[0]" />
                                    </div>
                                    <div class="form-group" :class="{'has-error': errors.email}">
                                        <label for="transaction-guest-email" class="control-label">Email</label>
                                        <input type="email" class="form-control" id="transaction-guest-email"
                                               v-model.trim="guest.email" autocomplete="off"
                                               :readonly="guest.cancelled"
                                        />
                                        <span v-if="errors.email" class="help-block" v-text="errors.email[0]" />
                                    </div>
                                    <div class="form-group" :class="{'has-error': errors.phone}">
                                        <label for="transaction-guest-phone" class="control-label">Phone</label>
                                        <input type="tel" class="form-control" id="transaction-guest-phone"
                                               v-model.trim="guest.phone" autocomplete="off"
                                               :readonly="guest.cancelled"
                                        />
                                        <span v-if="errors.phone" class="help-block" v-text="errors.phone[0]" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-error': errors.adults}">
                                        <label for="transaction-guest-adults" class="control-label">Adults</label>
                                        <input type="number" class="form-control" id="transaction-guest-adults" min="1"
                                               step="1"
                                               v-model.number="guest.adults" autocomplete="off"
                                               :readonly="guest.cancelled"
                                        />
                                        <span v-if="errors.adults" class="help-block" v-text="errors.adults[0]" />
                                    </div>
                                    <div class="form-group" :class="{'has-error': errors.children}">
                                        <label for="transaction-guest-children" class="control-label">Children</label>
                                        <input type="number" class="form-control" id="transaction-guest-children"
                                               min="0"
                                               step="1"
                                               v-model.number="guest.children" autocomplete="off"
                                               :readonly="guest.cancelled"
                                        />
                                        <span v-if="errors.children" class="help-block" v-text="errors.children[0]" />
                                    </div>
                                    <div class="form-group" :class="{'has-error': errors.owner}">
                                        <label for="transaction-guest-owner" class="control-label">Owned By</label>
                                        <input type="text" class="form-control" id="transaction-guest-owner"
                                               v-model.trim="guest.owner" autocomplete="off"
                                               :readonly="guest.cancelled"
                                        />
                                        <span v-if="errors.owner" class="help-block" v-text="errors.owner[0]" />
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button form="transaction-guest-form" type="submit" class="btn btn-success"
                            :disabled="busy || !loaded || guest.cancelled">
                        Update
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
