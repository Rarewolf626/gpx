<?php
/**
 * @var int     $cid
 * @var wpdb    $wpdb
 * @var array   $filterNames
 * @var ?array  $allBedrooms
 * @var ?array  $resorts
 * @var array   $featuredresorts
 * @var ?bool   $newStyle
 * @var ?string $select_year
 * @var ?array $restrictIDs
 */

use GPX\Repository\WeekRepository;
use GPX\Repository\RegionRepository;

gpx_expired_member_redirect();
?>
<?php if ( ! empty( $lpCookie ) ): ?>
    <?php $expires = time() + ( 86400 * 30 ); ?>
    <div class="cookieset" data-name="lp_promo" data-value="<?= esc_attr( $lpCookie ) ?>"
         data-expires="<?= esc_attr( $expires ) ?>"></div>
<?php endif; ?>
<?php if ( ! empty( $savesearch['guest-searchSessionID'] ) ): ?>
    <?php $expires = time() + ( 86400 * 30 ); ?>
    <div class="cookieset" data-name="guest-searchSessionID"
         data-value="<?= esc_attr( $savesearch['guest-searchSessionID'] ) ?>"
         data-expires="<?= esc_attr( $expires ) ?>"></div>
<?php endif; ?>
<?php $bookingDisabeledClass = ''; ?>
<?php if ( get_option( 'gpx_booking_disabled_active' ) && is_user_logged_in() && gpx_user_has_role( 'gpx_member' ) ): ?>
    <?php $bookingDisabeledClass = 'booking-disabled'; ?>
    <?php $bookingDisabledMessage = get_option( 'gpx_booking_disabled_msg' ); ?>
    <div id="bookingDisabledMessage" class="booking-disabled-check"
         data-msg="<?= esc_attr( $bookingDisabledMessage ) ?>"></div>
<?php endif; ?>
<?php $held = WeekRepository::instance()->get_prehold_weeks( $cid ); ?>
<div class="dgt-container g-w-modal">
    <dialog class="dialog--filter" id="modal-filter" data-width="460">
        <div class="w-modal">
            <form action="">
                <div class="block">
                    <h2>Sort Results</h2>
                    <label for="select_cities" class="ada-text">Filter City</label>
                    <select id="select_cities" class="dgt-select filter_city" multiple="multiple"
                            data-filter="subregions" name="mySelect" placeholder="All Cities">
                        <?php foreach ( $filterNames as $filterNameKey => $filterNameValue ): ?>
                            <option value="<?= esc_attr( $filterNameKey ) ?>"><?= esc_html( $filterNameValue ) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="select_soonest" class="ada-text">Filter Soonest</label>
                    <select id="select_soonest" class="dgt-select" name="mySelect" placeholder="Date/Soonest to Latest">
                        <option value="1">Date/Soonest to Latest</option>
                        <option value="2">Date/Latest to Soonest</option>
                        <option value="3">Price/Lowest to Hightest</option>
                        <option value="4">Price/Highest to Lowest</option>
                    </select>
                    <h3>- Date Range</h3>
                    <a href="#" class="dgt-btn" id="checkin-btn">Check-In <span class="icon-date"></span></a>
                    <input id="rangepicker" style="display: none">
                </div>
                <div class="block">
                    <h2>Filter Results</h2>
                    <h3>- Unit Size (note: currently working on this)</h3>
                    <ul class="list-check">
                        <?php if ( isset( $allBedrooms ) ): ?>
                            <?php foreach ( $allBedrooms as $bkey => $bval ): ?>
                                <li>
                                    <input type="checkbox" class="filter_size" id="chk-<?= esc_attr( $bkey ) ?>"
                                           name="addsize[]" value="<?= esc_attr( $bkey ) ?>" data-type="size"
                                           data-filter="bedtype" placeholder="Studio">
                                    <label for="chk-<?= esc_attr( $bkey ) ?>"><?= esc_html( $bval ) ?></label>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <h3>- Type of Week</h3>
                    <ul class="list-check">
                        <li>
                            <input type="checkbox" class="filter_resorttype filter_data filter_group" id="chk-rental"
                                   data-type="type" data-filter="resorttype" name="addname[]" value="Rental Week"
                                   placeholder="Rental" checked>
                            <label for="chk-rental">Rental</label>
                        </li>
                        <li>
                            <input type="checkbox" class="filter_resorttype filter_data filter_group" id="chk-exchange"
                                   data-type="type" data-filter="resorttype" name="addname[]" value="Exchange Week"
                                   placeholder="Exchange" checked>
                            <label for="chk-exchange">Exchange</label>
                        </li>
                    </ul>
                    <h3>- Resort Type</h3>
                    <h5 class="aiActive">All Inclusive Resorts - <span id="aiNot"></span>Included in Results</h5>
                    <ul class="list-check">
                        <li>
                            <input type="checkbox" class="filter_ai" id="chk-all-inclusive" name="addai[]"
                                   data-type="type" data-filter="resorttype" value="00"
                                   placeholder="All-Inclusive Resorts Not Included">
                            <label for="chk-all-inclusive">All-Inclusive Resorts Not Included</label>
                        </li>
                        <li>
                            <input type="checkbox" id="filter_ai_dummy" name="filter_ai_dummy" checked>
                            <label for="filter_ai_dummy">All Inclusive Resorts Included</label>
                        </li>
                    </ul>
                </div>
            </form>
        </div>
    </dialog>
</div>
<?php if ( isset( $loginalert ) ): ?>
    <dialog id="modal-alert" data-width="460" data-min-height="135" data-open>
        <div class="w-modal">
            <div class="icon-alert"></div>
            <p>These specials are only available to logged in users. Please <a class="dgt-btn call-modal-login signin"
                                                                               href="#">Sign In</a> to see the promo
                price.</p>
        </div>
    </dialog>
<?php endif; ?>
<section class="w-banner w-results w-resulst-reset w-results-home new-style-result-banner">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result rs-col-3">
        <li class="slider-item rsContent"><img class="rsImg"
                                               src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg"
                                               alt=""/></li>
    </ul>
    <div class="dgt-container w-box result-page-form">
        <h2 class="hero-main-text">
            Search Results
        </h2>
    </div>
</section>
<?php include( locate_template( 'template-parts/universal-search-widget.php' ) ); ?>
<section class="w-filter dgt-container">
    <div class="left">
        <?php
        $cntResults = 0;
        if ( ! empty( $rp ) ) {
            $cntResults = count( $rp );
        } elseif ( ! empty( $props ) ) {
            $cntResults = count( $props );
        }
        ?>
        <h3><?= $cntResults ?> Search Results</h3>
        <?php
        if ( isset( $returnLink ) && ! empty( $returnLink ) ) {
            echo $returnLink;
        }
        ?>
    </div>
    <div class="right">
        <ul class="status">
            <li>
                <div class="status-all">
                    <p>All-Inclusive</p>
                </div>
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
        <div id="sticky"><a href="" class="dgt-btn call-modal-filter">Filter Results</a></div>
    </div>
</section>
<section class="w-featured bg-gray-light w-result-home">
    <ul class="w-list-view dgt-container" id="results-content">
        <?php
        if ( ! isset( $resorts ) && ! isset( $newStyle ) ) {
            if ( isset( $insiderweek ) ) {
                echo '<div style="text-align:center; margin: 30px 20px 40px 20px; "><h3 style="color:#cc0000;">You must be logged in to view this page</h3><p style="font-size:15px;">Please login below.</p></div>';
                $resorts      = $featuredresorts;
                $disableMonth = true;
            } else {
                echo '<div style="text-align:center; margin: 30px 20px 40px 20px; "><h3 style="color:#009bd9; font-size:30px; font-weight:normal;">Sorry, Your search didn\'t return any results</h3><p style="font-size:20px;">Please consider expanding your search criteria above, searching for a <a href="/resorts/" style="color:#152136">specific resort</a> or view our featured resorts below.</p></div>';
                $resorts      = $featuredresorts;
                $disableMonth = true;
            }
        }
        if ( ! empty( $resorts ) || isset( $newStyle ) ) {
            $i = 0;
            foreach ( $resorts as $resort ) {
                if ( ! isset($resort['props']) ) {
                    $resort['props'] = [];
                }
                if ( empty( $resort['resort']->ResortName ) ) {
                    continue;
                }
                ?>
                <li class="w-item-view filtered" id="rl<?= $i ?>"
                    data-subregions='["<?= $resort['resort']->gpxRegionID ?>"]'>
                    <a href="#" data-resortid="<?= $resort['resort']->RID ?? '' ?>"
                       class="hidden-more-button dgt-btn result-resort-availability">View Availability <i
                                class="fa fa-chevron-down" aria-hidden="true"></i></a>
                    <div class="view">
                        <div class="view-cnt">
                            <?php
                            $metaResortID = $resort['resort']->ResortID;
                            if ( empty( $metaResortID ) ) {
                                $metaResortID = $resort['resort']->resortId;
                            }
                            $imgThumb   = '';
                            $imageTitle = strtolower( $resort['resort']->ResortName );
                            $imageAlt   = $resort['resort']->ResortName;

                            //check for updated images
                            $sql             = $wpdb->prepare( "SELECT meta_value FROM wp_resorts_meta WHERE meta_key='images' AND ResortID=%s",
                                                               $metaResortID );
                            $rawResortImages = $wpdb->get_row( $sql );
                            if ( ! empty( $rawResortImages->meta_value ) ) {
                                $resortImages = json_decode( $rawResortImages->meta_value, true );
                                $oneImage     = $resortImages[0] ?? null;
                                if ( ! empty( $oneImage ) ) {
                                    $imgThumb = $oneImage['src'];
                                    if ( $oneImage['type'] == 'uploaded' ) {
                                        $id         = $oneImage['id'];
                                        $imageAlt   = get_post_meta( $id, '_wp_attachment_image_alt', true );
                                        $imageTitle = get_the_title( $id );
                                    }
                                }
                            }


                            if ( empty( $imgThumb ) ) {
                                $imgThumb = '/wp-content/themes/gpx_new/images/blank_pixel.png';
                            }
                            $resortLinkID = $resort['resort']->RID ?? null;
                            if ( empty( $resortLinkID ) ) {
                                $resortLinkID = $resort['resort']->id;
                            }
                            ?>
                            <img src="<?= esc_attr( $imgThumb ) ?>" alt="<?= esc_attr( $imageAlt ); ?>"
                                 title="<?= esc_attr( $imageTitle ) ?>">
                        </div>
                        <div class="view-cnt">
                            <div class="descrip">
                                <hgroup>
                                    <h2>
                                        <?= esc_html( $resort['resort']->ResortName ) ?>
                                    </h2>
                                    <span><?= esc_html( $resort['resort']->Town ) ?>, <?= esc_html( $resort['resort']->Region ) ?> <?= esc_html( $resort['resort']->Country ) ?></span>
                                </hgroup>
                                <p>
                                    <a href="/resort-profile?resort=<?= rawurlencode( $resortLinkID ) ?>"
                                       data-rid="<?= esc_attr( $resortLinkID ) ?>" data-cid="<?= esc_attr( $cid ) ?>"
                                       class="dgt-btn resort-btn">View Resort</a>
                                </p>
                                <?php if ( isset($newStyle) &&  $newStyle ): ?>
                                    <p style="margin-top: 10px">
                                        <?php if ( ! empty( $resort['props'] ) ): ?>
                                            <a href="#" data-resortid="<?= esc_attr( $resort['resort']->RID ) ?>"
                                               class="dgt-btn result-resort-availability">View Availability <i
                                                        class="fa fa-chevron-down" aria-hidden="true"></i></a>
                                        <?php else: ?>
                                            <a href="#modal-custom-request" data-cid="<?= esc_attr( $cid ) ?>"
                                               data-pid="" class="custom-request gold-link">No Availability — click to
                                                submit a custom request</a>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                                <ul class="status">
                                    <?php $status = [
                                        'status-exchange' => 'Exchange Week',
                                        'status-rental'   => 'Rental Week',
                                    ]; ?>
                                    <?php foreach ( $status as $key => $value ): ?>
                                        <?php if ( isset( $resort->WeekType ) && in_array( $value,
                                                                                           $resort->WeekType ) ): ?>
                                            <li>
                                                <div class="<?= esc_attr( $key ) ?>"></div>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if ( isset( $resort['resort']->AllInclusive ) && $resort['resort']->AllInclusive == '6' ): ?>
                                        <li>
                                            <div class="status-all"></div>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="w-status">
                                <div class="close">
                                    <i class="icon-close"></i>
                                </div>
                                <div class="result">
                                    <?php if ( ! isset( $disableMonth ) ): ?>
                                        <span class="count-result"><?= count( $resort['props'] ) ?> Results</span>
                                        <?php if ( isset( $_POST['select_month'] ) ): ?>
                                            <span class="date-result"><?= esc_html( $_POST['select_month'] ) ?> <?= esc_html( $select_year ?? null ) ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    $collapseAvailablity = '';
                    if ( isset($newStyle) && $newStyle ) {
                        $collapseAvailablity = 'collapse';
                        if ( empty( $resort['props'] ) ) {
                            $collapseAvailablity .= ' no-availability';
                        }
                    }
                    ?>

                    <ul id="gpx-listing-result-<?= $resort['resort']->RID ?? '' ?>"
                        class="w-list-result <?= $collapseAvailablity ?>">
                        <?php ksort( $resort['props'] ); ?>
                        <?php foreach ( $resort['props'] as $kp => $prop ): ?>
                        <?php
                            $aorb = 'b';
                            if ( strpos( $kp, 'a' ) !== false ) {
                                $aorb = 'a';
                            }
                            $wte = explode( $aorb, $kp );
                            if ( isset( $wte[1] ) ) {
                                $prop->WeekType = $wte[1];
                            }
                            if ( isset( $propType[ $kp ] ) ) {
                                $prop->WeekType = $propType[ $kp ];
                            }
                            $exchangeprice = get_option( 'gpx_exchange_fee' );
                            if ( isset( $propPrice ) && round( $propPrice[ $kp ] ) == round( $exchangeprice ) ) {
                                $prop->WeekType = 'ExchangeWeek';
                            }
                            if ( isset( $prefPropSetDets[ $kp ] ) ) {
                                $prop->specialPrice = $prefPropSetDets[ $kp ]['specialPrice'];
                            }
                            if ( isset( $propPrice ) && $propPrice[ $kp ] > 0 ) {
                                $prop->Price = $propPrice[ $kp ];
                            }
                            $prop->Price = gpx_parse_number($prop->Price);
                            $prop->WeekPrice = $prop->Price;
                            $prop->specialPrice = gpx_parse_number($prop->specialPrice);

                            if ( $prop->WeekType == 'ExchangeWeek' ) {
                                $prop->WeekType = 'Exchange Week';
                            } else {
                                $prop->WeekType = 'Rental Week';
                            }
                            $datadate   = date( 'Ymd', strtotime( $prop->checkIn ) );
                            $dddatadate = date( 'Y-m-d', strtotime( $prop->checkIn ) );
                            $chechbr    = strtolower( substr( $prop->bedrooms, 0, 1 ) );
                            if ( is_numeric( $chechbr ) ) {
                                $bedtype = $chechbr;
                            } elseif ( $chechbr == 's' ) {
                                $bedtype = 'Studio';
                            } else {
                                $bedtype = $prop->bedrooms;
                            }
                            $indPrice = $prop->Price;
                            if ( ! empty( $prop->specialPrice ) ) {
                                $indPrice = $prop->specialPrice;
                            }
                            $classes = '';
                            $cmpSP   = str_replace( ",", "", $prop->specialPrice );
                            $cmpP    = str_replace( ",", "", $prop->Price );
                            //check to see if Prevent Highlighting is set
                            if ( ! empty( $prop->specialPrice ) && ( $cmpSP - $cmpP != 0 ) && empty( $setPropDetails[ $prop->propkeyset ]['preventhighlight'] ) ) {
                                $classes .= ' active';
                            }
                            ?>
                            <li
                                    id="<?= esc_attr( 'prop' . str_replace( " ", "", $prop->WeekType ) . $prop->weekId ) ?>"
                                    class="item-result gpx-loading-disabled <?= esc_attr( $classes ) ?>"
                                    data-resorttype='["<?= esc_attr($prop->WeekType) ?>"<?= !empty( $prop->AllInclusive ) ? '", "' . esc_attr($prop->AllInclusive) . '"' : ''?>]'
                                    data-bedtype='["<?= esc_attr($bedtype) ?>"]'
                                    data-date='<?= esc_attr($dddatadate) ?>'
                                    data-timestamp='<?= esc_attr(strtotime( $dddatadate )) ?>'
                                    data-price='<?= esc_attr($indPrice) ?>'
                            >
                                <div class="w-cnt-result">
                                    <div class="loading-spinner"><i class="fa fa-spin fa-spinner"></i></div>
                                    <div class="result-head">
                                        <?php
                                        if ( empty( $prop->specialPrice ) || ( $cmpSP - $cmpP == 0 ) ) {
                                            echo '<p><strong>' . gpx_currency($prop->WeekPrice,true) . '</strong></p>';
                                        } else {
                                            //check to see if Force Slash is set
                                            if ( ! empty( $setPropDetails[ $prop->propkeyset ]['slash'] ) ) {
                                                //Force Slash is set -- let's display the slash through
                                                echo '<p class="mach white-text"><strong>' . gpx_currency($prop->WeekPrice,true) . '</strong></p>';
                                            } elseif ( ! empty( $setPropDetails[ $prop->propkeyset ]['desc'] ) && ! empty( $setPropDetails[ $prop->propkeyset ]['icon'] ) ) {
                                                echo '<p class="mach"><strong>' . gpx_currency($prop->WeekPrice,true) . '</strong></p>';
                                            }
                                            if ( $prop->specialPrice - $prop->Price != 0 ) {
                                                echo '<p class="now">';
                                                if ( ! empty( $setPropDetails[ $prop->propkeyset ]['desc'] ) && ! empty( $setPropDetails[ $prop->propkeyset ]['icon'] ) ) {
                                                    echo 'Now ';
                                                }
                                                echo '<strong>' . gpx_currency($prop->specialPrice,true) . '</strong></p>';
                                            }
                                        }
                                        ?>
                                        <?php if ( ! empty( $setPropDetails[ $prop->propkeyset ]['desc'] ) && ! empty( $setPropDetails[ $prop->propkeyset ]['icon'] ) ): ?>
                                            <?php $dialogID = bin2hex( random_bytes( 8 ) ); ?>
                                            <a href="#dialog-special-<?php esc_attr_e( $dialogID ) ?>"
                                               class="special-link" aria-label="promo info"><i
                                                        class="fa <?= esc_attr( $setPropDetails[ $prop->propkeyset ]['icon'] ) ?>"></i></a>
                                            <dialog id="dialog-special-<?php esc_attr_e( $dialogID ) ?>"
                                                    class="modal-special">
                                                <div class="w-modal">
                                                    <p><?= nl2p( esc_html( $setPropDetails[ $prop->propkeyset ]['desc'] ) ) ?></p>
                                                </div>
                                            </dialog>
                                        <?php endif; ?>
                                        <?php
                                        $lpid = isset( $lpSPID ) ? $prop->weekId . $lpSPID : '';
                                        //Changed from limiting # of holds to just hiding the Hold button for SoCal weeks between Memorial day and Labor day.
                                        $heldClass = in_array($prop->weekId, $held);
                                        $is_restricted = RegionRepository::instance()->is_restricted($prop->gpxRegionID, $prop->checkIn);
                                        $holdClass = $is_restricted ? 'hold-hide' : '';
                                        ?>
                                        <ul class="status">
                                            <li>
                                                <div class="status-<?= esc_attr(str_replace( " ", "", $prop->WeekType )) ?>"></div>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="cnt">
                                        <p class="d-flex">
                                            <strong><?= esc_html($prop->WeekType) ?></strong>
                                            <?php if ( $prop->prop_count < 6 ) : ?>
                                                <span class="count-<?= esc_attr(str_replace( " ", "", $prop->WeekType )) ?>"> Only <?= esc_html($prop->prop_count) ?> remaining </span>
                                                <?php endif; ?>
                                        </p>
                                        <p>Check-In <?= date( 'm/d/Y', strtotime( $prop->checkIn ) ) ?></p>
                                        <p><?= esc_html($prop->noNights) ?> Nights</p>
                                        <p>Size <?= esc_html($prop->Size) ?></p>
                                    </div>
                                    <div class="list-button">
                                        <a href=""
                                           class="dgt-btn hold-btn <?= esc_attr($holdClass) ?> <?= esc_attr($bookingDisabeledClass) ?>"
                                           data-lpid="<?= esc_attr($lpid) ?>"
                                           data-wid="<?= esc_attr($prop->weekId) ?>"
                                           data-pid="<?= esc_attr($prop->PID) ?>"
                                           data-type="<?= esc_attr(str_replace( " ", "", $prop->WeekType )) ?>"
                                           data-cid="<?= esc_attr($cid) ?>"
                                           title="Hold Week <?= esc_attr($prop->weekId) ?>"
                                        >
                                            Hold<i class="fa fa-refresh fa-spin fa-fw" style="display: none;"></i>
                                        </a>
                                        <a href="/booking-path/?book=<?= rawurlencode($prop->PID) ?>&type=<?= rawurlencode(str_replace( " ", "", $prop->WeekType )) ?>"
                                           data-type="<?= esc_attr(str_replace( " ", "", $prop->WeekType )) ?>"
                                           data-lpid="<?= esc_attr($lpid) ?>"
                                           class="dgt-btn active book-btn <?= esc_attr($holdClass) ?> <?= esc_attr($heldClass) ?> <?= esc_attr($bookingDisabeledClass) ?>"
                                           data-propertiesID="<?= esc_attr($prop->PID) ?>"
                                           data-wid="<?= esc_attr($prop->weekId) ?>"
                                           data-pid="<?= esc_attr($prop->PID) ?>"
                                           data-cid="<?= esc_attr($cid) ?>" title="Book Week <?= esc_attr($prop->weekId) ?>"
                                        >
                                            Book
                                        </a>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                    </ul>
                </li>
                <?php $i ++; ?>
            <?php } ?>
        <?php } ?>
        <?= do_shortcode( '[websitetour id="18531"]' ); ?>
    </ul>
    <div class="dgt-container">
        <div class="w-list-actions">
            <a href="" class="dgt-btn custom-request" data-cid="<?= esc_attr($cid ?? '') ?>">Submit a Custom Request</a>
            <a href="" class="dgt-btn">Start a New Search</a>
        </div>
    </div>
</section>
<?php
function nl2p( $string ) {
    $paragraphs = '';

    $string = str_replace( "\\", "", $string );
    $string = str_replace( "\'", "'", $string );


    foreach ( explode( "\n", $string ) as $line ) {
        if ( trim( $line ) ) {
            $paragraphs .= '<p>' . $line . '</p>';
        }
    }

    return $paragraphs;
}
?>
