<script setup>
import {ref} from 'vue';

const props = defineProps({
    region: {
        type: Object,
        required: true
    },
});
const featured = ref(props.region.featured);
const hidden = ref(props.region.hidden);
const busy = ref(false);
const message = ref('');

const submitFeatured = () => {
    if (busy.value) return;
    busy.value = true;
    message.value = '';
    axios.post('/gpxadmin/region/featured/', {id: props.region.id})
        .then(response => {
            featured.value = response.data.featured;
            message.value = response.data.message;
        })
        .catch(error => {
            message.value = error.response?.data.message || 'Failed to update featured status';
        })
        .finally(() => {
            busy.value = false;
            setTimeout(() => {
                message.value = '';
            }, 4000);
        });
};
const submitHidden = () => {
    if (busy.value) return;
    busy.value = true;
    message.value = '';
    axios.post('/gpxadmin/region/hidden/', {id: props.region.id})
        .then(response => {
            hidden.value = response.data.hidden;
            message.value = response.data.message;
        })
        .catch(error => {
            message.value = error.response?.data.message || 'Failed to update hidden status';
        })
        .finally(() => {
            busy.value = false;
            setTimeout(() => {
                message.value = '';
            }, 4000);
        });
};
</script>

<template>
    <div>
        <div>
            <form @submit.prevent="submitFeatured" class="d-inline-block">
                <button type="submit" class="btn btn-primary" :disabled="busy">
                    Featured
                    <i class="hidden-status fa" :class="featured ? 'fa-check-square' : 'fa-square'"
                       aria-hidden="true"></i>
                </button>
            </form>
            <form @submit.prevent="submitHidden" class="d-inline-block" :disabled="busy">
                <button type="submit" class="btn btn-primary">
                    Hidden
                    <i class="hidden-status fa" :class="hidden ? 'fa-check-square' : 'fa-square'"
                       aria-hidden="true"></i>
                </button>
            </form>
        </div>
        <div class="alert alert-success" v-if="message" v-text="message"/>
    </div>
</template>
