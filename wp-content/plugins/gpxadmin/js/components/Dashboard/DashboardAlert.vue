<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
    modelValue: {
        enabled: boolean,
        message: string,
    },
}>()

let busy = ref(false);
let editing = ref(false);
let enabled = ref(props.modelValue.enabled);
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
    enabled.value = !enabled.value;
    submit();
}

const submit = () => {
    busy.value = true;
    axios.post('/gpxadmin/dashboard/alert', {enabled: enabled.value, message: message.value})
        .then(response => {
            enabled.value = response.data.data.enabled;
            message.value = response.data.data.message;
            editing.value = false;
            emit('update:modelValue', {
                enabled: enabled.value,
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
                <label class="control-label" style="margin-right:5px;" for="dashboard-alert-message">Alert Splash Message</label>
                <button type="button" class="btn btn-xs" style="margin-right:5px;" :class="{'btn-danger': !enabled, 'btn-success': enabled}" v-text="enabled ? 'Active' : 'Inactive'" :disabled="busy" @click="toggle" />
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
