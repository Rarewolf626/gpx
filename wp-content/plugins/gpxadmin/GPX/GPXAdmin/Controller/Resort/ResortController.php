<?php

namespace GPX\GPXAdmin\Controller\Resort;

use GPX\Model\Resort;
use GPX\Form\Admin\Resort\SearchResortsForm;

class ResortController {
    public function index(SearchResortsForm $form) {
        $search = $form->search();

        return gpx_render_blade('admin::resorts.index', compact('search'), false);
    }

    public function search(SearchResortsForm $form) {
        $search = $form->search();
        $resorts = Resort::query()
                         ->select(['id', 'ResortName', 'Town', 'Region', 'Country', 'ai', 'taID', 'active'])
                         ->adminSearch($search)
                         ->paginate($search->limit, ['*'], 'pg', $search->page)
                         ->setPageName('pg');

        wp_send_json([
            'pagination' => [
                'page' => $resorts->currentPage(),
                'total' => $resorts->total(),
                'first' => $resorts->firstItem(),
                'last' => $resorts->lastItem(),
                'limit' => $resorts->perPage(),
                'pages' => $resorts->lastPage(),
                'prev' => $resorts->previousPageUrl(),
                'next' => $resorts->nextPageUrl(),
                'elements' => gpx_pagination_window($resorts),
            ],
            'resorts' => $resorts->map(function (Resort $resort) {
                return [
                    'id' => $resort->id,
                    'view' => gpx_admin_route('resorts_edit', ['id' => $resort->id]),
                    'resort' => $resort->ResortName,
                    'city' => $resort->Town,
                    'region' => $resort->Region,
                    'country' => $resort->Country,
                    'ai' => $resort->ai,
                    'trip_advisor' => $resort->taID,
                    'active' => $resort->active,
                ];
            }),
        ]);
    }
}
