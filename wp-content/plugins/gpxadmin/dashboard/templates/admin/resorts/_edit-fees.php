<?php
/**
 * @var \GPX\Model\Resort $resort
 */
?>

<div id="gpxadmin-resort-fee-display" data-props="<?= esc_attr(json_encode([
    'resort' => [
        'id' => $resort->id,
        'name' => $resort->ResortName,
        'region' => $resort->show_resort_fees,
        'enabled' => $resort->ResortFeeSettings['enabled'] ?? false,
        'fee' => $resort->ResortFeeSettings['fee'] ?? 0,
        'frequency' => $resort->ResortFeeSettings['frequency'] ?? 'weekly',
    ],
]))?>"></div>

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
