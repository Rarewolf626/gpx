<?php
/**
 * @var array     $args
 * @var ?stdClass $resort
 * @var ?array    $ammenitiesList
 */

$ammenitiesList = $ammenitiesList ?? $args['ammenitiesList'] ?? [
    'UnitFacilities'   => 'Unit Facilities',
    'ResortFacilities' => 'Resort Facilities',
    'AreaFacilities'   => 'Area Facilities',
    'resortConditions' => 'Resort Conditions',
    'configuration'    => 'Conditions',
];
$resort         = $resort ?? $args['resort'] ?? null;
if ( ! $resort ) {
    return;
}

$amenities = array_filter( $ammenitiesList, fn( $alv, $alk ) => isset( $resort->$alk ), ARRAY_FILTER_USE_BOTH );

if ( empty( $amenities ) ) {
    return;
}
?>

<div class="title">
    <div class="close">
        <i class="icon-close"></i>
    </div>
    <h4>Amenities</h4>
</div>
<div class="cnt-list flex-list">
    <?php foreach ( $amenities as $alk => $alv ): ?>

        <ul class="list-cnt">
            <li>
                <p><strong><?= $alv ?></strong>
            </li>
            <?php $amms = is_array( $resort->$alk ) ? $resort->$alk : json_decode( $resort->$alk ); ?>
            <?php foreach ( $amms as $amm ): ?>
                <li><p><?= $amm ?></p></li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
</div>
