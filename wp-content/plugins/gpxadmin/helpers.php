<?php

use GPX\Model\Address;
use GPX\Model\Addressable;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\UrlWindow;
use GPX\GPXAdmin\Router\GpxAdminRouter;
use GPX\Exception\TemplateNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

function gpx_search_month(): string {
    $months = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
    ];
    $month = $_REQUEST['month'] ?? $_REQUEST['select_month'] ?? '';
    if (!in_array($month, [...$months, 'Any', 'any'])) $month = '';

    return $month;
}

function gpx_search_year(): string {
    $year = (int) ($_REQUEST['yr'] ?? $_REQUEST['year'] ?? $_REQUEST['select_year'] ?? '') ?: '';
    if (!is_numeric($year) || (int) $year < (int) date('Y')) $year = '';

    return (string) $year;
}

function gpx_db_placeholders(array $data = [], string $placeholder = '%s'): string {
    return implode(',', array_fill(0, count($data), $placeholder));
}

/**
 * escape a string to be used as a mysql table or column name
 *
 * @param string $value
 *
 * @return string
 */
function gpx_esc_table(string $value): string {
    if (Str::contains($value, '.')) {
        // this is table.column or schema.table.column or schema.table
        // each section needs to be escaped separately
        return implode('.', array_map('gpx_esc_table', explode('.', $value)));
    }

    return '`' . preg_replace("/[^a-z0-9\\-_\$]/i", '', $value) . '`';
}

function gpx_esc_orderby($value): string {
    if (!in_array(mb_strtolower($value), ['asc', 'desc'])) {
        return 'ASC';
    }

    return mb_strtoupper($value);
}

function gpx_esc_like($value): string {
    global $wpdb;

    return $wpdb->esc_like($value);
}

function gpx_validate_date($date, $format = 'Y-m-d H:i:s'): bool {
    $d = DateTime::createFromFormat($format, $date);

    return $d && $d->format($format) == $date;
}

function gpx_report_roles(): array {
    global $wp_roles;

    return Arr::except($wp_roles->roles, [
        'editor',
        'author',
        'contributor',
        'subscriber',
        'wpsl_store_locator_manager',
        'gpx_member',
        'wpseo_manager',
        'wpseo_editor',
    ]);
}

function gpx_in_array_any($needles, $haystack) {
    return (bool) array_intersect($needles, $haystack);
}

function gpx_get_attribute_key(string $from = null, string $to = null, int $order = null): string {
    $attributeKey = '0';
    if (!empty($from)) {
        $from = date('Y-m-d 00:00:00', strtotime($from));
        $attributeKey = strtotime($from);
        if (!empty($order)) {
            $attributeKey += $order;
        }
    }
    if (!empty($to)) {
        $to = date('Y-m-d 00:00:00', strtotime($to));
        $attributeKey .= "_" . strtotime($to);
    }

    return $attributeKey;
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
    bool $currency = false,
    bool $integer = false
): float|int {
    if ($value === null || $value === '') {
        return $integer ? 0 : 0.00;
    }
    if (is_int($value)) {
        return $integer ? $value : (float) $value;
    }
    if (is_float($value)) {
        return $integer ? (int) $value : $value;
    }
    if ($currency || str_starts_with((string)$value, '$')) {
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

function gpx_money(string|float|int $value = null): \Money\Money {
    if ($value === null || $value === '') return new \Money\Money(0, new \Money\Currency('USD'));
    $is_currency = str_contains((string) $value, '$');
    $value = gpx_parse_number($value, $is_currency, false);

    return new \Money\Money($value * 100, new \Money\Currency('USD'));
}

function gpx_currency($value = null, bool $round = false, bool $force = true, bool $symbol = true): ?string {

    if (gettype($value) === 'string') {
        $value = str_replace(['$',','],'', $value);
    }
    if ($value instanceof Money\Money) {
        $value = $value->getAmount() / 100;
    }
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
    if ($value < 0) {
        return '-$' . number_format(abs($value), $round ? 0 : 2, '.', '');
    }
    return '$' . number_format($value, $round ? 0 : 2, '.', ',');
}

function gpx_format_address(
    Addressable $address, bool $html = true, array $fields = [
    'Address1',
    'Address2',
    'Town',
    'Region',
    'PostCode',
    'Country',
]
): string {
    $address = $address->toAddress();
    if ($address->isEmpty($fields)) return '';
    $data = $address->only($fields);
    $string = '';
    if ($html) $string = '<div class="address">' . "\n";

    if (isset($data['Name'])) {
        if ($html) {
            $string .= "\t" . '<div class="address-name">' . esc_html($data['Name']) . '</div>' . "\n";
        } else {
            $string .= $data['Name'] . "\n";
        }
    }
    if (isset($data['Address1']) || isset($data['Address2'])) {
        $line = trim(($data['Address1'] ?? '') . ' ' . ($data['Address2'] ?? ''));
        if ($html) {
            $string .= "\t" . '<div class="address-street">' . esc_html($line) . '</div>' . "\n";
        } else {
            $string .= $line . "\n";
        }
    }
    if (isset($data['Town']) || isset($data['Region']) || isset($data['PostCode'])) {
        $line = trim(($data['Town'] ?? '') . ', ' . ($data['Region'] ?? '') . ' ' . ($data['PostCode'] ?? ''));
        if ($html) {
            $string .= "\t" . '<div class="address-locality">' . esc_html($line) . '</div>' . "\n";
        } else {
            $string .= $line . "\n";
        }
    }
    if (isset($data['Country'])) {
        if ($html) {
            $string .= "\t" . '<div class="address-country">' . esc_html($data['Country']) . '</div>' . "\n";
        } else {
            $string .= $data['Country'] . "\n";
        }
    }
    if (isset($data['Phone'])) {
        if ($html) {
            $string .= "\t" . '<div class="address-phone">Phone: ' . esc_html(gpx_format_phone($data['Phone'])) . '</div>' . "\n";
        } else {
            $string .= 'Phone: ' . $data['Phone'] . "\n";
        }
    }
    if (isset($data['Fax'])) {
        if ($html) {
            $string .= "\t" . '<div class="address-fax">Fax: ' . esc_html($data['Fax']) . '</div>' . "\n";
        } else {
            $string .= 'Fax: ' . $data['Fax'] . "\n";
        }
    }
    if (isset($data['Email'])) {
        if ($html) {
            $string .= "\t" . '<div class="address-email">Email: ' . esc_html($data['Email']) . '</div>' . "\n";
        } else {
            $string .= 'Email: ' . $data['Email'] . "\n";
        }
    }

    if ($html) $string .= '</div>';

    return $html ? $string : trim($string);
}

function gpx_format_phone(?string $value = null, int $format = \libphonenumber\PhoneNumberFormat::NATIONAL): ?string {
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

function gpx_format_date($value = null, string $format = 'm/d/Y'): ?string {
    if (empty($value)) {
        return null;
    }
    if ($value instanceof DateTimeInterface) {
        return $value->format($format);
    }
    if (is_numeric($value)) {
        return date($format, $value);
    }
    if (is_string($value)) {
        $date = Carbon::parse($value);
        if ($date->isValid()) {
            return $date->format($format);
        }
    }

    return null;
}

function gpx_pagination_window(LengthAwarePaginator $paginator): array {
    $window = UrlWindow::make($paginator);

    return collect([
        $window['first'],
        is_array($window['slider']) ? '...' : null,
        $window['slider'],
        is_array($window['last']) ? '...' : null,
        $window['last'],
    ])->filter()
      ->map(function ($links) use ($paginator) {
          if (is_string($links)) {
              return [['url' => null, 'label' => $links, 'active' => false]];
          }

          return collect($links)->map(fn($url, $page) => [
              'url' => $url,
              'label' => (string) $page,
              'page' => $page,
              'active' => $paginator->currentPage() === $page,
          ])->values()->toArray();
      })->values()->flatten(1)->toArray();
}

function gpx_admin_route(string $page = '', array $params = []): string {
    /** $var GpxAdminRouter $router */
    static $router;
    if (!$router) {
        /** $var GpxAdminRouter $router */
        $router = gpx(GpxAdminRouter::class);
    }

    return $router->url($page, $params);
}

function gpx_admin_view(string $template, array $params = [], bool $echo = true): ?string {
    try {
        $view = gpx_render_blade($template, $params, true);
        if ($echo) {
            echo $view;

            return null;
        } else {
            return $view;
        }
    } catch (TemplateNotFoundException $e) {
        if (!str_ends_with($template, '.php')) $template .= '.php';
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
}

function gpx_render_blade(string $template, array $params = [], bool $render = true): View|string {
    /** @var \Illuminate\Contracts\View\Factory $renderer */
    static $renderer;
    if (!$renderer) $renderer = gpx('Illuminate\Contracts\View\Factory');
    if (!$renderer->exists($template)) {
        throw new TemplateNotFoundException("Template {$template} does not exist");
    }

    $view = $renderer->make($template, $params);
    if ($render) return $view->render();

    return $view;
}

function gpx_admin_header(string $active = '', bool $echo = true): ?string {
    $active = $active ?: 'dashboard';

    return gpx_admin_view('header', ['active' => $active, 'user_data' => null], $echo);
}

function gpx_admin_footer(bool $echo = true): ?string {
    return gpx_admin_view('footer', [], $echo);
}

function gpx_user_has_role(string|array $role = [], WP_User|int $user = null, bool $any = true): bool {
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

function gpx_is_legacy_preferred_member(int $cid = null): bool {
    $cid = $cid ?? gpx_get_switch_user_cookie();
    if (!$cid) {
        return false;
    }

    return get_user_meta($cid, 'GP_Preferred', true) === 'Yes';
}

function gpx_get_user_email(int $cid = null): ?string {
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

function gpx_expired_member_redirect(): void {
    if (is_user_logged_in() && gpx_user_has_role('gpx_member_-_expired')) {
        if (!headers_sent()) {
            wp_redirect('/404');
            exit;
        }
        echo '<script type="text/javascript"> location.href="/404"; </script>';
        exit;
    }
}

function gpx_esc_fuzzy_like(string $value = '', bool $strip_non_alphanumeric = true, bool $match_around = true): string {
    if ($value === '') return '%';
    if ($strip_non_alphanumeric) {
        $value = preg_replace("/[^a-z0-9%_-]/i", '', $value);
    }
    $value = implode('%', str_split($value));

    return $match_around ? '%' . $value . '%' : $value;
}

function gpx_is_administrator(bool $allow_call_center = true, int $cid = null): bool {
    $allowed = ['gpx_admin', 'administrator', 'administrator_plus'];
    if ($allow_call_center) $allowed[] = 'gpx_call_center';

    return check_user_role($allowed, $cid);
}

function gpx_admin_asset(string $path = null): ?string {
    if (empty($path)) {
        return null;
    }
    static $manifest;
    if (!$manifest) {
        $file = file_exists(GPXADMIN_DIR . '/dist/manifest.json') ? file_get_contents(GPXADMIN_DIR . '/dist/manifest.json') : '{}';
        $manifest = json_decode($file, true);
    }
    if (!array_key_exists($path, $manifest)) {
        return null;
    }

    return $manifest[$path];
}

function gpx_show_debug(bool $admin_only = false): bool {
    if (!defined('GPX_SHOW_DEBUG') || !GPX_SHOW_DEBUG) {
        return false;
    }
    if (!$admin_only) {
        return true;
    }

    return gpx_is_administrator();
}

function gpx_admin_notification_email(): string {
    return defined('GPX_NOTIFICATION_EMAILS') ? GPX_NOTIFICATION_EMAILS : get_option('admin_email', '');
}

function gpx_show_404(?string $title = null, ?string $message = null): void {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part('404', '', compact('title', 'message'));
    exit;
}

function gpx_search_string(string $value = null): string {
    // transliterate value
    $value = iconv("UTF-8", "ASCII//TRANSLIT", (string) $value);
    // replace dashes with space
    $value = str_replace('-', ' ', $value);
    // strip out all but a-z, 0-9, and spaces
    $value = preg_replace("/[^a-z0-9 ]/i", '', $value);
    // replace multiple consecutive spaces with a single space
    $value = preg_replace("/\\s{2,}/i", ' ', $value);

    return trim($value);
}

if (!function_exists('e')) {
    function e($text = null, bool $double_encode = false): string {
        $safe_text = wp_check_invalid_utf8($text ?? '');
        $safe_text = _wp_specialchars($safe_text, ENT_QUOTES, false, $double_encode);

        return apply_filters('esc_html', $safe_text, $text);
    }
}
