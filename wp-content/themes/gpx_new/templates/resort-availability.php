<?php
/**
 * @var array[] $resorts
 * @var array[] $props
 * @var array[] $restrictIDs
 * @var int $resortid
 * @var int $cntResults
 * @var int $cid
 */

$total = (int)array_sum(array_map(fn($resort) => count($resort['props']), $resorts));
?>
<ul class="w-list-view dgt-container" id="results-content" data-count="<?= esc_attr($total) ?>">
    <?php foreach($resorts as $resort):?>
        <li class="w-item-view filtered" id="<?= esc_attr('rl' . $resort['id']) ?>" data-subregions="<?= esc_attr(json_encode([$resort['gpxRegionID']]))?>">
            <ul id="gpx-listing-result" class="w-list-result">
                <?php foreach($resort['props'] as $prop):?>
                    <?php
                    $showSlash = false;
                    $finalPrice = $prop->Price;
                    if ($prop->discount > 0) {
                        $finalPrice = $prop->WeekPrice;
                        if($prop->specialPrice - $prop->WeekPrice != 0){
                            $finalPrice = $prop->specialPrice;
                        }
                    }
                    ?>
                    <li
                        id="<?= esc_attr('prop' . $prop->WeekType . $prop->weekId)?>"
                        class="item-result <?= $prop->specialPrice && ($prop->specialPrice - $prop->Price) != 0 ? 'active' : ''?> "
                        data-resorttype="<?= esc_attr(json_encode([$prop->WeekType, $prop->AllInclusive]))?>"
                    >
                        <div class="w-cnt-result">
                            <?php gpx_theme_template_part('results-item-header', compact('resort', 'prop'), true) ?>
                            <div class="cnt">
                                <p>
                                    <strong><?= esc_html($prop->WeekTypeDisplay) ?></strong>
                                    <?php if($prop->prop_count < 6):?>
                                    <br><span class="<?= esc_attr('count-' . $prop->WeekType) ?>"> Only <?= esc_html($prop->prop_count)?> remaining </span>
                                    <?php endif; ?>
                                </p>
                                <p>Check-In <?= esc_html(date('m/d/Y', strtotime($prop->checkIn))) ?></p>
                                <p><?= esc_html($prop->noNights) ?> Nights</p>
                                <p>Size <?= esc_html($prop->Size) ?></p>
                            </div>
                            <div class="list-button">
                                <?php
                                //Changed from limiting # of holds to just hiding the Hold button for SoCal weeks between Memorial day and Labor day.
                                //set an empty hold class
                                $holdClass = '';
                                //is this in the summer?
                                $checkIN = strtotime($prop->checkIn);
                                $thisYear = date('Y', $checkIN);
                                $memorialDay = strtotime(" last monday of may $thisYear");
                                $laborDay = strtotime("first monday of september $thisYear");
                                if (($memorialDay <= $checkIN && $checkIN <= $laborDay)) {
                                    //the date in the range is between memorial day and labor day
                                    //check to see if this gpxRegionID is a restricted one.
                                    if (isset($restrictIDs) && in_array($prop->gpxRegionID, $restrictIDs)) {
                                        //we don't want to show the hold button
                                        $holdClass = 'hold-hide';
                                    }
                                }
                                ?>
                                <a
                                    href="#"
                                    class="dgt-btn hold-btn <?= esc_attr($holdClass)?>"
                                    data-type="<?= esc_attr($prop->WeekType)?>"
                                    data-wid="<?= esc_attr($prop->weekId)?>"
                                    data-pid="<?= esc_attr($prop->PID)?>"
                                    data-cid="<?= esc_attr($cid ?? '')?>"
                                >
                                    Hold
                                    <i class="fa fa-refresh fa-spin fa-fw" style="display: none;"></i>
                                </a>
                                <a
                                    href="/booking-path/?book=<?= esc_attr($prop->PID)?>&type=<?= esc_attr($prop->WeekType)?>"
                                    class="dgt-btn active book-btn <?= esc_attr($holdClass)?>"
                                    data-type="<?= esc_attr($prop->WeekType)?>"
                                    data-wid="<?= esc_attr($prop->weekId)?>"
                                    data-pid="<?= esc_attr($prop->PID)?>"
                                    data-propertiesID="<?= esc_attr($prop->PID)?>"
                                    data-cid="<?= esc_attr($cid ?? '')?>"
                                >
                                    Book
                                </a>
                            </div>
                        </div>
                        <div id="res_count_<?= esc_attr($resortid ?? '')?>" data-res-count="<?= esc_attr($total ?? '')?>"></div>
                    </li>
                <?php endforeach;?>
            </ul>
    <?php endforeach; ?>

<?php if (isset($cntResults) && $cntResults < 10000):?>
    <div style="text-align: center; margin-bottom: 20px;"><a href="#" class="dgt-btn show-more-btn" data-next="<?= esc_attr($cntResults + 10)?>">Show All</a></div>
<?php endif; ?>

