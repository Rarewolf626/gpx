<script setup>
import {ref} from 'vue'

const props = defineProps({
    resort: {
        type: Object,
        required: true
    }
});

const busy = ref(false);
const message = ref('');
const form = ref({
    enabled: props.resort.enabled || false,
    fee: props.resort.fee || 0,
    frequency: props.resort.frequency || 'weekly'
});

const submit = () => {
    if (busy.value) return;
    busy.value = true;
    message.value = '';

    axios.post('/gpxadmin/resort_fee_display/', {
        id: props.resort.id,
        settings: form.value
    }).then(response => {
        if (response.data.success) {
            form.value.enabled = response.data.settings.enabled;
            form.value.fee = response.data.settings.fee;
            form.value.frequency = response.data.settings.frequency;
        }
        showAlert(response.data.message, response.data.success);
        busy.value = false;
    }).catch(error => {
        let m = error.response?.data.message || 'An error occurred. Please try again.';
        if (error.response?.status !== 422) {
            showAlert(m, false);
        } else {
            message.value = m;
            setTimeout(() => message.value = '', 2000);
        }

        busy.value = false;
    });
};

const showAlert = (message, success = true) => {
    let alert = document.querySelector('.update-nag');
    if(!alert) return;
    if (success) {
        alert.classList.remove('nag-fail');
        alert.classList.add('nag-success');
    } else {
        alert.classList.remove('nag-success');
        alert.classList.add('nag-fail');
    }
    alert.innerText = message;
    alert.style.display = 'block';
    setTimeout(() => alert.style.display = 'none', 4000);
};
</script>

<template>
    <div class="well">
        <div>
            <div v-if="resort.region" class="alert alert-success">Resort Fees are currently shown for resorts in this region.</div>
            <div v-else class="alert alert-danger">
                Resort Fees are currently not shown for resorts in this region.<br>
                The settings below will not have an effect unless they are enabled for the region.
            </div>
        </div>
        <form @submit.prevent="submit" id="resort-resortfees-form">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="enabled" v-model="form.enabled" />
                        Calculate
                    </label>
                </div>
            </div>
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon">$</div>
                    <input id="resort-resortfees-fee" name="fee" class="form-control" type="number" step=".01"
                           v-model.number="form.fee" placeholder="0.00"
                           min="0.00" />
                </div>
            </div>
            <div class="form-group">
                <select class="form-control" name="frequency" v-model="form.frequency">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
        <div v-if="message" class="alert alert-danger" v-text="message" />
    </div>
</template>

<style scoped>
#resort-resortfees-form {
    display: flex;
    justify-content: start;
    align-items: center;
    gap: 20px;

    .form-group, .input-group, .btn {
        margin: 0;
    }
}
</style>
