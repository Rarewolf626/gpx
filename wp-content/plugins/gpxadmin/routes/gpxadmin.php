<?php

use GPX\GPXAdmin\Router\GpxAdminRouter;
use GPX\GPXAdmin\Controller\DashboardController;
use GPX\GPXAdmin\Controller\Room\RoomController;
use GPX\GPXAdmin\Controller\Promo\PromoController;
use GPX\GPXAdmin\Controller\Region\RegionController;
use GPX\GPXAdmin\Controller\Resort\ResortController;
use GPX\GPXAdmin\Controller\Room\RoomHoldsController;
use GPX\GPXAdmin\Controller\Resort\AddResortController;
use GPX\GPXAdmin\Controller\Resort\ResortFeeController;
use GPX\GPXAdmin\Controller\Room\RoomTransactionsController;
use GPX\GPXAdmin\Controller\Resort\ResortUnitTypesController;
use GPX\GPXAdmin\Controller\Transactions\TransactionsController;
use GPX\GPXAdmin\Controller\Transactions\TransactionFeeController;
use GPX\GPXAdmin\Controller\Transactions\TransactionGuestController;
use GPX\GPXAdmin\Controller\Transactions\TransactionCancelController;
use GPX\GPXAdmin\Controller\CustomRequests\CustomRequestMatchTesterController;

/** @var GpxAdminRouter $router */
//$router = gpx(GpxAdminRouter::class);

// Admin Pages /wp-admin/admin.php?page=gpx-admin-page&gpx-pg={page}
$router->addPage('', [DashboardController::class ,'dashboard']);
$router->addPage('dashboard', [DashboardController::class ,'redirect']);
$router->addPage('promos_all', [PromoController::class, 'index']);
$router->addPage('resorts_add', AddResortController::class);
$router->addPage('room_edit', [RoomController::class, 'edit'], ['id']);
$router->addPage('customrequests_match', [CustomRequestMatchTesterController::class ,'index']);
$router->addPage('regions_all', [RegionController::class, 'index']);
$router->addPage('regions_edit', [RegionController::class, 'edit'], ['id']);
$router->addPage('regions_tree', [RegionController::class, 'tree']);
$router->addPage('resorts_all', [ResortController::class, 'index']);
$router->addPage('transactions_all', [TransactionsController::class ,'index']);
$router->addPage('transactions_view', [TransactionsController::class ,'show'], ['id']);

// Admin API Routes /gpxadmin/{page}
$router->addApi('dashboard_alert', [DashboardController::class ,'alert']);
$router->addApi('dashboard_booking', [DashboardController::class ,'booking']);
$router->addApi('dashboard_hold', [DashboardController::class ,'hold']);
$router->addApi('dashboard_fee', [DashboardController::class ,'fee']);
$router->addApi('promos_search', [PromoController::class, 'search']);
$router->addApi('regions_search', [RegionController::class, 'search']);
$router->addApi('region_update', [RegionController::class, 'update']);
$router->addApi('region_featured', [RegionController::class, 'featured']);
$router->addApi('region_delete', [RegionController::class, 'remove']);
$router->addApi('resort_search', [ResortController::class, 'search']);
$router->addApi('resort_fee_display', [ResortFeeController::class ,'display']);
$router->addApi('resort_unittypes', [RoomController::class ,'unitTypes'], ['resort_id']);
$router->addApi('resort_unittype_add', [ResortUnitTypesController::class ,'add']);
$router->addApi('resort_unittype_edit', [ResortUnitTypesController::class ,'edit']);
$router->addApi('resort_unittype_delete', [ResortUnitTypesController::class ,'destroy'], ['resort_id', 'unit_id']);
$router->addApi('room_update', [RoomController::class ,'update'], ['id']);
$router->addApi('room_delete', [RoomController::class ,'destroy'], ['id']);
$router->addApi('room_transactions', [RoomTransactionsController::class ,'index'], ['id']);
$router->addApi('room_holds', [RoomHoldsController::class ,'index'], ['id']);
$router->addApi('room_holds_details', [RoomHoldsController::class ,'details'], ['id']);
$router->addApi('transactions_search', [TransactionsController::class ,'search']);
$router->addApi('transactions_details', [TransactionsController::class ,'details']);
$router->addApi('transactions_guest', [TransactionGuestController::class ,'index']);
$router->addApi('transactions_guest_update', [TransactionGuestController::class ,'save']);
$router->addApi('transactions_cancellation', [TransactionCancelController::class ,'index']);
$router->addApi('transactions_cancel_details', [TransactionCancelController::class ,'details']);
$router->addApi('transactions_cancel', [TransactionCancelController::class ,'cancel']);
$router->addApi('transactions_fee_refund', TransactionFeeController::class);
