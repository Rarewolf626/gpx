<?php

register_activation_hook(__FILE__, function () {
    add_rewrite_endpoint('gpx-htmx', EP_ROOT);
    flush_rewrite_rules();
});
register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});
add_action('init', function () {
    add_rewrite_endpoint('gpx-htmx', EP_ROOT);
});
add_action('template_redirect', function () {
    global $wp_query;
    if (empty($wp_query->query_vars['gpx-htmx'])) {
        return;
    }
    $endpoint = preg_replace("/[^a-z0-9_]/", "", mb_strtolower($wp_query->query_vars['gpx-htmx']));
    if (empty($endpoint)) {
        return;
    }
    if (!function_exists("gpx_htmx_endpoint_{$endpoint}")) {
        $wp_query->set_404();
        status_header(404);
    }

    echo call_user_func("gpx_htmx_endpoint_{$endpoint}");
    exit;
});
