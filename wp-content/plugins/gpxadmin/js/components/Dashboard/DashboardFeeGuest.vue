<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
    modelValue: {
        enabled: boolean,
        amount: number,
    },
}>()

let busy = ref(false);
let editing = ref(false);
let enabled = ref(props.modelValue.enabled);
let amount = ref(props.modelValue.amount);

const emit = defineEmits(['update:modelValue']);

const edit = () => {
    amount.value = props.modelValue.amount;
    editing.value = true;
}

const cancel = () => {
    editing.value = false;
    amount.value = props.modelValue.amount;
}

const toggle = () => {
    enabled.value = !enabled.value;
    submit();
}

const submit = () => {
    busy.value = true;
    axios.post('/gpxadmin/dashboard/fee', {field: 'guest', enabled: enabled.value, amount: amount.value})
        .then(response => {
            enabled.value = response.data.data.guest.enabled;
            amount.value = response.data.data.guest.amount;
            editing.value = false;
            emit('update:modelValue', {
                enabled: enabled.value,
                amount: amount.value,
            });
            busy.value = false;
        })
        .catch(error => {
            busy.value = false;
        })
}

</script>

<template>
    <form method="post" @submit.prevent="submit">
        <div class="form-group">
            <div>
                <label class="control-label" style="margin-right:5px;" for="dashboard-fee-guest-amount">Global Guest Fees</label>
                <button type="button" class="btn btn-xs" style="margin-right:5px;" :class="{'btn-danger': !enabled, 'btn-success': enabled}" v-text="enabled ? 'Active' : 'Inactive'" :disabled="busy" @click="toggle" />
                <button v-show="!editing" type="button" class="btn btn-link btn-xs" @click="edit"><i class="fa fa-pencil"></i></button>
            </div>
            <div class="input-group">
                <span class="input-group-addon">$</span>
                <input type="number" min="1" step="1" id="dashboard-fee-guest-amount" class="form-control" v-model.number="amount" :disabled="!editing || busy" />
                <span class="input-group-addon">.00</span>
            </div>
            <div v-show="editing" style="margin-top:5px;">
                <button type="submit" class="btn btn-primary" :disabled="!editing || busy">Save</button>
                <button type="button" class="btn btn-default" :disabled="!editing || busy" @click="cancel">Cancel</button>
            </div>
        </div>
    </form>
</template>

<style scoped>

</style>
