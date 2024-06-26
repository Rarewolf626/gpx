import {createApp} from 'vue';

import '@css/main.scss';
import '@dashboard/scss/custom.scss';

document.addEventListener("DOMContentLoaded", (event) => {

    const apps = [
        {el: 'gpxadmin-dashboard', component: () => import('./components/Dashboard/Dashboard.vue')},
        {el: 'gpxadmin-specials-table', component: () => import('./components/Promo/PromosTable.vue')},
        {el: 'gpxadmin-regions-table', component: () => import('./components/Region/RegionTable.vue')},
        {el: 'gpxadmin-region-edit', component: () => import('./components/Region/RegionEdit.vue')},
        {el: 'gpxadmin-region-featured', component: () => import('./components/Region/RegionFeatured.vue')},
        {el: 'gpxadmin-region-delete', component: () => import('./components/Region/RegionDelete.vue')},
        {el: 'gpxadmin-resorts-table', component: () => import('./components/Resort/ResortsTable.vue')},
        {el: 'gpxadmin-resort-unitypes', component: () => import('./components/Resort/ResortUnitTypes.vue')},
        {el: 'gpxadmin-resort-fee-display', component: () => import('./components/Resort/ResortFeeDisplay.vue')},
        {el: 'gpxadmin-transactions-table', component: () => import('./components/Transactions/TransactionsTable.vue')},
        {el: 'gpxadmin-transaction-details', component: () => import('./components/Transactions/TransactionDetails.vue')},
        {el: 'gpxadmin-owner-transactions', component: () => import('./components/Owner/OwnerTransactionsTable.vue')},
        {el: 'gpxadmin-room-edit', component: () => import('./components/Room/RoomEdit.vue')},
        {el: 'gpxadmin-room-delete', component: () => import('./components/Room/DeleteWeek.vue')},
        {el: 'gpxadmin-room-transactions', component: () => import('./components/Room/RoomTransactions.vue')},
        {el: 'gpxadmin-room-holds', component: () => import('./components/Room/RoomHolds.vue')},
    ];

    apps.forEach(app => {
        const el = document.getElementById(app.el);
        if(!el) return;
        app.component().then(Component => {
            const props = JSON.parse(el.dataset.props || '{}') || {};
            createApp(Component.default, props).mount(el);
        });
    });
});
