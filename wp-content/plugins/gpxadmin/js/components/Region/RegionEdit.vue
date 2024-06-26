<script setup>
import {ref} from 'vue';

const props = defineProps({
    region: {
        type: Object,
        required: true
    },
    countries: {
        type: Array,
        required: true
    },
    regions: {
        type: Array,
        required: true
    }
});
const busy = ref(false);
const success = ref(false);
const form = ref({
    name: props.region.name,
    CountryID: props.region.CountryID,
    parent: props.region.parent,
    displayName: props.region.displayName,
    ddHidden: props.region.ddHidden,
    featured: props.region.featured,
    show_resort_fees: props.region.show_resort_fees,
});
const errors = ref({});
const message = ref('');

const ancestors = ref(props.region.ancestors
    .map(function (parent) {
        return {
            id: parent.id,
            name: parent.name,
            parent: parent.parent,
            siblings: props.regions.filter(region => {
                if (region.id === props.region.id) return false;
                if (parent.parent === 1) {
                    return region.CountryID === parent.CountryID && region.parent === 1;
                }
                return region.parent === parent.parent;
            }).sort(({lft: a}, {lft: b}) => b - a)
        }
    })
);
const countryChanged = (e) => {
    let country = props.countries.find(country => country.id == e.target.value);
    ancestors.value = [
        {
            id: e.target.value,
            name: country?.name,
            parent: null,
            siblings: props.regions.filter(region => {
                if (region.id === props.region.id) return false;
                if (region.parent !== 1) return false;

                return region.CountryID == e.target.value;
            }).sort(({lft: a}, {lft: b}) => b - a)
        }
    ];
    form.value.parent = null;
}
const ancestorChanged = (value, parent) => {
    let region = value ? props.regions.find(region => region.id == value) : props.regions.find(region => region.id == parent);
    let tree = [];
    let current = region;
    do {
        tree.push({
            id: current.id,
            name: current.name,
            parent: current.parent,
            siblings: props.regions.filter(region => {
                if (region.id === props.region.id) return false;
                if (current.parent === 1) {
                    return region.CountryID === current.CountryID && region.parent === 1;
                }
                return region.parent === current.parent;
            }).sort(({lft: a}, {lft: b}) => b - a)
        });
        current = props.regions.find(region => region.id == current.parent);
    } while (current);
    tree.reverse();
    current = region;
    if (props.regions.find(p => p.parent == current.id)) {
        tree.push({
            id: parent,
            name: null,
            parent: current.id,
            siblings: props.regions.filter(region => {
                if (region.id === props.region.id) return false;
                if (region.parent === 1) return false;
                return current.id === region.parent;
            }).sort(({lft: a}, {lft: b}) => b - a)
        });
    }
    ancestors.value = tree;
    form.value.parent = value || null;
}


const submit = () => {
    busy.value = true;
    success.value = false;
    message.value = '';
    errors.value = {};
    axios.post('/gpxadmin/region/update/', {...form.value, id: props.region.id})
        .then(response => {
            console.log(response.data);
            message.value = response.data.message || 'Region was updated.';
            success.value = response.data.success;
            busy.value = false;
            setTimeout(() => {
                message.value = '';
            }, 4000);
        })
        .catch(error => {
            console.log(error);
            success.value = false;
            if (error.response.status === 422) {
                errors.value = error.response.data.errors;
            } else {
                message.value = error.response.data.message || 'Failed to save region.';
                setTimeout(() => {
                    message.value = '';
                }, 4000);
            }
            busy.value = false;
        });
};


</script>

<template>

    <form @submit.prevent="submit" novalidate>
        <fieldset class="row" :disabled="busy">
            <div class="col-md-6 col-md-offset-3">
                <div class="form-group">
                    <label for="region-CountryID" class="control-label required">Country</label>
                    <div>
                        <select id="region-CountryID" name="CountryID" class="form-control"
                                v-model.number="form.CountryID" @change="countryChanged">
                            <option v-for="country in countries" :key="country.id" :value="country.id"
                                    v-text="country.name"/>
                        </select>
                    </div>
                    <div v-if="errors.CountryID" v-text="errors.CountryID[0]" class="form-error"/>
                </div>
                <div v-for="(ancestor, index) in ancestors" :key="ancestor.id" class="form-group">
                    <label :for="`region-parent-${ancestor.id}`" class="control-label" :class="{required: index === 0}">Parent
                        Region</label>
                    <div>
                        <select :id="`region-parent-${ancestor.id}`" class="form-control"
                                @change="ancestorChanged(parseInt($event.target.value), ancestor.parent)">
                            <option value=""></option>
                            <option v-for="sibling in ancestor.siblings" :key="sibling.id" :value="sibling.id"
                                    v-text="sibling.name" :selected="sibling.id === ancestor.id"/>
                        </select>
                    </div>
                </div>
                <div v-if="errors.parent" v-text="errors.parent[0]" class="form-error"/>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="region-name" class="control-label required">Region Name</label>
                            <div>
                                <input type="text" id="region-name" name="name" class="form-control" v-model="form.name"
                                       required maxlength="255"
                                />
                                <div v-if="errors.name" v-text="errors.name[0]" class="form-error"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="region-displayName" class="control-label">Display Name</label>
                            <div>
                                <input type="text" id="region-displayName" name="displayName" class="form-control"
                                       v-model="form.displayName"
                                       maxlength="255"
                                />
                                <div v-if="errors.displayName" v-text="errors.displayName[0]" class="form-error"/>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="region-featured" class="control-label">Featured</label>
                            <div>
                                <select id="region-featured" name="featured" class="form-control"
                                        v-model="form.featured">
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                            </div>
                            <div v-if="errors.featured" v-text="errors.featured[0]" class="form-error"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="region-ddHidden" class="control-label">Hidden</label>
                            <div>
                                <select id="region-ddHidden" name="ddHidden" class="form-control"
                                        v-model="form.ddHidden">
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                            </div>
                            <div v-if="errors.ddHidden" v-text="errors.show_resort_fees[0]" class="form-error"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="region-show_resort_fees" class="control-label">Show Resort Fees</label>
                            <div>
                                <select id="region-show_resort_fees" name="show_resort_fees" class="form-control"
                                        v-model="form.show_resort_fees">
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                            </div>
                            <div v-if="errors.show_resort_fees" v-text="errors.show_resort_fees[0]" class="form-error"/>
                            <div class="form-help">This will affect all resorts in this region and its children.</div>
                        </div>
                    </div>
                </div>


            </div>
        </fieldset>
        <div class="ln_solid"></div>
        <div class="col-md-6 col-md-offset-3">
            <button type="submit" class="btn btn-success" :disabled="busy">Save</button>
            <div class="alert" :class="{'alert-success': success, 'alert-danger': !success}" v-if="message"
                 v-text="message"/>
        </div>
    </form>

</template>
