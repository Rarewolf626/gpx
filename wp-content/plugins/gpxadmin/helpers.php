<?php

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use GPX\GPXAdmin\Router\GpxAdminRouter;


function gpx_search_month(): string
{
    $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $month = $_REQUEST['month'] ?? $_REQUEST['select_month'] ?? '';
    if (!in_array($month, [...$months, 'Any', 'any'])) $month = '';

    return $month;
}

function gpx_search_year(): string
{
    $year = (int)($_REQUEST['yr'] ?? $_REQUEST['year'] ?? $_REQUEST['select_year'] ?? '') ?: '';
    if (!is_numeric($year) || (int)$year < (int)date('Y')) $year = '';

    return (string)$year;
}


function gpx_db_placeholders(array $data = [], string $placeholder = '%s'): string
{
    return implode(',', array_fill(0, count($data), $placeholder));
}

/**
 * escape a string to be used as a mysql table or column name
 *
 * @param string $value
 *
 * @return string
 */
function gpx_esc_table(string $value): string
{
    if (Str::contains($value, '.')) {
        // this is table.column or schema.table.column or schema.table
        // each section needs to be escaped separately
        return implode('.', array_map('gpx_esc_table', explode('.', $value)));
    }

    return '`' . preg_replace("/[^a-z0-9\\-_\$]/i", '', $value) . '`';
}

function gpx_esc_orderby($value): string
{
    if (!in_array(mb_strtolower($value), ['asc', 'desc'])) {
        return 'ASC';
    }

    return mb_strtoupper($value);
}

function gpx_esc_like($value): string
{
    global $wpdb;

    return $wpdb->esc_like($value);
}

/**
 * Turns a localized, numeric or currency string into a number
 * Can handle commas and currency symbols
 *
 * @param string|float|int|null $value
 * @param bool $currency If true $value is a currency string, if false it is just a number string
 * @param bool $integer If true the value will be returned as integer, if false will be returned as
 *                                        float
 *
 * @return float|int
 */
function gpx_parse_number(
    string|float|int $value = null,
    bool             $currency = false,
    bool             $integer = false
): float|int
{
    if ($value === null || $value === '') {
        return $integer ? 0 : 0.00;
    }
    if (is_int($value)) {
        return $integer ? $value : (float)$value;
    }
    if (is_float($value)) {
        return $integer ? (int)$value : $value;
    }
    if ($currency) {
        $style = NumberFormatter::CURRENCY;
    } else {
        $style = $integer ? NumberFormatter::TYPE_INT64 : NumberFormatter::DECIMAL;
    }
    $fmt = numfmt_create('en_US', $style);
    $number = $fmt->parse($value, $integer ? NumberFormatter::TYPE_INT64 : NumberFormatter::TYPE_DOUBLE);
    if ($number === false) {
        return $integer ? 0 : 0.00;
    }

    return $number;
}

function gpx_currency($value = null, bool $round = false, bool $force = true, bool $symbol = true): ?string
{
    if (!is_numeric($value)) {
        $value = null;
    }
    if (null === $value) {
        if (!$force) {
            return null;
        }
        $value = 0.00;
    }
    if (!$symbol) {
        return number_format($value, $round ? 0 : 2, '.', '');
    }

    return '$' . number_format($value, $round ? 0 : 2, '.', ',');
}

function gpx_admin_route(string $page, array $params = []): string
{
    /** $var GpxAdminRouter $router */
    static $router;
    if (!$router) {
        /** $var GpxAdminRouter $router */
        $router = gpx(GpxAdminRouter::class);
    }

    return $router->url($page, $params);
}

function gpx_admin_view(string $template, array $params = [], bool $echo = true): ?string
{
    if(!str_ends_with($template, '.php')) $template .= '.php';
    $__gpx_admin_template = realpath(GPXADMIN_PLUGIN_DIR . '/templates/admin/' . $template);
    unset($template);
    if (!$__gpx_admin_template || !file_exists($__gpx_admin_template) || !Str::startsWith($__gpx_admin_template,
            GPXADMIN_PLUGIN_DIR)) {
        return null;
    }
    extract($params, EXTR_SKIP);
    if (array_key_exists('params', $params)) {
        $params = $params['params'];
    } else {
        unset($params);
    }
    if (!$echo) {
        ob_start();
    }
    require $__gpx_admin_template;
    if (!$echo) {
        return ob_get_clean();
    }

    return null;
}

function gpx_admin_header(string $active = '', bool $echo = true): ?string
{
    return gpx_admin_view('header.php', ['active' => $active], $echo);
}

function gpx_admin_footer(bool $echo = true): ?string
{
    return gpx_admin_view('footer.php', [], $echo);
}

function gpx_user_has_role(string|array $role = [], WP_User|int $user = null, bool $any = true): bool
{
    $role = Arr::wrap($role);
    if (is_numeric($user)) {
        $user = get_user_by('id', $user);
    }
    $user = $user ?: wp_get_current_user();
    if (!$user) {
        return false;
    }

    $roles = array_filter($role, fn($r) => in_array($r, $user->roles));
    if ($any) {
        return !empty($roles);
    }

    return count($roles) === count($role);
}

function gpx_get_user_email(int $cid = null): ?string
{
    if (!$cid) {
        $cid = gpx_get_switch_user_cookie();
    }
    if (!$cid) {
        return null;
    }
    static $repository;
    if (!$repository) {
        $repository = \GPX\Repository\OwnerRepository::instance();
    }

    return $repository->get_email($cid);
}

function gpx_expired_member_redirect(): void
{
    if (is_user_logged_in() && gpx_user_has_role('gpx_member_-_expired')) {
        if (!headers_sent()) {
            wp_redirect('/404');
            exit;
        }
        echo '<script type="text/javascript"> location.href="/404"; </script>';
        exit;
    }
}

function gpx_esc_fuzzy_like(string $value = '', bool $strip_non_alphanumeric = true, bool $match_around = true): string
{
    if ($value === '') return '%';
    if ($strip_non_alphanumeric) {
        $value = preg_replace("/[^a-z0-9%_-]/i", '', $value);
    }
    $value = implode('%', str_split($value));
    return $match_around ? '%' . $value . '%' : $value;
}

function gpx_is_administrator(): bool
{
    return check_user_role(['gpx_admin', 'gpx_call_center', 'administrator', 'administrator_plus']);
}

function gpx_show_debug(bool $admin_only = false): bool
{
    if (!defined('GPX_SHOW_DEBUG') || !GPX_SHOW_DEBUG) {
        return false;
    }
    if (!$admin_only) {
        return true;
    }

    return gpx_is_administrator();
}

function gpx_show_404(?string $title = null, ?string $message = null): void
{
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part('404', '', compact('title', 'message'));
    exit;
}

function gpx_format_phone(?string $value = null, int $format = \libphonenumber\PhoneNumberFormat::NATIONAL): ?string
{
    if (empty($value)) {
        return null;
    }

    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    try {
        $number = $phoneUtil->parse($value, "US");

        return $phoneUtil->format($number, $format);
    } catch (\libphonenumber\NumberParseException $e) {
        return $value;
    }
}

function gpx_search_string(string $value = null): string
{
    // transliterate value
    $value = iconv("UTF-8", "ASCII//TRANSLIT", (string)$value);
    // replace dashes with space
    $value = str_replace('-', ' ', $value);
    // strip out all but a-z, 0-9, and spaces
    $value = preg_replace("/[^a-z0-9 ]/i", '', $value);
    // replace multiple consecutive spaces with a single space
    $value = preg_replace("/\\s{2,}/i", ' ', $value);

    return trim($value);
}
