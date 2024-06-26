<?php

namespace GPX\GPXAdmin\Controller\CustomRequests;

use Illuminate\Support\Carbon;

class CustomRequestMatchTesterController {

    public function index() {
        if(!current_user_can( 'administrator' ) ) {
            exit;
        }
        $data = ['last_run' => null];
        $path = WP_CONTENT_DIR . '/logs/custom-request-checker.log';
        if (is_file($path)) {
            $data['last_run'] = Carbon::createFromTimestamp(filemtime($path));
        }

        return gpx_render_blade('admin::customrequests.matcher', $data, false);
    }

    public function review() {

    }
}
