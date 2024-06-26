<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
    modelValue: string,
}>()

let busy = ref(false);
let editing = ref(false);
let error = ref(props.modelValue);

const emit = defineEmits(['update:modelValue']);

const edit = () => {
    error.value = props.modelValue;
    editing.value = true;
}

const cancel = () => {
    editing.value = false;
    error.value = props.modelValue;
}

const submit = () => {
    busy.value = true;
    axios.post('/gpxadmin/dashboard/hold', {field: 'error', error: error.value})
        .then(response => {
            error.value = response.data.data.error;
            editing.value = false;
            emit('update:modelValue', error.value);
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
                    <label class="control-label" style="margin-right:5px;" for="dashboard-booking-error">Hold Limit Error Message</label>
                    <button v-show="!editing" type="button" class="btn btn-link btn-xs" @click="edit"><i class="fa fa-pencil"></i></button>
                </div>
                <div>
                    <input type="text" id="dashboard-booking-error" class="form-control" v-model="error" :disabled="!editing || busy" />
                </div>

            </div>


        <div v-show="editing" class="form-group">
            <button type="submit" class="btn btn-primary" :disabled="!editing || busy">Save</button>
            <button type="button" class="btn btn-default" :disabled="!editing || busy" @click="cancel">Cancel</button>
        </div>
    </form>
</template>

<style scoped>

</style>
