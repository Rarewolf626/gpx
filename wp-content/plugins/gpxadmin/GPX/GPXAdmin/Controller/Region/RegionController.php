<?php

namespace GPX\GPXAdmin\Controller\Region;

use DB;
use GpxModel;
use GPX\Model\Region;
use GPX\Model\Resort;
use GPX\Model\Category;
use Illuminate\Support\Arr;
use GPX\Form\Admin\Region\RegionForm;
use GPX\Form\Admin\Region\SearchRegionsForm;

class RegionController {
    public function index() {
        return gpx_render_blade('admin::regions.index', [], false);
    }

    public function search(SearchRegionsForm $form) {
        $search = $form->search();
        $records = Region::adminSearch($search)->paginate($search->limit, ['*'], 'pg', $search->page)->setPageName('pg');
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
            'records' => $records->map(function (Region $region) {
                return [
                    'id' => $region->id,
                    'edit' => gpx_admin_route('regions_edit', ['id' => $region->id]),
                    'gpx' => $region->RegionID ? '' : 'Yes',
                    'region' => $region->name,
                    'display' => $region->displayName,
                    'parent' => $region->parent_name,
                ];
            }),
        ]);
    }

    public function tree() {
        $regions = Region::orderBy('lft')->get()->toTree();
        $errors = Region::countErrors();
        $countries = Category::orderBy('country')->get();

        return gpx_admin_view('regions/tree', compact('regions', 'errors', 'countries'), false);
    }

    public function edit(int $id) {
        $region = Region::select([
            'wp_gpxRegion.*',
            DB::raw("(SELECT wp_daeRegion.CountryID FROM wp_daeRegion WHERE wp_daeRegion.id = wp_gpxRegion.RegionID) as CountryID"),
        ])
                        ->with([
                            'ancestors' => fn($query) => $query
                                ->select([
                                    'id',
                                    'name',
                                    'lft',
                                    'rght',
                                    'parent',
                                    'RegionID',
                                    DB::raw("(SELECT wp_daeRegion.CountryID FROM wp_daeRegion WHERE wp_daeRegion.id = wp_gpxRegion.RegionID) as CountryID"),
                                ]),
                        ])
                        ->findOrFail($id);
        $regions = Region::select([
            'id',
            'name',
            'lft',
            'rght',
            'parent',
            'RegionID',
            DB::raw("(SELECT wp_daeRegion.CountryID FROM wp_daeRegion WHERE wp_daeRegion.id = wp_gpxRegion.RegionID) as CountryID"),
        ])
                         ->orderBy('lft')
                         ->get();
        $countries = Category::select(['country', 'CountryID'])->get();

        return gpx_render_blade('admin::regions.edit', compact('region', 'countries', 'regions'), false);
    }

    public function update() {
        $region = Region::findOrFail(gpx_request('id'));
        if ($region->RegionID !== null) {
            wp_send_json([
                'success' => false,
                'message' => 'You cannot edit a base region',
            ]);
        }
        /** @var RegionForm $form */
        $form = gpx(RegionForm::class);
        $values = $form->validate();
        $values['search_name'] = gpx_search_string($values['displayName'] ?: $values['name']);

        $region->fill(Arr::except($values, ['id', 'CountryID', 'parent']));
        if ($values['parent'] !== $region->parent) {
            $parent = Region::find($values['parent']);
            $region->appendToNode($parent);
        }
        $region->save();

        wp_send_json([
            'success' => true,
            'message' => 'Region updated successfully',
        ]);
    }

    public function featured() {
        $region = Region::findOrFail(gpx_request('id'));
        $region->featured = !$region->featured;
        $region->save();

        wp_send_json([
            'success' => true,
            'featured' => $region->featured,
            'message' => $region->featured ? 'Region is featured!' : 'Region is not featured!',
        ]);
    }

    public function hidden() {
        $region = Region::findOrFail(gpx_request('id'));
        $region->ddHidden = !$region->ddHidden;
        $region->save();

        wp_send_json([
            'success' => true,
            'hidden' => $region->ddHidden,
            'message' => $region->ddHidden ? 'Region is hidden!' : 'Region is not hidden!',
        ]);
    }

    public function remove() {
        $region = Region::findOrFail(gpx_request('id'));
        if ($region->RegionID !== null) {
            wp_send_json([
                'success' => false,
                'message' => 'You cannot remove a base region',
            ]);
        }
        $parent = $region->parent_region()->first();
        //reassign all resorts to parent
        Resort::where('gpxRegionID', $region->id)->update(['gpxRegionID' => $parent->id]);

        // also reasign all direct children to the parent
        Region::where('parent', $region->id)->update(['parent' => $parent->id]);

        // remove the existing record
        DB::table('wp_gpxRegion')->where('id', $region->id)->delete();

        $gpx_model = new GpxModel();
        $gpx_model->rebuild_tree(1, 0);

        wp_send_json([
            'success' => true,
            'msg' => 'Successfully removed region!',
            'redirect' => gpx_admin_route('regions_all'),
        ]);
    }
}
