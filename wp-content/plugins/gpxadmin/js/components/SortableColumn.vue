<script setup>

const props = defineProps({
    label: {
        type: String,
        required: false
    },
    selected: {
        type: String,
        required: false
    },
    column: {
        type: String,
        required: true
    },
    dir: {
        type: String,
        required: false,
        default: 'asc',
        validator: (value) => ['asc', 'desc'].includes(value)
    }
})
</script>

<template>
    <th class="sortable">
        <a href="#" @click.prevent="$emit('sort', column)" class="sort">
            <slot>{{ label }}</slot>
            <i class="sort-icon fa" :class="{
                'sort-active': selected === column,
                'fa-sort sort-inactive': selected !== column,
                'fa-sort-asc': selected === column && dir === 'asc',
                'fa-sort-desc': selected === column && dir === 'desc'
            }"
            ></i>
        </a>
    </th>
</template>

<style scoped>
th.sortable {
    padding:0;
    margin:0;
}
a.sort {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1em;
    width: 100%;
    padding:5px 7px;
}
i.sort-icon {
    margin-left:auto;
    display: inline-block;
}
.sort-active {}
.sort-inactive {
    color: #ccc;
}
</style>
