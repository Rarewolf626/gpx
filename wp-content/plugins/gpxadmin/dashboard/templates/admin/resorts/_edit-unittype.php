<?php
/**
 * @var $resort \GPX\Admin\Models\Resort
 * @var $unit_types stdClass[]
 * @var $unittype ?stdClass
 */
?>

<div class="row">
    <div class="col-xs-12 col-sm-7">
        <form id="unitTypeadd" data-parsley-validate
              class="form-horizontal form-label-left usage_exclude">
            <input type="hidden" name="resort_id" id="resort_id" value="<?= esc_attr($resort->id) ?>"/>
            <input type="hidden" name="unit_id" id="unit_id" value="<?= esc_attr($unittype->record_id ?? null) ?>"/>
            <div id="usage-add" class="usage_exclude" data-type="usage">
                <div class="form-group">
                    <h4><?= $unittype ? 'Edit' : 'Add' ?> Unit Type</h4>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Name<span
                            class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" id="name" name="name" required="required"
                               class="form-control col-md-7 col-xs-12" value="<?= esc_attr($unittype->name ?? null) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Number
                        of Bedrooms<span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <select name="number_of_bedrooms" id="number_of_bedrooms" class="form-control">
                            <option value="STD" <?= 'STD' == ($unittype->name ?? null) ? 'selected' : '' ?>>STD</option>
                            <option value="1" <?= '1' == ($unittype->name ?? null) ? 'selected' : '' ?>>1</option>
                            <option value="2" <?= '2' == ($unittype->name ?? null) ? 'selected' : '' ?>>2</option>
                            <option value="3" <?= '3' == ($unittype->name ?? null) ? 'selected' : '' ?>>3</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">
                        Sleeps Total<span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <select id="sleeps_total" name="sleeps_total" class="form-control">
                            <?php for ($i = 2; $i <= 12; $i++) : ?>
                                <option
                                    value="<?= esc_attr($i) ?>" <?= $i == ($thisUnit->sleeps_total ?? null) ? 'selected' : '' ?> >
                                    <?= esc_html($i) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <button id="unitTypeaddsubmit" type="submit" class="btn btn-success">
                            Submit <i class="fa fa-circle-o-notch fa-spin fa-fw"
                                      style="display: none;"></i></button>
                        <a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=resorts_edit&id=<?= esc_attr($resort->id) ?>"
                           class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="col-xs-5">
        <h3>Unit Types</h3>
        <ul>
            <?php foreach ($unit_types as $utK => $unit_type): ?>
                <li style="margin-bottom: 15px;">
                    <a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=resorts_edit&id=<?= esc_attr($resort->id) ?>&unitID=<?= esc_attr($utK) ?>">
                        <?= $unit_type->name ?>
                        <i class="fa fa-pencil"></i>
                    </a>
                    <form class="form-unittype-delete" method="post" action="/wp-admin/admin-ajax.php?&action=deleteUnittype" style="display:inline;">
                        <input type="hidden" name="resort_id" value="<?= esc_attr($resort->id) ?>"/>
                        <input type="hidden" name="unit_id" value="<?= esc_attr($utK) ?>"/>
                        <button type="submit" class="btn btn-plain" style="color: #f00;"><i class="fa fa-remove"></i>
                        </button>
                    </form>

                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
