<?php

use GPX\GPXAdmin\Router\GpxAdminRouter;
use GPX\Exception\NoMatchingRouteException;

register_activation_hook(__FILE__, function () {
    add_rewrite_endpoint('gpx', EP_ROOT);
    add_rewrite_endpoint('gpxadmin', EP_ROOT);
    flush_rewrite_rules();
});
register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});
add_action('init', function () {
    add_rewrite_endpoint('gpx', EP_ROOT);
    add_rewrite_endpoint('gpxadmin', EP_ROOT);
});
add_action('template_redirect', function () {
    global $wp_query;
    global $gpx_is_endpoint;
    $gpx_is_endpoint = false;
    $is_admin = false;
    if (empty($wp_query->query_vars['gpx']) && empty($wp_query->query_vars['gpxadmin'])) {
        return;
    }
    $path = $wp_query->query_vars['gpx'];
    if (!empty($wp_query->query_vars['gpxadmin'])) {
        $path = $wp_query->query_vars['gpxadmin'];
        $is_admin = true;
    }
    if ($is_admin && !gpx_is_administrator()) {
        // admin endpoints require admin login
        if (gpx_request()->wantsJson()) {
            wp_send_json(['error' => 'Unauthorized'], 403);
        }
        wp_redirect(wp_login_url() . '?redirect_to=' . urlencode(gpx_request()->fullUrl()));

        return;
    }
    $endpoint = preg_replace("/[^a-z0-9_]/", "", str_replace('/', '_', mb_strtolower($path)));
    if (empty($endpoint)) {
        return;
    }
    $gpx_is_endpoint = true;
    $function = $is_admin ? "gpxadmin_endpoint_{$endpoint}" : "gpx_endpoint_{$endpoint}";
    if (function_exists($function)) {
        if ($wp_query->is_404) {
            $wp_query->is_404 = false;
        }
        status_header(200);
        echo call_user_func($function);

        return;
    }

    if ($is_admin) {
        try {
            /** @var GpxAdminRouter $router */
            $router = gpx(GpxAdminRouter::class);
            $router->dispatchApi($endpoint);
            if ($wp_query->is_404) {
                $wp_query->is_404 = false;
            }
            status_header(200);

            return;
        } catch (NoMatchingRouteException $e) {
        }
    }

    $gpx_is_endpoint = false;
    $wp_query->set_404();
    status_header(404);
});

add_action('template_include', function ($template) {
    global $gpx_is_endpoint, $wp_query;
    if (!$gpx_is_endpoint || $wp_query->is_404) return $template;

    return GPXADMIN_PLUGIN_DIR . '/templates/blank.php';
});

function gpx_url(string $path, array $params = []): string {
    $endpoint = preg_replace("/[^a-z0-9_]/", "", str_replace('/', '_', mb_strtolower($path)));
    $url = site_url('gpx/' . trim($endpoint, '/') . '/');
    if ($params) {
        $url .= '?' . http_build_query($params);
    }

    return $url;
}

function gpxadmin_url(string $path, array $params = []): string {
    $endpoint = preg_replace("/[^a-z0-9_]/", "", str_replace('/', '_', mb_strtolower($path)));
    $url = site_url('gpxadmin/' . trim($endpoint, '/') . '/');
    if ($params) {
        $url .= '?' . http_build_query($params);
    }

    return $url;
}

//function gpx_endpoint_hello() {
//    return 'Hello World';
//}
