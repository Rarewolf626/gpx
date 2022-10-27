<?php

/**
 * Template Name: Resort Profile Page
 * Theme: GPX
 */

use GPX\Repository\ResortRepository;

global $wpdb;

if ( isset( $_GET['resort'] ) ) {
    $id = $_GET['resort'];
    $field = 'id';
} elseif ( isset( $_GET['resortName'] ) ) {
    $id = $_GET['resortName'];
    $field = 'name';
} elseif ( isset( $_GET['ResortID'] ) ) {
    $id = $_GET['ResortID'];
    $field = 'resort_id';
} else {
    $id = null;
    $field = null;
    gpx_show_404('Resort not Found', 'Sorry, we couldn\'t find the resort you\'re looking for.');
}
$resort = ResortRepository::instance()->get_resort($id, $field);
if ( ! $resort ) {
    // resort was not found show blank result
    gpx_show_404('Resort not Found', 'Sorry, we couldn\'t find the resort you\'re looking for.');
}

$sql        = $wpdb->prepare( "SELECT DISTINCT number_of_bedrooms FROM wp_room a
                        INNER JOIN wp_unit_type b ON b.record_id=a.unit_type WHERE a.resort=%s",
                              $resort->ResortID );
$resortBeds = $wpdb->get_results( $sql );

$cid        = gpx_get_switch_user_cookie();
if ( isset( $cid ) && ! empty( $cid ) ) {
    save_search_resort( $resort, [ 'cid' => $cid ] );
}

$totalstars = 0;
$reviews = 0;
$taURL = null;

if ( ! empty( $resort->taID ) && $resort->taID != 1 ) {
    $ta = new TARetrieve( GPXADMIN_API_URI, GPXADMIN_API_DIR );

    $tripadvisor = json_decode( $ta->location( $resort->taID ) );

    foreach ( $tripadvisor->review_rating_count as $tarKey => $tarValue ) {
        $totalstars += $tarKey * $tarValue;
    }

    $reviews = array_sum( (array) $tripadvisor->review_rating_count );

    $stars      = round( number_format( $totalstars / $reviews, 1 ) * 2 ) / 2;
    $starsclass = str_replace( ".", "_", $stars );
    $taURL      = $tripadvisor->web_url;
}
?>
<?php get_header(); ?>
<?php while ( have_posts() ) : the_post(); ?>
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

            <a href="#" class="dgt-btn search show-availabilty cal-av-toggle" data-resortid="<?= esc_attr($resort->id) ?>">
                <span>Check Pricing & Availability</span>
                <i class="fa fa-th-large"></i>
            </a>
        </div>
    </section>

        <section class="resort-detail dgt-container">
            <?php get_template_part( 'template-parts/resort-profile', 'gallery', compact('resort') ); ?>
            <?php get_template_part( 'template-parts/resort-profile', 'info-detail', compact('resort','taURL', 'starsclass', 'reviews') ); ?>
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
                            <p><?= $resort->Description; ?>
                        </div>
                    </div>
                </div>

                <div class="w-list-availables" id="expand_4">
                    <div id="availiblity-calendar-btn">
                        <a href="#" class="dgt-btn search search-availability cal-av-toggle" id="search-availability"
                           data-resort="<?= esc_attr($resort->ResortID) ?>">
                            <span>Availability Calendar</span>
                            <i class="icon-calendar"></i>
                        </a>
                        <?php
                        $dsmonth = date( 'F' );
                        if ( isset( $_GET['month'] ) && ! empty( $_GET['month'] ) ) {
                            $dsmonth        = $_GET['month'];
                            $allowed_values = [
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
                            if ( ! in_array( $dsmonth, $allowed_values ) ) {
                                $dsmonth = "Any";
                            }
                        }
                        $dsyear = date( 'Y' );
                        if ( isset( $_GET['yr'] ) && ! empty( $_GET['yr'] ) ) {
                            $dsyear = intval( $_GET['yr'] );
                            // allowed values are this year + 3
                            $minyear = date( 'Y' );
                            $maxyear = date( 'Y', strtotime( '+3 years' ) );
                            if ( $dsyear >= $maxyear || $dsyear < $minyear ) {
                                $dsyear = $minyear;
                            }
                        }
                        ?>
                        <a href="#" style="display: none;"
                           class="dgt-btn search show-availabilty cal-av-toggle show-availability-btn"
                           id="show-availability" data-month="<?= esc_attr($dsmonth) ?>" data-year="<?= esc_attr($dsyear) ?>"
                           data-resortid="<?= esc_attr( $resort->id ) ?>">
                            <span>Check Pricing & Availability</span>
                            <i class="fa fa-th-large"></i>
                        </a>
                    </div>
                    <div class="title">
                        <div class="close">
                            <i class="icon-close"></i>
                        </div>
                        <h4>Availability</h4>
                    </div>
                    <div class="cnt-list">
                        <div id="availability-cards"></div>
                        <section class="resort-availablility dgt-container">
                            <div id="resort-calendar-filter">
                                <h3>Filter Results</h3>
                                <p>
                                    <select id="calendar-type" class="dgt-select" name="calendar-type"
                                            placeholder="Week Type">
                                        <option value="All" selected></option>
                                        <option value="All">All</option>
                                        <option value="BonusWeek">Rental</option>
                                        <option value="ExchangeWeek">Exchange</option>
                                    </select>
                                </p>
                                <p>
                                    <select id="calendar-bedrooms" class="dgt-select" name="calendar-bedrooms"
                                            placeholder="Bedrooms">
                                        <option value="All" selected></option>
                                        <option value="All">All</option>
                                        <?php foreach ( $resortBeds as $bed ): ?>
                                            <option value="<?= esc_attr($bed->bedrooms) ?>">
                                                <?= $bed->bedrooms == 'St' ? 'Studio' : esc_html(str_replace( "b", ' Bedroom', $bed->bedrooms )) ?>
                                            </option>
                                            <?php endforeach; ?>
                                    </select>
                                </p>
                                <p>
                                    <select id="calendar-month" class="dgt-select" name="calendar-month"
                                            placeholder="Month">
                                        <option value="0" disabled selected></option>
                                        <?php
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
                                        foreach ( $months as $mkey => $month ) {
                                            ?>
                                            <option value="<?= esc_attr($mkey) ?>"><?= esc_html($month) ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </p>
                                <p>
                                    <select id="calendar-year" class="dgt-select" name="calendar-year"
                                            placeholder="Year">
                                        <option value="0" disabled selected></option>
                                        <?php
                                        $currentYear = date( 'Y' );
                                        for ( $z = $currentYear; $z <= $currentYear + 2; $z ++ ) {
                                            ?>
                                            <option value="<?= esc_attr($z) ?>"><?= esc_html($z) ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </p>
                                <p>
                                <ul class="status status-block">
                                    </li>
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
                                </p>
                            </div>
                            <div id="resort-calendar"></div>
                        </section>
                    </div>
                </div>
                <div class="w-list-availables" id="expand_1">
                    <?php get_template_part( 'template-parts/resort-profile', 'amenities', compact('resort') ); ?>
                </div>
                <div class="w-list-availables" id="expand_2">
                    <?php get_template_part( 'template-parts/resort-profile','unit', compact('resort') ); ?>
                </div>
                <div class="w-list-availables" id="expand_3">
                    <?php get_template_part( 'template-parts/resort-profile', 'important-information', compact('resort') ); ?>
                </div>
                <div class="w-list-availables" id="expand_3">
                    <?php get_template_part( 'template-parts/resort-profile', 'ada', compact('resort') ); ?>
                </div>
            </div>
        </section>

    <?php echo do_shortcode( '[websitetour id="18526"]' ); ?>
<?php endwhile; ?>
<?php get_footer(); ?>
