<?php

/**
 * Template Name: Resort Profile Page
 * Theme: GPX
 */

use GPX\Api\TripAdvisor\TripAdvisor;
use GPX\Model\Enum\ResortPath;
use GPX\Repository\ResortRepository;

global $wpdb;

if (isset($_GET['resort'])) {
    $id = $_GET['resort'];
    $field = 'id';
} elseif (isset($_GET['resortName'])) {
    $id = $_GET['resortName'];
    $field = 'name';
} elseif (isset($_GET['ResortID'])) {
    $id = $_GET['ResortID'];
    $field = 'resort_id';
} else {
    $id = null;
    $field = null;
    gpx_show_404('Resort not Found', 'Sorry, we couldn\'t find the resort you\'re looking for.');
}
$resort = ResortRepository::instance()->get_resort($id, $field, ResortPath::PROFILE);

if (!$resort) {
    // resort was not found show blank result
    gpx_show_404('Resort not Found', 'Sorry, we couldn\'t find the resort you\'re looking for.');
}

$sql = $wpdb->prepare("SELECT DISTINCT number_of_bedrooms FROM wp_room a
                        INNER JOIN wp_unit_type b ON b.record_id=a.unit_type WHERE a.resort=%d AND number_of_bedrooms IS NOT NULL AND number_of_bedrooms != ''",
    $resort->id);
$resortBeds = $wpdb->get_col($sql);

$cid = gpx_get_switch_user_cookie();
if (isset($cid) && !empty($cid)) {
    save_search_resort($resort, ['cid' => $cid]);
}

$tripadvisor = null;
$totalstars = 0;
$reviews = 0;
$taURL = null;
$starsclass = null;

if (!empty($resort->taID) && $resort->taID != 1) {
    $ta = TripAdvisor::instance();
    try {
        $tripadvisor = $ta->location($resort->taID);

        foreach ($tripadvisor->review_rating_count as $tarKey => $tarValue) {
            $totalstars += $tarKey * $tarValue;
        }

        $reviews = (int)$tripadvisor->num_reviews;
        $stars = $reviews > 0 ? round(number_format($totalstars / $reviews, 1, '.', '') * 2) / 2 : 0;
        $starsclass = str_replace(".", "_", $stars);
        $taURL = $tripadvisor->web_url;
    } catch (Exception $e) {
        $tripadvisor = null;
        $totalstars = 0;
        $reviews = 0;
        $taURL = null;
        $starsclass = null;
    }
}

$months = [
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December',
];
$dsmonth = gpx_request('month', 'Any');
$dsmonth = in_array($dsmonth, $months) ? $dsmonth : 'Any';

$dsyear = gpx_request('yr', '');
// allowed values are this year + 3
$currentYear = (int)date('Y');
$maxyear = $currentYear + 1;
$dsyear = $dsyear ? max(min((int)$dsyear, $maxyear), $currentYear) : '';

$calendar_date = gpx_get_next_availability_date($resort->id, array_search($dsmonth, $months), $dsyear);

$calendar = [
    'WeekType' => null,
    'bedrooms' => null,
    'month' => str_pad($calendar_date['month'] ?? date('m'), 2, '0', STR_PAD_LEFT),
    'year' => $calendar_date['year'] ?? $currentYear,
];


?>
<?php get_header(); ?>
<?php while (have_posts()) : the_post(); ?>
    <div id="cid" data-cid="<?= $cid ?>"></div>
    <section class="w-banner w-results w-results-home w-profile new-style-result-banner">
        <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result rs-col-3">
            <li class="slider-item rsContent">
                <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt=""/>
            </li>
        </ul>
        <div class="w-options">
            <hgroup>

                <h1><?= esc_html($resort->ResortName) ?></h1>
                <h3><?= esc_html($resort->Town . ", " . $resort->Region . " " . $resort->Country) ?></h3>
            </hgroup>
            <a href="#" class="dgt-btn search show-availabilty cal-av-toggle"
               data-resortid="<?= esc_attr($resort->id) ?>">
                <span>Check Pricing & Availability</span>
                <i class="fa fa-th-large"></i>
            </a>
        </div>
    </section>

    <section class="resort-detail dgt-container">
        <?php get_template_part('template-parts/resort-profile', 'gallery', compact('resort')); ?>
        <?php get_template_part('template-parts/resort-profile',
            'info-detail',
            compact('resort', 'taURL', 'starsclass', 'reviews')); ?>
    </section>
    <section class="review bg-gray-light">
        <div class="dgt-container profile">
            <div class="overview w-list-availables">
                <div class="title">
                    <div class="close">
                        <i class="icon-close"></i>
                    </div>
                    <h4>Resort Overview</h4>
                </div>
                <div class="cnt-list cnt">
                    <div class="p">
                        <p><?= stripslashes($resort->Description); ?>
                    </div>
                </div>
            </div>

            <div class="w-list-availables" id="expand_4">
                <div class="title">
                    <div class="close">
                        <i class="icon-close"></i>
                    </div>
                    <h4>Availability</h4>
                    <div id="resort-availability-filter-summary" class="hidden" style="margin-right:10px;">
                        <?php if ($dsyear): ?>
                            Search Results for
                            <span><?= mb_strtolower($dsmonth) === 'any' ? 'All' : $dsmonth ?></span>
                            <span><?= $dsyear ?></span>
                        <?php endif; ?>
                    </div>
                    <div id="availiblity-calendar-btn">
                        <a href="#" class="dgt-btn search search-availability cal-av-toggle" id="search-availability"
                           data-resort="<?= esc_attr($resort->ResortID) ?>">
                            <span>Full Availability Calendar</span>
                            <i style="margin-left:5px;" class="fa fa-calendar-o"></i>
                        </a>
                        <a href="#" style="display: none;"
                           class="dgt-btn search show-availabilty cal-av-toggle show-availability-btn"
                           id="show-availability"

                            <?php if (!empty($dsyear)) { ?>
                                data-month="<?= esc_attr($dsmonth) ?>"
                                data-year="<?= esc_attr($dsyear) ?>"
                            <?php } ?>
                           data-resortid="<?= esc_attr($resort->id) ?>">
                            <span>Check Pricing & Availability</span>
                            <i style="margin-left:5px;" class="fa fa-th-large"></i>
                        </a>
                    </div>
                </div>
                <div class="cnt-list">
                    <div id="availability-cards"></div>
                    <section class="resort-availablility dgt-container">
                        <form method="get" action="<?= admin_url('admin-ajax.php') ?>" id="resort-calendar-filter">
                            <input type="hidden" name="action" value="resort_availability_calendar">
                            <input type="hidden" name="resort" value="<?= esc_attr($resort->id) ?>">
                            <h3>Filter Results</h3>
                            <p>
                                <select id="calendar-type" class="calendar-filter-select" name="WeekType"
                                        placeholder="Week Type">
                                    <option
                                        value="All" <?= !in_array($calendar['WeekType'], ['RentalWeek', 'BonusWeek', 'ExchangeWeek']) ? 'selected' : '' ?>>
                                        All Week Types
                                    </option>
                                    <option
                                        value="RentalWeek" <?= in_array($calendar['WeekType'], ['RentalWeek', 'BonusWeek']) ? 'selected' : '' ?>>
                                        Rental
                                    </option>
                                    <option
                                        value="ExchangeWeek" <?= $calendar['WeekType'] == 'ExchangeWeek' ? 'selected' : '' ?>>
                                        Exchange
                                    </option>
                                </select>
                            </p>
                            <p>
                                <select id="calendar-bedrooms" class="calendar-filter-select" name="bedrooms"
                                        placeholder="Bedrooms">
                                    <option
                                        value="Any" <?= !in_array($calendar['bedrooms'], $resortBeds) ? 'selected' : '' ?>>
                                        Any Bedrooms
                                    </option>
                                    <?php foreach ($resortBeds as $bedrooms): ?>
                                        <option
                                            value="<?= esc_attr($bedrooms) ?>" <?= $calendar['bedrooms'] == $bedrooms ? 'selected' : '' ?>>
                                            <?= $bedrooms == 'St' ? 'Studio' : esc_html(str_replace("b", ' Bedroom', $bedrooms)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </p>
                            <p>
                                <select id="calendar-month" class="calendar-filter-select" name="month">
                                    <?php foreach ($months as $value => $label): ?>
                                        <option
                                            value="<?= esc_attr($value) ?>" <?= $value == $calendar['month'] ? 'selected' : '' ?>>
                                            <?= esc_html($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </p>
                            <p>
                                <select id="calendar-year" class="calendar-filter-select" name="year">
                                    <?php for ($year = $currentYear; $year <= $maxyear; $year++): ?>
                                        <option
                                            value="<?= esc_attr($year) ?>" <?= $year == $calendar['year'] ? 'selected' : '' ?>>
                                            <?= esc_html($year) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </p>
                            <ul class="status status-block">
                                <li>
                                    <div class="status-exchange">
                                        <p>Exchange</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="status-rental">
                                        <p>Rental</p>
                                    </div>
                                </li>
                            </ul>
                        </form>
                        <section class="resort-calendar">
                            <header class="resort-calendar-header">
                                <h2 id="resort-calendar-title"><?= esc_html($months[$calendar['month']]) ?> <?= esc_html($calendar['year']) ?></h2>
                                <div class="fc-right">
                                    <button type="button" class="resort-calendar-nav resort-calendar-prev"
                                            data-direction="prev" aria-label="prev">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="resort-calendar-nav resort-calendar-next"
                                            data-direction="next" aria-label="next">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                </div>
                            </header>
                            <div id="resort-calendar"></div>
                        </section>
                    </section>
                </div>
            </div>
            <div class="w-list-availables" id="expand_1">
                <?php get_template_part('template-parts/resort-profile', 'amenities', compact('resort')); ?>
            </div>
            <div class="w-list-availables" id="expand_2">
                <?php get_template_part('template-parts/resort-profile', 'unit', compact('resort')); ?>
            </div>
            <div class="w-list-availables" id="expand_3">
                <?php get_template_part('template-parts/resort-profile',
                    'important-information',
                    compact('resort')); ?>
            </div>
            <div class="w-list-availables" id="expand_3">
                <?php get_template_part('template-parts/resort-profile', 'ada', compact('resort')); ?>
            </div>
        </div>
    </section>

    <?php echo do_shortcode('[websitetour id="18526"]'); ?>
<?php endwhile; ?>
<?php get_footer(); ?>
