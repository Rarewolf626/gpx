<script setup>
import {ref} from 'vue';

const props = defineProps({
    region: {
        type: Object,
        required: true
    },
});
const busy = ref(false);
const submit = () => {
    if (busy.value) return;
    if (!confirm("Are you sure you want to remove this record?\nAll associated data will be moved to the parent region.\nThis action cannot be undone!")) {
        return;
    }
    busy.value = true;
    axios.post('/gpxadmin/region/delete/', {id: props.region.id})
        .then(response => {
            if (response.data.success) {
                // redirect
                window.location = response.data.redirect;
                return;
            } else {
                let message = response.data.message || 'Failed to delete region';
                busy.value = false;
                alert(message);
            }
        })
        .catch(error => {
            let message = error.response?.data.message || 'Failed to delete region';
            busy.value = false;
            alert(message);
        });
};
</script>

<template>
    <form @submit.prevent="submit" class="d-inline-block">
        <button type="submit" class="btn btn-danger" :disabled="busy">
            Remove {{ region.name }}
            <i v-show="busy" class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i>
        </button>
    </form>
</template>

