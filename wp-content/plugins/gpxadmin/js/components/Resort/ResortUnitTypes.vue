<script setup>
import {ref} from 'vue';

const props = defineProps({
    resort_id: {
        type: Number,
        required: true
    },
    unit_types: {
        type: Array,
        required: true
    }
})

const busy = ref(false);
const message = ref('');
const errors = ref({});
const types = ref(props.unit_types);
const form = ref({
    record_id: null,
    name: '',
    number_of_bedrooms: 'STD',
    bedrooms_override: '',
    sleeps_total: 2
});

const sleeps = Array.from({length: 11}, (v, k) => k + 2);

const clear = () => {
    form.value = {
        record_id: null,
        name: '',
        number_of_bedrooms: 'STD',
        bedrooms_override: '',
        sleeps_total: 2
    };
    message.value = '';
    errors.value = {};
};

const edit = (unit_type) => {
    message.value = '';
    errors.value = {};
    form.value = {
        record_id: unit_type.record_id,
        name: unit_type.name,
        number_of_bedrooms: unit_type.number_of_bedrooms,
        bedrooms_override: unit_type.bedrooms_override,
        sleeps_total: unit_type.sleeps_total
    };
};

const submit = () => {
    if(busy.value) return;
    busy.value = true;
    message.value = '';
    errors.value = {};
    const url = form.value.record_id ? '/gpxadmin/resort_unittype_edit/' : '/gpxadmin/resort_unittype_add/';
    axios.post(url, {resort_id: props.resort_id, ...form.value})
        .then(response => {
            if (!response.data.success) {
                throw new Error(response.data.message || "Failed to save unit type");
            }
            if (form.value.record_id) {
                const index = types.value.findIndex(t => t.record_id === form.value.record_id);
                types.value.splice(index, 1, response.data.unit_type);
            } else {
                types.value.push(response.data.unit_type);
            }
            clear();
            busy.value = false;
        })
        .catch(error => {
            if (error.response?.status === 422) {
                errors.value = error.response.data.errors;
            }
            message.value = error.response?.data?.message || error.message || "Failed to save unit type";
            busy.value = false;
        });
};

const deleteUnittype = (unit_type) => {
    if(busy.value) return;
    busy.value = true;
    message.value = '';
    errors.value = {};
    axios.post(`/gpxadmin/resort_unittype_delete/`, {resort_id: props.resort_id, record_id: unit_type.record_id})
        .then(response => {
            if (!response.data.success) {
                throw new Error(response.data.message || "Failed to delete unit type");
            }
            const index = types.value.findIndex(t => t.record_id === unit_type.record_id);
            types.value.splice(index, 1);
            clear();
            busy.value = false;
        })
        .catch(error => {
            message.value = error.response?.data?.message || error.message || "Failed to delete unit type";
            busy.value = false;
        });
};

</script>

<template>
    <div class="row">
        <div class="col-xs-12 col-sm-7">
            <form @submit.prevent="submit" class="form-horizontal form-label-left usage_exclude" novalidate>
                <div class="form-group">
                    <h4>{{ form.record_id ? 'Edit' : 'Add' }} Unit Type</h4>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="resort-unittype-name">Name<span
                        class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" id="resort-unittype-name" name="name" required
                               class="form-control col-md-7 col-xs-12" v-model="form.name"/>
                        <div v-if="errors.name" class="form-error" v-text="errors.name[0]"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="resort-unittype-number_of_bedrooms">
                        Number of Bedrooms<span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <select name="number_of_bedrooms" id="resort-unittype-number_of_bedrooms" class="form-control"
                                v-model="form.number_of_bedrooms" :disabled="busy">
                            <option value="STD">STD</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                        <div v-if="errors.number_of_bedrooms" class="form-error" v-text="errors.number_of_bedrooms[0]"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="resort-unittype-sleeps_total">
                        Sleeps Total<span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <select id="resort-unittype-sleeps_total" name="sleeps_total" class="form-control"
                                v-model="form.sleeps_total" :disabled="busy">
                            <option v-for="i in sleeps" :value="i" v-text="i"/>
                        </select>
                        <div v-if="errors.sleeps_total" class="form-error" v-text="errors.sleeps_total[0]"/>
                    </div>
                </div>
<!--                <div class="form-group">-->
<!--                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="resort-unittype-bedrooms_override">-->
<!--                        Exchange Rate Exception-->
<!--                    </label>-->
<!--                    <div class="col-md-6 col-sm-6 col-xs-12">-->
<!--                        <select name="bedrooms_override" id="resort-unittype-bedrooms_override" class="form-control"-->
<!--                                v-model="form.bedrooms_override" :disabled="busy">-->
<!--                            <option value="">No Exception</option>-->
<!--                            <option value="STD">STD</option>-->
<!--                            <option value="1">1</option>-->
<!--                            <option value="2">2</option>-->
<!--                            <option value="3">3</option>-->
<!--                        </select>-->
<!--                        <div v-if="errors.bedrooms_override" class="form-error" v-text="errors.bedrooms_override[0]"/>-->
<!--                        <div class="form-help">-->
<!--                            <div>Use this value instead of the Number of Bedrooms field for the purposes of calculating upgrade fees.</div>-->
<!--                            <div>-->
<!--                                <strong>Example:</strong>-->
<!--                                If the Number of Bedrooms is set to 1 and 2 is set as the Exchange Rate Exception, then no upgrade fee will be charged for a 2 bedroom unit.-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->

                <div class="form-group">
                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <button type="submit" class="btn btn-success" :disabled="busy">
                            {{ form.record_id ? 'Update' : 'Create' }}
                            <i v-show="busy" class="fa fa-circle-o-notch fa-spin fa-fw"/>
                        </button>
                        <button type="button" class="btn btn-secondary" @click="clear" :disabled="busy">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-xs-5">
            <h3>Unit Types</h3>
            <ul>
                <li v-for="unit_type in types" style="margin-bottom: 15px;" :key="unit_type.record_id">
                    <button type="button" class="btn btn-plain" @click="edit(unit_type)" :disabled="busy"
                            style="margin-right:5px;">
                        {{ unit_type.name }}
                        <i class="fa fa-pencil"></i>
                    </button>
                    <form class="form-unittype-delete" @submit.prevent="deleteUnittype(unit_type)"
                          style="display:inline;">
                        <input type="hidden" name="resort_id" :value="resort_id"/>
                        <input type="hidden" name="unit_id" :value="unit_type.record_id"/>
                        <button type="submit" class="btn btn-plain" style="color: #f00;" :disabled="busy">
                            <i class="fa fa-remove"></i>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>

</style>
