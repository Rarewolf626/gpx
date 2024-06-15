<?php
/**
 * @var \GPX\Model\Resort $resort
 */
?>
<div class="well">
    <div>
        <?php if ($resort->show_resort_fees): ?>
            <div class="alert alert-success">Resort Fees are currently shown for resorts in this region.</div>
        <?php else: ?>
            <div class="alert alert-danger">
                Resort Fees are currently not shown for resorts in this region.<br>
                The settings below will not have an effect unless they are enabled for the region.
            </div>
        <?php endif; ?>
    </div>
    <form id="resort-resortfees-form" method="post"
          action="<?= admin_url('admin-ajax.php') ?>?action=gpxadmin_resort_edit_resortfees">
        <input type="hidden" name="resort" value="<?= esc_attr($resort->id) ?>"/>
        <?php $resort_fees = isset($resort->ResortFeeSettings) ? json_decode($resort->ResortFeeSettings, true) : ['enabled' => false, 'fee' => 0, 'frequency' => 'weekly']; ?>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="hidden" name="enabled" value="0"/>
                    <input type="checkbox" name="enabled" value="1" <?= $resort_fees['enabled'] ? 'checked' : '' ?> />
                    Calculate
                </label>
            </div>
        </div>
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon">$</div>
                <input id="resort-resortfees-fee" name="fee" class="form-control" type="number" step=".01"
                       value="<?= esc_attr($resort_fees['fee']) ?>"
                       min="0.00">
            </div>
        </div>
        <div class="form-group">
            <select class="form-control" name="frequency">
                <option value="daily" <?= $resort_fees['frequency'] == 'weekly' ? '' : 'selected' ?>>Daily</option>
                <option value="weekly" <?= $resort_fees['frequency'] == 'weekly' ? 'selected' : '' ?>>Weekly</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>

    </form>
</div>

<?php foreach ($resort->fees as $dates): ?>
    <form method="post" action="<?= admin_url('admin-ajax.php') ?>?action=gpxadmin_resort_edit_fees"
          class="resort-edit-fees-form repeatable well">
        <input type="hidden" name="resort" value="<?= esc_attr($resort->id) ?>"/>
        <input type="hidden" name="key" value="<?= esc_attr($dates['dates']['key']) ?>"/>
        <div class="fee-actions">
            <button type="button"
                    class="btn btn-plain resort-fees-copy"
                    data-resort="<?= esc_attr($resort->id) ?>"
                    data-key="<?= esc_attr($dates['dates']['key']) ?>"
            >
                <i class="fa fa-copy"></i>
            </button>
            <button type="button"
                    class="btn btn-plain resort-fees-delete"
                    style="margin-left: 10px;"
                    data-resort="<?= esc_attr($resort->id) ?>"
                    data-key="<?= esc_attr($dates['dates']['key']) ?>"
            >
                <i class="fa fa-times-circle-o"
                   data-resortid="<?= esc_attr($resort->ResortID) ?>"></i>
            </button>
        </div>
        <div>
            <div class="filterRow">
                <div class="filterBox">
                    <strong>Check In Date</strong>
                </div>
            </div>
            <div class="filterRow">
                <div class="filterBox">
                    <input type="date" name="start" value="<?= esc_attr($dates['dates']['start']) ?>" />
                    <span class="hyphen">-</span>
                </div>
                <div class="filterBox">
                    <input type="date" name="end" value="<?= esc_attr($dates['dates']['end']) ?>"/>
                </div>
                <div class="filterBox">
                    <button type="submit" class="btn btn-apply">Apply</button>
                </div>
            </div>
        </div>
        <div class="two-column-grid">
            <div class="edit-resort-group well">
                <fieldset class="resort-edit">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="resortFee">Resort Fees</label>
                        </div>
                        <div class="col-xs-12 col-sm-8 text-right"></div>
                    </div>
                    <ul class="resort-fees">
                        <?php foreach ($dates['resortFees'] as $resortFeeKey => $resortFeeItem): ?>
                            <li class="resort-fees-item" id="resortFees-<?= $resortFeeKey ?>">
                                <?= esc_html($resortFeeItem) ?>
                                <span class="resort-fees-item-remove"><i
                                        class="fa fa-times-circle-o"></i></span>
                                <input type="hidden" name="resortFees[]" value="<?= esc_attr($resortFeeItem) ?>">
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="row form-group attribute-group">
                        <input id="resortFee" type="number" step=".01" min="0" class="form-control form-element"
                               name="resortFee" value=""/>
                        <button type="submit" class="btn btn-primary">Add Fee</button>
                    </div>
                </fieldset>
            </div>

            <div class="edit-resort-group well">
                <fieldset class="resort-edit fees-group row">
                    <?php
                    $resortFees = [
                        'ExchangeFeeAmount' => 'Exchange Fee',
                        'RentalFeeAmount' => 'Rental Fee',
                        'CPOFeeAmount' => 'CPO Fee',
                        'GuestFeeAmount' => 'Guest Fee',
                        'UpgradeFeeAmount' => 'Upgrade Fee',
                        'SameResortExchangeFee' => 'Same Resort Exchange Fee',
                    ];
                    ?>
                    <?php foreach ($resortFees as $resortFeeKey => $resortFeeVal): ?>
                        <div class="col-xs-12 col-sm-4">
                            <label for="<?= esc_attr($resortFeeKey) ?>"><?= esc_html($resortFeeVal) ?></label>
                        </div>
                        <div class="col-xs-12 col-sm-8 attribute-group">
                            <div class="form-group">
                                <div class="col-xs-10">
                                    <input id="<?= esc_attr($resortFeeKey) ?>"
                                           type="number" step=".01" min="0"
                                           class="form-control form-element resort-fee-edit"
                                           name="<?= esc_attr($resortFeeKey) ?>"
                                           value="<?= esc_attr($dates[$resortFeeKey] ?? ''); ?>"
                                           disabled
                                    />
                                </div>
                                <div class="col-xs-1" style="cursor: pointer">
                                    <i
                                        class="fa fa-lock col-xs-1 resort-lock"
                                        aria-hidden="true" style="font-size: 20px"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            </div>
        </div>
    </form>
<?php endforeach; ?>
