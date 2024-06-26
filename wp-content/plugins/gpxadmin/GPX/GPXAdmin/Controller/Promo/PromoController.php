<?php

namespace GPX\GPXAdmin\Controller\Promo;

use GPX\Model\Special;
use GPX\Form\Admin\Promo\SearchPromosForm;

class PromoController {
    public function index(SearchPromosForm $form) {
        $search = $form->search();

        return gpx_render_blade('admin::promos.index', compact('search'), false);
    }

    public function search(SearchPromosForm $form) {
        $search = $form->search();
        $records = Special::query()
                         ->select(['id', 'Type', 'Name', 'Properties', 'TravelStartDate', 'TravelEndDate', 'redeemed', 'Active', 'Slug'])
                         ->adminSearch($search)
                         ->paginate($search->limit, ['*'], 'pg', $search->page)
                         ->setPageName('pg');

        wp_send_json([
            'pagination' => [
                'page' => $records->currentPage(),
                'total' => $records->total(),
                'first' => $records->firstItem(),
                'last' => $records->lastItem(),
                'limit' => $records->perPage(),
                'pages' => $records->lastPage(),
                'prev' => $records->previousPageUrl(),
                'next' => $records->nextPageUrl(),
                'elements' => gpx_pagination_window($records),
            ],
            'records' => $records->map(function (Special $record) {
                return [
                    'id' => $record->id,
                    'edit' => gpx_admin_route('promos_edit', ['id' => $record->id]),
                    'page' => rtrim(get_permalink( '229' ), '/') . '/' . $record->Slug,
                    'type' => ucfirst( $record->Type ),
                    'name' => stripslashes( $record->Name ),
                    'slug' => $record->Slug,
                    'availability' => ucfirst( $record->Properties->availability ?? '' ),
                    'travel_start' => $record->TravelStartDate?->format( 'm/d/Y' ),
                    'travel_end' => $record->TravelEndDate?->format( 'm/d/Y' ),
                    'coupon' => $record->Type == 'coupon' ? ($record->redeemed ?: '') : 'N/A',
                    'active' => $record->Active,
                ];
            }),
        ]);
    }
}
