<script setup>
import countries from '../../data/countries.json';

defineProps(['modelValue'])
defineEmits(['update:modelValue'])

</script>

<template>
    <select :value="modelValue" @input="$emit('update:modelValue', $event.target.value)">
        <option value="">Country</option>
        <option v-for="country in countries" :value="country.abbr" v-text="country.name" />
    </select>
</template>

