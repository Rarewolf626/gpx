<?php

namespace GPX\GPXAdmin\Controller\Region;

use GPX\Model\Category;
use GPX\Model\Region;
use GPX\Repository\RegionRepository;

class RegionController
{
    public function tree()
    {
        $regions = Region::orderBy('lft')->get()->toTree();
        $errors = Region::countErrors();
        $countries = Category::orderBy('country')->get();

        return gpx_admin_view('regions/tree', compact('regions', 'errors', 'countries'), false);
    }
}
