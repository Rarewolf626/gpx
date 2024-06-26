<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
    modelValue: number,
}>()

let busy = ref(false);
let editing = ref(false);
let time = ref(props.modelValue);

const emit = defineEmits(['update:modelValue']);

const edit = () => {
    time.value = props.modelValue;
    editing.value = true;
}

const cancel = () => {
    editing.value = false;
    time.value = props.modelValue;
}

const submit = () => {
    busy.value = true;
    axios.post('/gpxadmin/dashboard/hold', {field: 'time', time: time.value})
        .then(response => {
            time.value = response.data.data.time;
            editing.value = false;
            emit('update:modelValue', time.value);
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
                    <label class="control-label" style="margin-right:5px;" for="dashboard-booking-time">Hold Limit Time (in hours)</label>
                    <button v-show="!editing" type="button" class="btn btn-link btn-xs" @click="edit"><i class="fa fa-pencil"></i></button>
                </div>
                <div>
                    <input type="number" min="1" step="1" id="dashboard-booking-time" class="form-control" v-model.number="time" :disabled="!editing || busy" />
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
