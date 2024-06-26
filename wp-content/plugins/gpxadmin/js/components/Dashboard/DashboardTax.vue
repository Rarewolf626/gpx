<script setup lang="ts">

const props = defineProps<{
    bonus: boolean,
    exchange: boolean,
}>()
</script>

<template>
    <div>
        <div class="form-group">
            <div>
                <label class="control-label" style="margin-right:5px;" for="dashboard-booking-time">Tax Transaction Types</label>
            </div>
            <div>
                <div class="btn-group" role="group" aria-label="Tax Transaction Types">
                    <button type="button" class="btn" :class="{'btn-success': bonus, 'btn-default': !bonus}">Bonus/Rental</button>
                    <button type="button" class="btn" :class="{'btn-success': exchange, 'btn-default': !exchange}">Exchange</button>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>

</style>
