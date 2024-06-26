<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
    modelValue: {
        disabled: boolean,
        message: string,
    },
}>()

let busy = ref(false);
let editing = ref(false);
let disabled = ref(props.modelValue.disabled);
let message = ref(props.modelValue.message);

const emit = defineEmits(['update:modelValue']);

const edit = () => {
    message.value = props.modelValue.message;
    editing.value = true;
}

const cancel = () => {
    editing.value = false;
    message.value = props.modelValue.message;
}

const toggle = () => {
    disabled.value = !disabled.value;
    submit();
}

const submit = () => {
    busy.value = true;
    axios.post('/gpxadmin/dashboard/booking', {disabled: disabled.value, message: message.value})
        .then(response => {
            disabled.value = response.data.data.disabled;
            message.value = response.data.data.message;
            editing.value = false;
            emit('update:modelValue', {
                disabled: disabled.value,
                message: message.value,
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
                <label class="control-label" style="margin-right:5px;" for="dashboard-alert-message">Booking Disabled</label>
                <button type="button" class="btn btn-xs" style="margin-right:5px;" :class="{'btn-danger': !disabled, 'btn-success': disabled}" v-text="disabled ? 'Active' : 'Inactive'" :disabled="busy" @click="toggle" />
                <button v-show="!editing" type="button" class="btn btn-link btn-xs" @click="edit"><i class="fa fa-pencil"></i></button>
            </div>
            <div>
                <textarea id="dashboard-alert-message" class="form-control" v-model="message" :disabled="!editing || busy" />
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
