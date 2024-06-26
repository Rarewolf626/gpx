<script setup>
import {ref, onMounted, onUnmounted, nextTick} from 'vue'

const loading = ref(false);
const loaded = ref(false);
const busy = ref(false);
const open = ref(false);
const accepted = ref(false);
const credit_id = ref(null);
const amount = ref(0);
const interval = ref(null);
const message = ref('');

const load = async (id, options) => {
    credit_id.value = id;
    amount.value = options.amt;
    interval.value = options.interval;
    accepted.value = false;
    message.value = '';
    modal.activate('modal-extension');
    open.value = true;
    loaded.value = true;
};

const resetData = () => {
    loaded.value = false;
    credit_id.value = null;
    amount.value = null;
    interval.value = null;
    accepted.value = false;
    message.value = '';
    open.value = false;
};

const cancel = () => {
    modal.closeAll();
    resetData();
};

const accept = () => {
    if (!loaded.value || busy.value) return;
    message.value = '';
    accepted.value = true;
};

const addToCart = () => {
    if (!loaded.value || !accepted.value || busy.value) return;
    busy.value = true;
    message.value = '';
    axios.post('/wp-admin/admin-ajax.php?action=gpx_add_extension_to_cart', {credit: credit_id.value})
        .then(response => {
            if (response.data.redirect) {
                // redirect to checkout
                window.location.href = response.data.redirect;
                return;
            }
            if (response.data.success) {
                modal.closeAll();
                resetData();
                if (response.data.message) {
                    window.alertModal.alert(response.data.message);
                }
                if (response.data.refresh) {
                    window.location.reload();
                    return;
                }
            } else {
                message.value = response.data.message || 'An error occurred. Please try again later.';
                accepted.value = false;
            }
            busy.value = false;
        })
        .catch(error => {
            message.value = error.response?.data?.message || 'An error occurred. Please try again later.';
            busy.value = false;
        })
};


const modal = new ModalManager();
onMounted(() => {
    modal.add('modal-extension');
    modal.get('modal-extension').el.addEventListener('closed', () => resetData());
})

onUnmounted(() => {
    modal.clear();
})

defineExpose({load})
</script>

<template>
    <div class="dgt-container g-w-modal">
        <div class="dialog__overlay" :class="{open: open}">
            <div id="modal-extension" class="dialog" :class="{open: open}"
                 data-width="400" data-close-button="true" data-move-to-body="false"
                 data-close-on-outside-click="false">
                <div class="w-modal">
                    <form @submit.prevent="addToCart" v-if="accepted">
                        <p>You will be required to pay a credit extension fee of $<span v-text="amount"></span>
                            to complete this transaction.</p>
                        <br><br>
                        <div v-if="message" v-text="message"></div>
                        <div
                            class="flex flex-col flex-justify-center flex-align-stretch usw-button max-w-none gap-em">
                            <button type="submit" class="dgt-btn w-full" :disabled="busy">
                                Add To Cart
                            </button>
                            <button type="button" class="dgt-btn af-agent-skip w-full" @click.prevent="cancel"
                                    :disabled="busy">
                                Cancel
                            </button>
                        </div>
                    </form>
                    <form @submit.prevent="accept" v-else>
                        <p>Are you sure you want to extend this deposit?</p>
                        <br/><br/>

                        <div v-if="message" v-text="message" class="alert alert-danger"/>

                        <div class="usw-button flex flex-justify-stretch gap-em w-full max-w-none">
                            <button type="submit" class="dgt-btn" :disabled="busy">Yes</button>
                            <button type="button" class="dgt-btn af-agent-skip" @click.prevent="cancel"
                                    :disabled="busy">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
p {
    font-size: 18px;
    font-weight: bold;
}

.date-extension {
    width: 100%;
    padding: 10px;
}

.alert {
    font-size: 16px;
    margin-bottom: 20px;
    color: red;
}
</style>
