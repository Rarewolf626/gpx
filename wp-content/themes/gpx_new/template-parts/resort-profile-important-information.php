<?php

/** @var ?stdClass $resort */
$resort = $resort ?? $args['resort'] ?? null;
if ( ! $resort ) {
    return;
}
if ( empty( $resort->HTMLAlertNotes ) && empty( $resort->AdditionalInfo ) && empty( $resort->AlertNote ) ) {
    return;
}
?>

<div class="title">
    <div class="close">
        <i class="icon-close"></i>
    </div>
    <h4>Important Information</h4>
</div>
<div class="cnt-list">
    <ul class="list-cnt full-list">

        <?php if ( ! empty( $resort->HTMLAlertNotes ) || ! empty( $resort->AlertNote ) ): ?>
            <li>
                <p><strong>Alert Note</strong></p>
            </li>
            <?php if ( ! empty( $resort->AlertNote ) ): ?>
                <?php if ( is_array( $resort->AlertNote ) ): ?>
                    <?php foreach ( $resort->AlertNote as $ral ): ?>
                        <li class="alert-note-info">
                            <p>
                                <strong>
                                    Beginning <?= date( 'm/d/y', $ral['date'][0] ) ?>
                                    <?php if(isset($ral['date'][1])) echo ', Ending ' . date( 'm/d/y', $ral['date'][1] ) ?>
                                    :
                                </strong>
                                <br/>
                                <?= nl2br( stripslashes( $ral['desc'] ) ) ?>
                            </p>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="alert-note-info">
                        <p><?= $resort->AlertNote ?></p>
                    </li>
                <?php endif; ?>
            <?php elseif ( ! empty( $resort->HTMLAlertNotes ) ): ?>
                <li class="alert-note-info">
                    <p><?= $resort->HTMLAlertNotes ?></p>
                </li>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ( ! empty( $resort->AdditionalInfo ) ): ?>
            <li>
                <p><strong>Additional Info</strong></p>
            </li>
            <li>
                <p><?= $resort->AdditionalInfo ?></p>
            </li>
        <?php endif; ?>

    </ul>
</div>

