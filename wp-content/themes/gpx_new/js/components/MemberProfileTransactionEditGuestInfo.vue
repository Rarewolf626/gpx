<script setup>
import {ref, onMounted} from 'vue';

const props = defineProps({
    transaction_id: {
        type: Number,
        required: true
    }
})
const message = ref('');
const loaded = ref(false);
const busy = ref(false);
const transaction = ref({});
const owners = ref([]);
const guest = ref({});
const waived = ref(false);
const selected = ref(-1);
const errors = ref({});

const modal = new ModalManager();

const load = () => {
    busy.value = true;
    message.value = '';
    axios.get('/gpx/profile_transaction_guest_modal/', {params: {transaction: props.transaction_id}})
        .then(response => {
            transaction.value = response.data.transaction;
            guest.value = response.data.guest;
            owners.value = response.data.owners;
            waived.value = false;
            selected.value = response.data.owners[0]?.id ?? -1;
            busy.value = false;
            loaded.value = true;
        })
        .catch(error => {
            message.value = error?.response.data.message || 'An error occurred while loading the transaction.';
            busy.value = false;
        });
};

const changeGuest = () => {
    if (selected.value === -2) return;
    if (!transaction.value.has_guest_fee || transaction.value.paid_guest_fee) {
        selected.value = -2;
        guest.value.id = null;
        guest.value.first_name = '';
        guest.value.last_name = '';
        guest.value.name = '';
        guest.value.email = '';
        guest.value.phone = '';
    } else {
        showModal();
    }
};

const selectOwner = (id) => {
    if (id === selected.value) return;
    let owner = owners.value.find(owner => owner.id === id);
    if (!owner) return;
    selected.value = id;
    guest.value.id = owner.id;
    guest.value.first_name = owner.first_name;
    guest.value.last_name = owner.last_name;
    guest.value.name = owner.name;
    guest.value.email = owner.email;
    guest.value.phone = owner.phone;
};

const cancelGuest = () => {
    modal.closeAll();
};

const waiveFee = () => {
    waived.value = true;
    selected.value = -2;
    guest.value.first_name = '';
    guest.value.last_name = '';
    guest.value.name = '';
    guest.value.email = '';
    guest.value.phone = '';
    modal.closeAll();
};

const acceptGuest = () => {
    waived.value = false;
    selected.value = -2;
    guest.value.first_name = '';
    guest.value.last_name = '';
    guest.value.name = '';
    guest.value.email = '';
    guest.value.phone = '';
    modal.closeAll();
};

const showModal = () => {
    if (!transaction.value.has_guest_fee || transaction.value.paid_guest_fee) return;
    if (selected.value === -2) return;
    modal.activate('modal-guest-fees');
};

const submit = () => {
    if (busy.value || !loaded.value) return;
    busy.value = true;
    message.value = '';
    axios.post('/gpx/profile_transaction_guest_update', {
        transaction: props.transaction_id,
        guest: guest.value,
        fee: !waived.value && selected.value === -2,
    })
        .then(response => {
            if (response.data.success) {
                if (response.data.redirect) {
                    window.location = response.data.redirect;
                } else {
                    window.location.reload();
                }
                busy.value = false;
                return;
            }
            message.value = response.data.message || 'Failed to update guest info';
            errors.value = response.data.errors || {};
            busy.value = false;
        })
        .catch(error => {
            console.error(error);
            message.value = error?.response.data.message || 'An error occurred while updating the guest details.';
            busy.value = false;
        });
}

onMounted(() => {
    modal.add('modal-guest-fees');
    load();
});

</script>

<template>
    <div>
        <h3>Guest Details</h3>
        <div v-if="!loaded">
            <div v-if="busy" class="fa fa-spinner fa-spin" style="font-size:30px;"></div>
            <div class="alert alert-danger mb-1" v-if="message" v-text="message"/>
        </div>
        <form v-if="loaded" @submit.prevent="submit">
            <div class="alert alert-danger mb-1" v-if="message" v-text="message"/>
            <div>
                <div>Select the guest.</div>
                <div class="guest-form-owners">
                    <div class="guest-form-owner" v-for="owner in owners" :key="owner.id"
                         :class="{'selected': selected === owner.id}"
                         @click.prevent="selectOwner(owner.id)">
                        <div>
                            <div class="font-bold mb-1">{{ owner.first_name }} {{ owner.last_name }}</div>
                        </div>
                        <div class="mt-2">
                            <button
                                class="btn btn-blue btn-xs guest-form-owner__selector"
                                @click.prevent.stop="selectOwner(owner.id)"
                                v-text="selected === owner.id ? 'Selected' : 'Select'"
                            />
                        </div>
                    </div>
                    <div class="guest-form-owner" :class="{'selected': selected === -2}" @click.prevent="changeGuest">
                        <div>
                            <div class="font-bold mb-1">Add Guest</div>
                            <div v-if="!transaction.has_guest_fee">
                                (a fee of
                                <span v-if="transaction.fee_slash"
                                      style="text-decoration: line-through;"> ${{ transaction.fee_slash }} </span>
                                <span v-else> ${{ transaction.fee }} </span>
                                will be applied)
                            </div>
                        </div>
                        <div class="mt-2">
                            <button
                                class="btn btn-blue btn-xs guest-form-owner__selector"
                                @click.prevent.stop="changeGuest"
                                v-text="selected === -2 ? 'Selected' : 'Select'"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <fieldset class="grid grid-cols-1 grid-cols-md-2 gap-2" :disabled="busy">
                <div>
                    <div>
                        <label for="transaction-guest-first_name" class="control-label required">
                            First Name
                        </label>
                        <input type="text" class="form-control w-full" id="transaction-guest-first_name"
                               :class="{'has-error': errors.first_name}"
                               v-model.trim="guest.first_name" autocomplete="off" required
                               @input="delete errors.first_name"
                               :disabled="selected !== -2"
                        />
                        <span v-if="errors.first_name" class="form-error"
                              v-text="errors.first_name ? errors.first_name[0] : ''"/>
                    </div>
                    <div>
                        <label for="transaction-guest-last_name" class="control-label required">Last Name</label>
                        <input type="text" class="form-control w-full" id="transaction-guest-last_name"
                               :class="{'has-error': errors.last_name}"
                               v-model.trim="guest.last_name" autocomplete="off" required
                               @input="delete errors.last_name"
                               :disabled="selected !== -2"
                        />
                        <span v-if="errors.last_name" class="form-error"
                              v-text="errors.last_name ? errors.last_name[0] : ''"/>
                    </div>
                    <div>
                        <label for="transaction-guest-email" class="control-label required">Email</label>
                        <input type="email" class="form-control w-full" id="transaction-guest-email"
                               :class="{'has-error': errors.email}"
                               v-model.trim="guest.email" autocomplete="off" required @input="delete errors.email"
                        />
                        <span v-if="errors.email" class="form-error" v-text="errors.email ? errors.email[0] : ''"/>
                    </div>
                    <div>
                        <label for="transaction-guest-phone" class="control-label required">Phone</label>
                        <input type="tel" class="form-control w-full" id="transaction-guest-phone"
                               :class="{'has-error': errors.phone}"
                               v-model.trim="guest.phone" autocomplete="off" required @input="delete errors.phone"
                        />
                        <span v-if="errors.phone" class="form-error" v-text="errors.phone ? errors.phone[0] : ''"/>
                    </div>
                </div>
                <div>
                    <div>
                        <label for="transaction-guest-adults" class="control-label required">Adults</label>
                        <input type="number" class="form-control w-full" id="transaction-guest-adults" min="1"
                               step="1" :class="{'has-error': errors.adults}"
                               v-model.number="guest.adults" autocomplete="off" required @input="delete errors.adults"
                        />
                        <span v-if="errors.adults" class="form-error" v-text="errors.adults ? errors.adults[0] : ''"/>
                    </div>
                    <div>
                        <label for="transaction-guest-children" class="control-label required">Children</label>
                        <input type="number" class="form-control w-full" id="transaction-guest-children"
                               min="0" :class="{'has-error': errors.children}" @input="delete errors.children"
                               step="1"
                               v-model.number="guest.children" autocomplete="off" required
                        />
                        <span v-if="errors.children" class="form-error"
                              v-text="errors.children ? errors.children[0] : ''"/>
                    </div>
                </div>
                <div>
                    <button class="btn btn-blue" type="submit" :disabled="busy">Update</button>
                </div>

            </fieldset>
        </form>

        <dialog id="modal-guest-fees" class="dialog dialog--opaque"
                data-width="800" data-close-button="false" data-move-to-body="false"
                data-close-on-outside-click="false">
            <div class="w-modal">
                <div class="member-form">
                    <div class="w-form">
                        <h2>Guest Fees Required</h2>
                        <div class="gform_wrapper">
                            <h4>
                                By continuing you acknowledge that a
                                <span v-if="transaction.fee_slash"
                                      style="text-decoration: line-through;"> ${{
                                        transaction.fee_slash
                                    }} </span>
                                <span v-else> ${{ transaction.fee }} </span>
                                fee will be added to this transaction at checkout.
                            </h4>
                            <div class="flex justify-start items-center gap-1">
                                <button type="button"
                                        @click="cancelGuest"
                                        class="btn ">
                                    Cancel
                                </button>
                                <button type="button"
                                        @click="waiveFee"
                                        class="btn btn-blue">
                                    Waive Fee
                                </button>
                                <button type="button" class="btn btn-blue"
                                        @click="acceptGuest">
                                    Accept Fee
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </dialog>
    </div>
</template>

<style scoped>

</style>
