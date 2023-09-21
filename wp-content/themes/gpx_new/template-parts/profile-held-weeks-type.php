<?php
/**
 * @var stdClass $hold
 * @var string $type
 */
?>
<div class="profile-week-type" data-hold="<?= esc_attr($hold->holdid) ?>" data-value="<?= esc_attr($hold->weekType)?>">
    <div class="profile-week-type-display">
        <span class="profile-week-type-value"><?= esc_html($type) ?></span>
        <?php if ($hold->AllowedWeekType == 3): ?>
            <button type="button" class="hold-edit-type hold-edit-type-button" title="Edit"><i class="fa fa-pencil"></i></button>
        <?php endif; ?>
    </div>
    <?php if ($hold->AllowedWeekType == 3): ?>
        <form class="hold-edit-type-form" id="hold-edit-type-<?= esc_attr($hold->holdid) ?>" method="post"
              action="<?= admin_url('admin-ajax.php') ?>?action=gpx_held_week_change_type">
            <input type="hidden" name="HoldID" value="<?= esc_attr($hold->holdid) ?>">
            <input type="hidden" name="WeekId" value="<?= esc_attr($hold->weekId) ?>">
            <select name="WeekType">
                <option value="ExchangeWeek" <?= $hold->weekType === 'ExchangeWeek' ? 'selected' : '' ?>>Exchange Week</option>
                <option value="RentalWeek" <?= $hold->weekType === 'RentalWeek' ? 'selected' : '' ?>>Rental Week</option>
            </select>
            <button type="submit" title="Save" class="hold-edit-type-save hold-edit-type-button"><i class="fa fa-save"></i></button>
            <button type="button" title="Cancel" class="hold-edit-type-cancel hold-edit-type-button"><i class="fa fa-ban"></i></button>
        </form>
    <?php endif; ?>
</div>
