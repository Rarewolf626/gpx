<?php
/**
 * @var ?stdClass $resort
 * @var ?array $adaList
 */

$adaList = $adaList ?? $args['ammenitiesList'] ?? [
    'CommonArea'=>'Common Area Accessibility Features',
    'GuestRoom'=>'Guest Room Accessibility Features',
    'GuestBathroom'=>'Guest Bathroom Accessibility Features',
    'UponRequest'=>'The following can be added to any Guest Room upon request',
];
$resort         = $resort ?? $args['resort'] ?? null;
if ( ! $resort ) {
    return;
}
$ada = array_filter( $adaList, fn( $alv, $alk ) => isset( $resort->$alk ), ARRAY_FILTER_USE_BOTH );
if ( empty( $ada ) ) {
    return;
}

?>
<div class="title">
    <div class="close">
        <i class="icon-close"></i>
    </div>
    <h4>Accessibility Features</h4>
</div>
<div class="cnt-list flex-list">
    <?php foreach($ada as $alk=>$alv): ?>
        <ul class="list-cnt">
            <li>
                <p><strong><?=esc_html($alv)?></strong>
            </li>
            <?php $amms = is_array( $resort->$alk ) ? $resort->$alk : json_decode( $resort->$alk ); ?>
            <?php foreach ( $amms as $amm ): ?>
                <li><p><?= $amm ?></p></li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
</div>
