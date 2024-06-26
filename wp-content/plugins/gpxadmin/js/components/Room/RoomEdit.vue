<script setup>
import {ref, computed, onMounted, onUnmounted} from 'vue'
import {parse as parseDate, format as formatDate, add as addDate, sub as subDate} from 'date-fns';

const props = defineProps({
    id: {
        type: Number,
        required: true
    },
    room: {
        type: Object,
        required: true
    },
    disabled: {
        type: Boolean,
        default: false
    },
    resorts: {
        type: Array,
        default: () => []
    },
    unit_types: {
        type: Array,
        default: () => []
    },
})
const today = formatDate(new Date(), 'yyyy-MM-dd');
const tomorrow = formatDate(addDate(new Date(), {days: 1}), 'yyyy-MM-dd');
const nextWeek = formatDate(addDate(new Date(), {days: 7}), 'yyyy-MM-dd');
const uid = Math.random().toString(36).substring(2, 15);
const busy = ref(false);
const form = ref({
    resort_confirmation_number: props.room.resort_confirmation_number,
    check_in_date: props.room.check_in_date || today,
    check_out_date: props.room.check_out_date,
    resort: props.room.resort,
    unit_type: props.room.unit_type,
    source_num: props.room.source_num,
    source_partner: props.room.source_partner,
    source_partner_id: props.room.source_partner_id,
    active: props.room.active,
    active_type: props.room.active_type,
    active_specific_date: props.room.active_specific_date,
    active_week_month: props.room.active_week_month,
    availability: props.room.availability,
    available_to_partner: props.room.available_to_partner,
    available_to_partner_id: props.room.available_to_partner_id,
    type: props.room.type,
    price: props.room.price,
    active_rental_push_date: props.room.active_rental_push_date,
    note: props.room.note,
});
const success_modal = ref(null);
const resort = ref(null);
const source_partner = ref(null);
const available_to_partner = ref(null);
const resorts = ref(props.resorts);
const unit_types = ref(props.unit_types);
const errors = ref({});
const min_checkout_date = computed(() => {
    if (!form.value.check_in_date) return tomorrow;
    const checkin = parseDate(form.value.check_in_date, 'yyyy-MM-dd', new Date());
    return formatDate(addDate(checkin, {days: 1}), 'yyyy-MM-dd');
});

onMounted(() => {
    const checkin = parseDate(form.value.check_in_date, 'yyyy-MM-dd', new Date());
    if (!form.value.check_out_date) {
        form.value.check_out_date = formatDate(addDate(checkin, {days: 7}), 'yyyy-MM-dd');
    }
    if (!form.value.active_specific_date) {
        form.value.active_specific_date = formatDate(subDate(checkin, {years: 1}), 'yyyy-MM-dd');
    }
    jQuery(resort.value).select2().on('change', (event) => {
        form.value.resort = parseInt(event.target.value) || null;
        loadUnitTypes();
    })

    jQuery(source_partner.value).autocomplete({
        source: (request, response) => {
            jQuery.ajax({
                url: `${window.ajaxurl || '/wp-admin/admin-ajax.php'}?action=partner_autocomplete`,
                type: 'post',
                dataType: "json",
                data: {
                    search: request.term,
                    type: form.value.source_num
                },
                success: (data) => response(data)
            });
        },
        select: (event, ui) => {
            form.value.source_partner_id = parseInt(ui.item.value || 0);
            form.value.source_partner = ui.item.label?.trim() || null;
            return false;
        }
    });

    jQuery(available_to_partner.value).autocomplete({
        source: (request, response) => {
            jQuery.ajax({
                url: `${window.ajaxurl || '/wp-admin/admin-ajax.php'}?action=partner_autocomplete`,
                type: 'post',
                dataType: "json",
                data: {
                    search: request.term,
                    type: form.value.availability,
                    availabilty: true,
                },
                success: data => response(data)
            });
        },
        select: (event, ui) => {
            form.value.available_to_partner_id = parseInt(ui.item.value || 0);
            form.value.available_to_partner = ui.item.label?.trim() || null;
            return false;
        }
    });
})
onUnmounted(() => {
    jQuery(source_partner.value).autocomplete('dispose');
    jQuery(available_to_partner.value).autocomplete('dispose');
    jQuery(resort.value).select2('destroy');
})

const loadUnitTypes = () => {
    if (!form.value.resort) {
        unit_types.value = [];
        return;
    }
    busy.value = true;
    axios.get(`/gpxadmin/resort_unittypes/?resort_id=${form.value.resort}`)
        .then(response => {
            form.value.unit_type = null;
            unit_types.value = response.data;
        })
        .finally(() => busy.value = false);
};
const updateCheckOutDate = () => {
    if (!form.value.check_in_date) {
        return;
    }
    const checkOutDate = parseDate(form.value.check_in_date, 'yyyy-MM-dd', new Date());
    form.value.check_out_date = formatDate(addDate(checkOutDate, {days: 7}), 'yyyy-MM-dd');
};

const submit = () => {
    if(busy.value || props.disabled) return;
    axios.post(`/gpxadmin/room_update/?id=${props.id}`, form.value)
        .then(response => {
            jQuery(success_modal.value).modal();
        })
        .catch(error => {
            if(error.response?.status === 422) {
                errors.value = error.response.data.errors;
            }
        })
        .finally(() => busy.value = false);
};

</script>

<template>
    <form @submit.prevent="submit" class="form-horizontal form-label-left usage_exclude" novalidate>
        <fieldset :disabled="busy || disabled">
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" :for="`${uid}-resort_confirmation_number`">
                    Resort confirmation number
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" :id="`${uid}-resort_confirmation_number`" name="resort_confirmation_number"
                           class="form-control col-md-7 col-xs-12"
                           :class="{ 'parsley-error': !!errors.resort_confirmation_number }"
                           maxlength="30"
                           v-model.trim="form.resort_confirmation_number"
                    />
                    <div v-if="errors.resort_confirmation_number" class="form-error"
                         v-text="errors.resort_confirmation_number[0]"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" :for="`${uid}-check_in_date`">
                    Check In Date
                    <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="date" :id="`${uid}-check_in_date`" name="check_in_date"
                           class="form-control w-auto"
                           required
                           :class="{ 'parsley-error': !!errors.check_in_date }"
                           v-model="form.check_in_date"
                           :min="today"
                           @input="updateCheckOutDate"
                    />
                    <div v-if="errors.check_in_date" class="form-error" v-text="errors.check_in_date[0]"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" :for="`${uid}-check_out_date`">
                    Check Out Date
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="date" :id="`${uid}-check_out_date`" name="check_out_date"
                           class="form-control w-auto"
                           :class="{ 'parsley-error': !!errors.check_out_date }"
                           v-model="form.check_out_date"
                           :min="min_checkout_date"
                    />
                    <div v-if="errors.check_out_date" class="form-error" v-text="errors.check_out_date[0]"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-xs-12" :for="`${uid}-resort`">
                    Resort
                    <span class="required">*</span>
                </label>
                <div class="col-sm-6 col-xs-12">
                    <select ref="resort" :id="`${uid}-resort`" name="resort" required
                            class="form-control"
                            :class="{ 'parsley-error': !!errors.resort }"
                            v-model.number="form.resort"
                            @change="loadUnitTypes"
                    >
                        <option :value="null">Please Select</option>
                        <option v-for="resort in resorts" :value="resort.id" v-text="resort.name"/>
                    </select>
                    <div v-if="errors.resort" class="form-error" v-text="errors.resort[0]"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" :for="`${uid}-unit_type_id`">
                    Unit Type
                    <span class="required">*</span>
                </label>
                <div class="col-sm-6 col-xs-12">
                    <select :id="`${uid}-unit_type_id`" name="unit_type"
                            class="form-control"
                            :class="{ 'parsley-error': !!errors.unit_type }"
                            v-model.number="form.unit_type"
                    >
                        <option :value="null">Please Select</option>
                        <option v-for="type in unit_types" :value="type.id" v-text="type.name"/>
                    </select>
                    <div v-if="errors.unit_type" class="form-error" v-text="errors.unit_type[0]"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" :for="`${uid}-source`">
                    Source
                    <span class="required">*</span>
                </label>
                <div class="col-sm-6 col-xs-12">
                    <select :id="`${uid}-source`" name="source_num" required
                            class="form-control"
                            :class="{ 'parsley-error': !!errors.source_num }"
                            v-model.number="form.source_num"
                    >
                        <option :value="null" v-text="'Please Select'"/>
                        <option :value="1" v-text="'Owner'"/>
                        <option :value="2" v-text="'GPR'"/>
                        <option :value="3" v-text="'Trade Partner'"/>
                    </select>
                    <div v-if="errors.source_num" class="form-error" v-text="errors.source_num[0]"/>
                </div>
            </div>
            <div v-show="[1,3].includes(form.source_num)" class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" :for="`${uid}-source_partner`">
                    Source Partner
                </label>
                <div class="col-sm-6 col-xs-12">
                    <input ref="source_partner" :id="`${uid}-source_partner`" type="text" name="source_partner"
                           class="form-control"
                           :class="{ 'parsley-error': !!errors.source_partner_id }"
                           :value="form.source_partner"
                    />
                    <input type="hidden" name="source_partner_id" :value="form.source_partner_id">
                    <div v-if="errors.source_partner_id" class="form-error" v-text="errors.source_partner_id[0]"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">
                    Active
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="radio-inline">
                        <label>
                            <input type="radio" name="active" :value="true" v-model="form.active"/>
                            True
                        </label>
                    </div>
                    <div class="radio-inline">
                        <label>
                            <input type="radio" name="active" :value="false" v-model="form.active"/>
                            False
                        </label>
                    </div>
                    <div v-if="errors.active" class="form-error" v-text="errors.active[0]"/>
                </div>
            </div>
            <div v-show="!form.active" class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" :for="`${uid}-active_type`">
                    Display Date
                </label>
                <div class="col-sm-6 col-xs-12">
                    <div style="display:flex;justify-content:flex-start;align-items:center;gap:10px;">
                        <select :id="`${uid}-active_type`" name="active_type"
                                class="form-control"
                                :class="{ 'parsley-error': !!errors.active_type }"
                                v-model="form.active_type"
                        >
                            <option :value="null" v-text="'Please Select'"/>
                            <option value="date" v-text="'Select Date'"/>
                            <option value="weeks" v-text="'Select Weeks Before Check-in'"/>
                            <option value="months" v-text="'Select Months Before Check-in'"/>
                        </select>
                        <div v-show="form.active_type">
                            <input v-show="form.active_type === 'date'" type="date" name="active_specific_date"
                                   class="form-control col-md-7 col-xs-12"
                                   :class="{ 'parsley-error': !!errors.active_specific_date }"
                                   v-model="form.active_specific_date"
                            />
                            <input v-show="['weeks','months'].includes(form.active_type)" type="number"
                                   name="active_week_month" min="0"
                                   max="50" step="1"
                                   class="form-control col-md-7 col-xs-12"
                                   :class="{ 'parsley-error': !!errors.active_week_month }"
                                   v-model.number="form.active_week_month"
                            />
                        </div>
                    </div>
                    <div v-if="errors.active_type" class="form-error" v-text="errors.active_type[0]"/>
                    <div v-if="errors.active_specific_date" class="form-error" v-text="errors.active_specific_date[0]"/>
                    <div v-if="errors.active_week_month" class="form-error" v-text="errors.active_week_month[0]"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" :for="`${uid}-availability`">
                    Availability
                    <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <select :id="`${uid}-availability`" name="availability" required
                            class="form-control"
                            :class="{ 'parsley-error': !!errors.availability }"
                            v-model="form.availability"
                    >
                        <option :value="null">Please Select</option>
                        <option :value="1">All</option>
                        <option :value="2">Owner Only</option>
                        <option :value="3">Partner Only</option>
                    </select>
                    <div v-if="errors.availability" class="form-error" v-text="errors.availability[0]"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" :for="`${uid}-available_to_partner`">
                    Available To
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input ref="available_to_partner" :id="`${uid}-available_to_partner`" type="text"
                           class="form-control"
                           :class="{ 'parsley-error': !!errors.available_to_partner_id }"
                           :value="form.available_to_partner"
                    />
                    <input type="hidden" :value="form.available_to_partner_id" name="available_to_partner_id"/>
                    <div v-if="errors.available_to_partner_id" class="form-error"
                         v-text="errors.available_to_partner_id[0]"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" :for="`${uid}-type`">
                    Type
                    <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <select :id="`${uid}-type`" name="type" required
                            class="form-control"
                            :class="{ 'parsley-error': !!errors.type }"
                            v-model.number="form.type"
                    >
                        <option :value="3">Exchange/Rental</option>
                        <option :value="1">Exchange</option>
                        <option :value="2">Rental</option>
                    </select>
                    <div v-if="errors.type" class="form-error" v-text="errors.type[0]"/>
                </div>
            </div>
            <div v-show="form.type !== 1">
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" :for="`${uid}-price`">
                        Price
                        <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="number" :id="`${uid}-price`" name="price" :min="room.min_price"
                               step="0.01" required
                               class="form-control"
                               :class="{ 'parsley-error': !!errors.price }"
                               v-model.number="form.price"
                        />
                        <div v-if="errors.price" class="form-error" v-text="errors.price[0]"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" :for="`${uid}-rental_push_date`">
                        Rental Available
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="date" :id="`${uid}-rental_push_date`" name="active_rental_push_date"
                               class="form-control w-auto"
                               :class="{ 'parsley-error': !!errors.active_rental_push_date }"
                               v-model="form.active_rental_push_date"
                        />
                        <div v-if="errors.active_rental_push_date" class="form-error"
                             v-text="errors.active_rental_push_date[0]"/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" :for="`${uid}-note`">
                    Note
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" :id="`${uid}-note`" name="note"
                           class="form-control"
                           maxlength="300"
                           :class="{ 'parsley-error': !!errors.note }"
                           v-model.trim="form.note"
                    />
                    <div v-if="errors.note" class="form-error" v-text="errors.note[0]"/>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <button id="submit-button" type="submit" class="btn btn-success" :disabled="busy || disabled">
                        Submit
                        <i v-show="busy" class="fa fa-circle-o-notch fa-spin fa-fw"/>
                    </button>
                </div>
            </div>
        </fieldset>

        <div ref="success_modal" class="modal fade">
            <div class="modal-dialog modal-confirm">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="icon-box">
                            <i class="material-icons">&#xE876;</i>
                        </div>
                        <h4 class="modal-title">Done!</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-center">Room updated Successfully.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success btn-block" data-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<style scoped>

</style>
