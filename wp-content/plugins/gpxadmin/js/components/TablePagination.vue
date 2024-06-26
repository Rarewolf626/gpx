<script setup>
const props = defineProps({
    busy: {
        type: Boolean,
        default: false
    },
    pagination: {
        type: Object,
        required: true
    },
    limits: {
        type: Array,
        default: () => [10, 20, 50, 100],
    }
});
const emit = defineEmits(['paginate', 'limit']);

const paginate = (page, url) => {
    if(!page || props.busy || page == props.pagination.page) return;
    emit('paginate', {page, url});
};

</script>

<template>
    <div v-if="pagination" class="pagination-wrapper">
        <div class="pagination-summary">
            <div>
                Showing <span v-text="pagination.first" /> to <span v-text="pagination.last" /> of
                <span v-text="pagination.total" /> rows
            </div>
            <div>
                <select :value="pagination.limit" @change="emit('limit', $event.target.value)">
                    <option v-for="limit in limits" :key="limit" :value="limit" v-text="limit" />
                </select>
                <span style="margin-left:1em;">rows per page</span>
            </div>
        </div>
        <div>
            <nav aria-label="Page navigation">

                    <ul v-if="pagination.pages > 1" class="pagination">
                        <li v-if="pagination.page == 1" class="disabled" aria-disabled="true" aria-label="Previous">
                            <span aria-hidden="true">&lsaquo;</span>
                        </li>

                        <li v-if="pagination.page > 1">
                            <a :href="pagination.prev" rel="prev" aria-label="Previous"
                               @click.prevent="paginate(pagination.page - 1, pagination.prev)">&lsaquo;</a>
                        </li>

                        <li v-for="(element, index) in pagination.elements" :key="index" :class="{'active': element.page && element.active, 'disabled': !element.page}">
                            <a :href="element.url || '#'" v-text="element.label" @click.prevent="paginate(element.page, element.url)" />
                        </li>

                        <li v-if="pagination.page < pagination.pages">
                            <a :href="pagination.next" rel="next" aria-label="Next"
                               @click.prevent="paginate(pagination.page + 1, pagination.next)">&rsaquo;</a>
                        </li>

                        <li v-if="pagination.page == pagination.pages" class="disabled" aria-disabled="true"
                            aria-label="Next">
                            <span aria-hidden="true">&rsaquo;</span>
                        </li>

                    </ul>
            </nav>
        </div>
    </div>
</template>

<style scoped>
.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 10px 0;
    gap: 20px;
}
.pagination-summary {
    display: flex;
    justify-content: start;
    align-items: center;
    gap: 20px;
}
</style>
