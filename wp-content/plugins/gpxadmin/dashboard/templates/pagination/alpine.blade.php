<nav aria-label="Page navigation">
    <template x-if="pagination.pages > 1">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            <template x-if="pagination.page == 1">
                <li class="disabled" aria-disabled="true"
                    aria-label="<?= esc_attr__('Previous', 'gpxadmin') ?>">
                    <span aria-hidden="true">&lsaquo;</span>
                </li>
            </template>

            <template x-if="pagination.page > 1">
                <li>
                    <a :href="pagination.prev" rel="prev" aria-label="<?= esc_attr__('Previous', 'gpxadmin') ?>"
                       @click.prevent="paginate(pagination.page - 1, pagination.prev)">&lsaquo;</a>
                </li>
            </template>

            <template x-for="(element, index) in pagination.elements" :key="index">
                <li :class="{'active': element.page && element.active, 'disabled': !element.page}">
                    <a :href="element.url || '#'" x-text="element.label" @click.prevent="paginate(element.page, element.url)"></a>
                </li>
            </template>

            {{-- Next Page Link --}}
            <template x-if="pagination.page < pagination.pages">
                <li>
                    <a :href="pagination.next" rel="next" aria-label="<?= esc_attr__('Next', 'gpxadmin') ?>"
                       @click.prevent="paginate(pagination.page + 1, pagination.next)">&rsaquo;</a>
                </li>
            </template>

            <template x-if="pagination.page == pagination.pages">
                <li class="disabled" aria-disabled="true"
                    aria-label="<?= esc_attr__('Next', 'gpxadmin') ?>">
                    <span aria-hidden="true">&rsaquo;</span>
                </li>
            </template>

        </ul>
    </template>
</nav>
