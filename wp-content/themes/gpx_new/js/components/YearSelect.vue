<script setup>
import range from 'lodash/range';
defineProps(['modelValue'])
defineEmits(['update:modelValue'])

const min = (new Date()).getFullYear();
const max = min + 20;
const years = range(min, max);

</script>

<template>
    <select :value="modelValue" @input="$emit('update:modelValue', $event.target.value)">
        <option value="">Year</option>
        <option v-for="year in years" :value="year" v-text="year" />
    </select>
</template>

