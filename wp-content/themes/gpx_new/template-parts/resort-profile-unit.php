<?php
/**
 * @var array $args
 * @var ?stdClass $resort
 */

$resort = $resort ?? $args['resort'] ?? null;
if ( ! $resort ) {
    return;
}
$unitconfigs = is_array( $resort->UnitConfig ) ? $resort->UnitConfig : array_values( json_decode( $resort->UnitConfig,
                                                                                                  true ) );
if ( ! $unitconfigs ) {
    return;
}

?>
<div class="title">
    <div class="close">
        <i class="icon-close"></i>
    </div>
    <h4>Unit Configuration</h4>
</div>
<div class="cnt-list">
    <ul class="list-cnt">
        <li>
            <p><strong>Unit Config</strong>
        </li>
        <?php foreach ( $unitconfigs as $config ): ?>
            <li><p><?= esc_html( $config ) ?></p></li>
        <?php endforeach ?>
    </ul>
</div>

