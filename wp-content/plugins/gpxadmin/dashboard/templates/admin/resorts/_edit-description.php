<?php
/**
 * @var stdClass $resort
 * @var array{name: string, label: string, type: string, booking: bool, profile: bool, enabled: bool, attributes: array<string, string>} $field
 */
?>

<form
    method="post"
    class=" edit-resort-group well resort-edit-form"
    action="<?= admin_url('admin-ajax.php') ?>?action=gpx_admin_resort_description_edit"
    data-resort="<?= esc_attr($resort->ResortID) ?>"
    data-field="<?= esc_attr($field['name']) ?>"
    novalidate
>
    <fieldset class="resort-edit-form-fields">
        <input type="hidden" class="resort-edit-resort" name="resort"
               value="<?= esc_attr($resort->ResortID) ?>"/>
        <input type="hidden" class="resort-edit-field" name="field"
               value="<?= esc_attr($field['name']) ?>"/>
        <div class="row">
            <div class="col-xs-12 col-sm-4">
                <label for="<?= esc_attr($field['name']) ?>"><?= esc_attr($field['label']) ?></label>
            </div>

            <div class="col-xs-12 col-sm-8 text-right">
                <div class="resort-edit-buttons">
                    <div class="resort-edit-checkbox <?= $field['booking'] ? 'resort-edit-checkbox--checked' : '' ?>">
                        Booking Path
                        <i class="fa <?= $field['booking'] ? 'fa-check-square' : 'fa-square' ?>"
                           style="margin-left:10px;"></i>
                    </div>
                    <div class="resort-edit-checkbox <?= $field['profile'] ? 'resort-edit-checkbox--checked' : '' ?>">
                        Resort Profile
                        <i class="fa <?= $field['profile'] ? 'fa-check-square' : 'fa-square' ?>"
                           style="margin-left:10px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-10">
                <?php if ($field['type'] === 'textarea'): ?>
                    <textarea
                        id="<?= esc_attr($field['name']) ?>"
                        name="value"
                        class="form-control form-element resort-edit-field resort-description-edit"
                        <?php foreach($field['attributes'] as $key => $value): ?>
                            <?= esc_attr($key) ?>="<?= esc_attr($value) ?>"
                        <?php endforeach; ?>
                        rows="4"
                        disabled
                    ><?= esc_html($resort->{$field['name']}) ?></textarea>
                <?php else: ?>
                    <input
                        id="<?= esc_attr($field['name']) ?>"
                        name="value"
                        type="<?= esc_attr($field['type'] ?? 'text') ?>"
                        class="form-control form-element resort-edit-field resort-description-edit"
                        <?php foreach($field['attributes'] as $key => $value): ?>
                            <?= esc_attr($key) ?>="<?= esc_attr($value) ?>"
                        <?php endforeach; ?>
                        disabled
                        value="<?= esc_attr($resort->{$field['name']}) ?>"
                    />
                <?php endif; ?>
            </div>
            <div class="col-xs-1" style="cursor: pointer"><i
                    class="fa fa-lock col-xs-1 resort-lock"
                    aria-hidden="true"
                    style="font-size: 20px"></i></div>
        </div>
    </fieldset>
</form>
