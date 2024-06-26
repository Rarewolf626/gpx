<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
    modelValue: number,
}>()

let busy = ref(false);
let editing = ref(false);
let value = ref(props.modelValue);

const emit = defineEmits(['update:modelValue']);

const edit = () => {
    value.value = props.modelValue;
    editing.value = true;
}

const cancel = () => {
    editing.value = false;
    value.value = props.modelValue;
}

const submit = () => {
    busy.value = true;
    axios.post('/gpxadmin/dashboard/fee', {field: 'extension', value: value.value})
        .then(response => {
            value.value = response.data.data.extension;
            editing.value = false;
            emit('update:modelValue', value.value);
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
                <label class="control-label" style="margin-right:5px;" for="dashboard-fee-extension">Extension Fee</label>
                <button v-show="!editing" type="button" class="btn btn-link btn-xs" @click="edit"><i class="fa fa-pencil"></i></button>
            </div>
            <div class="input-group">
                <span class="input-group-addon">$</span>
                <input type="number" min="1" step="1" id="dashboard-fee-extension" class="form-control" v-model.number="value" :disabled="!editing || busy" />
                <span class="input-group-addon">.00</span>
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
