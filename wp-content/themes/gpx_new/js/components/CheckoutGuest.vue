<script setup>
import {ref, nextTick, computed, watch} from 'vue';
import {storeToRefs} from 'pinia';
import {useCheckoutStore} from "@js/stores/CheckoutStore";

const props = defineProps({
    week: Object,
    user: Object,
    owners: Array,
    has_guest_fee: Boolean,
    modelValue: Object,
    errors: Object,
})
const emit = defineEmits(['update:modelValue', 'submit'])

const checkoutStore = useCheckoutStore();
const {busy, cart} = storeToRefs(checkoutStore);

const guest = computed({
    get() {
        return props.modelValue
    },
    set(value) {
        emit('update:modelValue', value)
    }
})

const changeGuest = () => {
    if (guest.value.guest) return;
    if (!props.week.guestFeesEnabled) {
        guest.value.owner = null;
        guest.value.guest = true;
        guest.value.first_name = '';
        guest.value.last_name = '';
        guest.value.email = '';
        guest.value.phone = '';
        guest.value.adults = 1;
        guest.value.children = 0;
        guest.value.special_request = '';
    } else {
        showModal();
    }
};

const selectOwner = (id) => {
    if(id === guest.value.owner) return;
    let owner = props.owners.find(owner => owner.id === id);
    if (!owner) return;
    guest.value.guest = false;
    guest.value.owner = owner.id;
    guest.value.first_name = owner.first_name;
    guest.value.last_name = owner.last_name;
    guest.value.email = owner.email;
    guest.value.phone = owner.phone;
};

const cancelGuest = () => {
    selectOwner(cart.value.cid);
    window.modals.closeAll();
};

const acceptGuest = () => {
    guest.value.guest = true;
    guest.value.owner = null;
    guest.value.first_name = '';
    guest.value.last_name = '';
    guest.value.email = '';
    guest.value.phone = '';
    guest.value.adults = 1;
    guest.value.children = 0;
    guest.value.special_request = '';
    window.modals.closeAll();
};

const showModal = (value = true) => {
    if (!props.week.guestFeesEnabled) return;
    if (guest.value.guest) return;
    if (!value) return;
    guest.value.guest_asked = true;
    window.active_modal('modal-guest-fees');
};

const submitGuestInfo = () => {
    if (busy.value) return;
    emit('submit');
};

</script>

<template>
    <div>
        <div class="member-form">
            <hgroup>
                <h2>Member / Guest Information</h2>
                <h2>GPX Member: <strong>{{ user.first_name }}, {{ user.last_name }}</strong></h2>
            </hgroup>
            <div class="w-form">
                <div>Select the guest.</div>
                <div class="guest-form-owners">
                    <div class="guest-form-owner" v-for="owner in owners" :key="owner.id"
                         :class="{'selected': !guest.guest && guest.owner === owner.id}"
                         @click.prevent="selectOwner(owner.id)">
                        <div>
                            <div class="font-bold mb-1">{{ owner.first_name }} {{ owner.last_name }}</div>
                        </div>
                        <div class="mt-2">
                            <button
                                class="btn btn-blue btn-xs guest-form-owner__selector"
                                @click.prevent.stop="selectOwner(owner.id)"
                                v-text="(!guest.guest && guest.owner === owner.id) ? 'Selected' : 'Select'"
                            />
                        </div>
                    </div>
                    <div class="guest-form-owner" :class="{'selected': guest.guest}" @click.prevent="changeGuest">
                        <div>
                            <div class="font-bold mb-1">Add Guest</div>
                            <div v-if="props.week.guestFeesEnabled">
                                (a fee of
                                <span v-if="week.gfSlash"
                                      style="text-decoration: line-through;"> ${{ week.gfSlash }} </span>
                                <span v-else> ${{ week.gfAmt }} </span>
                                will be applied)
                            </div>
                        </div>
                        <div class="mt-2">
                            <button
                                class="btn btn-blue btn-xs guest-form-owner__selector"
                                @click.prevent.stop="changeGuest"
                                v-text="guest.guest ? 'Selected' : 'Select'"
                            />
                        </div>
                    </div>
                </div>
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
                                        <span v-if="week.gfSlash"
                                              style="text-decoration: line-through;"> ${{
                                                week.gfSlash
                                            }} </span>
                                        <span v-else> ${{ week.gfAmt }} </span>
                                        fee will be added to this transaction at checkout.
                                    </h4>
                                    <button type="button"
                                            @click="cancelGuest"
                                            class="btn">
                                        Cancel
                                    </button>
                                    <button type="button" class="btn btn-blue"
                                            @click="acceptGuest"
                                            style="margin-left: 10px;">
                                        Continue
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </dialog>

                <form method="post" @submit.prevent="submitGuestInfo">
                    <fieldset :disabled="busy">
                        <div class="form w-1/2">
                            <div class="form-row">
                                <label for="guest-form-first_name" class="form-label required">
                                    First Name
                                </label>
                                <input type="text" name="first_name" id="guest-form-first_name"
                                       class="form-input"
                                       v-model.trim="guest.first_name"
                                       required
                                       maxlength="255"
                                       :readonly="!guest.guest"
                                >
                                <div class="form-error" v-show="errors.first_name"
                                     v-text="errors.first_name"></div>
                            </div>
                            <div class="form-row">
                                <label for="guest-form-last_name" class="form-label required">Last
                                    Name</label>
                                <input type="text" name="last_name" id="guest-form-last_name"
                                       class="form-input"
                                       v-model.trim="guest.last_name"
                                       required
                                       maxlength="255"
                                       :readonly="!guest.guest"
                                >
                                <div class="form-error" v-show="errors.last_name"
                                     v-text="errors.last_name"></div>
                            </div>
                            <div class="form-row">
                                <label for="guest-form-email" class="form-label required">Email</label>
                                <input type="email" name="email" id="guest-form-email"
                                       class="form-input"
                                       v-model.trim="guest.email"
                                       required
                                       maxlength="255">
                                <div class="form-error" v-show="errors.email"
                                     v-text="errors.email"></div>
                            </div>
                            <div class="form-row">
                                <label for="guest-form-phone" class="form-label required">Phone</label>
                                <input type="tel" name="phone" id="guest-form-phone"
                                       class="form-input"
                                       v-model.trim="guest.phone"
                                       required
                                       maxlength="25">
                                <div class="form-error" v-show="errors.phone"
                                     v-text="errors.phone"></div>
                            </div>
                            <div class="form-row">
                                <label for="guest-form-adults"
                                       class="form-label required">Adults</label>
                                <input type="number" min="1" :max="week.sleeps" name="adults"
                                       id="guest-form-adults"
                                       class="form-input" v-model.number="guest.adults" required>
                                <div class="form-error" v-show="errors.adults"
                                     v-text="errors.adults"></div>
                            </div>
                            <div class="form-row">
                                <label for="guest-form-children"
                                       class="form-label required">Children</label>
                                <input type="number" min="0" :max="week.sleeps" name="children"
                                       id="guest-form-children"
                                       class="form-input" v-model.number="guest.children">
                                <div class="form-error" v-show="errors.children"
                                     v-text="errors.children"></div>
                            </div>
                            <div class="form-row">
                                <label for="guest-form-special_request" class="form-label">Special
                                    Request</label>
                                <textarea name="special_request" id="guest-form-special_request"
                                          class="form-input" rows="4" maxlength="250"
                                          v-model.trim="guest.special_request"></textarea>
                                <div class="text-right font-italic">{{ guest.special_request.length }} / 250 characters</div>
                                <div class="form-error" v-show="errors.special_request"
                                     v-text="errors.special_request"></div>
                            </div>
                        </div>
                    </fieldset>
                    <div class="gform_footer">
                        <button type="submit" class="dgt-btn" :disabled="busy">
                            Checkout
                            <i v-show="busy" class="fa fa-refresh fa-spin fa-fw"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
