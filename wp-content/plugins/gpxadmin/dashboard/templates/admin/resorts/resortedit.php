<?php

use GPX\Model\Resort;
use Illuminate\Support\Arr;

extract($static);
extract($data);
include $dir . '/templates/admin/header.php';
$GuestFeeAmount = '';
if (isset($resort->GuestFeeAmount)) $GuestFeeAmount = $resort->GuestFeeAmount;
$resortDates = (array)$resort->dates;
$defaultAttrs = (array)$resort->defaultAttrs;
$defaultModals = [];
$rmDefaults = (array)$resort->rmdefaults;
$unit_types = (array)$resort->unit_types;

?>
<div id="gpx-ajax-loading">
    <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
</div>
<div class="modal fade" id="addTax">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <div class="modal-title">Add New Tax</div>
            </div>
            <div class="modal-body">
                <form id="resorttax-add" data-parsley-validate class="form-horizontal form-label-left">
                    <input type="hidden" name="resortID" value="<?= $resort->ResortID ?>">
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="TaxAuthority">Tax Authority <span
                                class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-11">
                            <input type="text" name="TaxAuthority" id="TaxAuthority" class="form-control form-element"
                                   value="<?= $tax->TaxAuthority ?? ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="City">City <span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-11">
                            <input type="text" name="City" id="City" class="form-control form-element"
                                   value="<?= $tax->City ?? ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="State">State <span
                                class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-11">
                            <input type="text" name="State" id="State" class="form-control form-element"
                                   value="<?= $tax->State ?? ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Country">Country <span
                                class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-11">
                            <input type="text" name="Country" id="Country" class="form-control form-element"
                                   value="<?= $tax->Country ?? ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="TaxPercent1">Tax Percent 1 <span
                                class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-11">
                            <input type="text" name="TaxPercent1" id="TaxPercent1" class="form-control form-element"
                                   value="<?= $tax->TaxPercent1 ?? ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="TaxPercent2">Tax Percent 2</label>
                        <div class="col-md-6 col-sm-6 col-xs-11">
                            <input type="text" name="TaxPercent2" id="TaxPercent2" class="form-control form-element"
                                   value="<?= $tax->TaxPercent2 ?? ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="TaxPercent3">Tax Percent 3</label>
                        <div class="col-md-6 col-sm-6 col-xs-11">
                            <input type="text" name="TaxPercent3" id="TaxPercent3" class="form-control form-element"
                                   value="<?= $tax->TaxPercent3 ?? ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="FlatTax1">Flat Tax 1 <span
                                class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-11">
                            <input type="text" name="FlatTax1" id="FlatTax1" class="form-control form-element"
                                   value="<?= $tax->FlatTax1 ?? ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="FlatTax2">Flat Tax 2</label>
                        <div class="col-md-6 col-sm-6 col-xs-11">
                            <input type="text" name="FlatTax2" id="FlatTax21" class="form-control form-element"
                                   value="<?= $tax->FlatTax2 ?? ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="FlatTax3">Flat Tax 3</label>
                        <div class="col-md-6 col-sm-6 col-xs-11">
                            <input type="text" name="FlatTax3" id="FlatTax3" class="form-control form-element"
                                   value="<?= $tax->FlatTax3 ?? ''; ?>">
                        </div>
                    </div>
                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <button type="submit" class="btn btn-success" id="resorttax-submit">Submit <i
                                    class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="right_col" role="main">
    <div class="update-nag"></div>
    <div class="">

        <div class="page-title">
            <div class="title_left">
                <h3>Edit <a href="/resort-profile/?resortName=<?= $resort->ResortName ?>"
                            target="_blank"><?= $resort->ResortName ?></a></h3>
            </div>
            <div class="title_right"></div>
        </div>

        <div class="clearfix"></div>
        <div class="row">
            <div class="col-xs-12">
                <ul class="nav nav-tabs">
                    <?php
                    $activeClass = [
                        'alertnotes' => 'active in',
                        'description' => '',
                        'attributes' => '',
                        'ada' => '',
                        'images' => '',
                        'resort-fees' => '',
                        'unittype' => '',
                        'resort-settings' => '',
                    ];
                    if (isset($resort->newfile)) {
                        $activeClass['alertnotes'] = '';
                        $activeClass['images'] = 'active in';
                    }
                    if (isset($_COOKIE['resort-tab'])) {
                        $activeTab = str_replace("#", "", $_COOKIE['resort-tab']);
                        foreach ($activeClass as $acK => $acV) {
                            if ($acK == $activeTab) {
                                $activeClass[$acK] = 'active in';
                            } else {
                                $activeClass[$acK] = '';
                            }
                        }
                    }
                    ?>
                    <li class="<?= $activeClass['alertnotes'] ?> tab-click"><a href="#alertnotes" role="tab"
                                                                               data-toggle="tab">Alert Notes</a></li>
                    <li class="<?= $activeClass['description'] ?> tab-click"><a href="#description" role="tab"
                                                                                data-toggle="tab">Description</a></li>
                    <li class="<?= $activeClass['ada'] ?> tab-click"><a href="#ada" role="tab" data-toggle="tab">ADA</a>
                    </li>
                    <li class="<?= $activeClass['attributes'] ?> tab-click"><a href="#attributes" role="tab"
                                                                               data-toggle="tab">Attributes</a></li>
                    <li class="<?= $activeClass['images'] ?> tab-click"><a href="#images" role="tab" data-toggle="tab">Gallery</a>
                    </li>
                    <li class="<?= $activeClass['resort-fees'] ?> tab-click"><a href="#resort-fees" role="tab"
                                                                                data-toggle="tab">Resort Fees</a></li>
                    <li class="<?= $activeClass['unittype'] ?> tab-click"><a href="#unittype" role="tab"
                                                                             data-toggle="tab">Unit Type</a></li>
                    <li class="<?= $activeClass['resort-settings'] ?> tab-click"><a href="#resort-settings" role="tab"
                                                                                    data-toggle="tab">Resort
                            Settings</a></li>
                </ul>
                <div class="tab-content resort-tabs">
                    <div class="tab-pane fade tab-padding two-column-grid <?= $activeClass['alertnotes'] ?>"
                         id="alertnotes">
                        <?php
                        $msi = 0;
                        $attrDates = json_decode($resort->AlertNote ?? '[]', true);
                        foreach ($resortDates['alertnotes'] as $repeatableDate => $resortAttribute):
                            $oldorder = $msi;
                            $displayDateFrom = '';
                            $displayDateTo = '';
                            $dates = explode("_", $repeatableDate);
                            if (count($dates) == 1 and $dates[0] == '0') {
                                $displayDateFrom = date('Y-m-d');
                            } else {
                                $oldorder = date('s', $dates[0]);
                                $displayDateFrom = date('Y-m-d', $dates[0]);
                                if (isset($dates[1])) {
                                    $displayDateTo = date('Y-m-d', $dates[1]);
                                }
                            }
                            ?>
                            <form
                                class="repeatable well resort-edit-form"
                                action="<?= admin_url('admin-ajax.php') ?>?action=gpx_admin_resort_alert_save"
                                data-resort="<?= esc_attr($resort->ResortID) ?>"
                                data-dates="<?= esc_attr($repeatableDate) ?>"
                                data-seq="<?= $msi; ?>"
                            >
                                <fieldset class="resort-edit-form-fields">
                                    <input type="hidden" class="resort-edit-resort" name="resort"
                                           value="<?= $resort->ResortID ?>">
                                    <input type="hidden" class="resort-edit-dates" name="oldDates"
                                           value="<?= $repeatableDate ?>">
                                    <input type="hidden" class="resort-edit-field" name="field" value="AlertNote"/>
                                    <div class="alert-actions <?= empty($attrDates) ? 'hidden' : '' ?>">
                                        <i class="fa fa-copy copy-alert" title="Copy Alert Note"></i>
                                        <i class="fa fa-times-circle-o delete-alert" title="Delete Alert Note"
                                           style="margin-left: 10px;"
                                           data-type="descriptions" data-resortid="<?= $resort->ResortID ?>"></i>
                                    </div>
                                    <div id="date-select">
                                        <div class="filterRow">
                                            <div class="filterBox">
                                                <strong>Active Date
                                                    <a href="#"
                                                       title="Different date ranges are required for each alert note."
                                                       onclick="preventDefault();"><i class="fa fa-info-circle"></i></a>
                                                </strong>
                                            </div>
                                        </div>
                                        <div class="filterRow">
                                            <div class="filterBox">
                                                <input type="date"
                                                       class="from-date dateFilterFrom"
                                                       placeholder="from"
                                                       name="from"
                                                       value="<?= $displayDateFrom; ?>"
                                                       data-oldfrom="<?= $displayDateFrom; ?>"
                                                       data-oldorder="<?= $oldorder; ?>"/>
                                                <span class="hyphen">-</span>
                                            </div>
                                            <div class="filterBox">
                                                <input type="date"
                                                       class="to-date dateFilterTo"
                                                       placeholder="to"
                                                       name="to"
                                                       value="<?= $displayDateTo; ?>"
                                                       data-oldto="<?= $displayDateTo; ?>"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="resort-edit">

                                        <div class="two-column-grid">
                                            <?php
                                            $descs = [
                                                'AlertNote' => 'Alert Note',
                                            ];
                                            $btns = [
                                                'booking' => 'Booking Path',
                                                'profile' => 'Resort Profile',
                                            ];
                                            $i = 0;
                                            foreach ($descs as $descKey => $descVal) {
                                                $defaultModals[$descKey] = [
                                                    'type' => $descVal,
                                                    'desc' => stripslashes($rmDefaults[$descKey] ?? '')
                                                ];
                                                $attrDates = json_decode($resort->$descKey, true);
                                                $thisAttr = '';
                                                $thisBtn['booking'] = '0';
                                                $thisBtn['profile'] = '0';
                                                if (!empty($attrDates)) {
                                                    $thisAttrs = isset($attrDates[$repeatableDate]) ? end($attrDates[$repeatableDate]) : null;
                                                    $thisAttr = stripslashes($thisAttrs['desc'] ?? '');
                                                    $thisAttrBk = '0';
                                                    $thisAttrP = '0';
                                                    if (isset($thisAttrs['path']['booking']) && $thisAttrs['path']['booking'] != 0) {
                                                        $thisAttrBk = 1;
                                                    }
                                                    if (isset($thisAttrs['path']['booking']) && $thisAttrs['path']['profile'] != 0) {
                                                        $thisAttrP = 1;
                                                    }
                                                    $thisBtn['booking'] = $thisAttrBk;
                                                    $thisBtn['profile'] = $thisAttrP;
                                                }
                                                ?>
                                                <div class=" edit-resort-group well">

                                                    <div class="row">
                                                        <div class="col-xs-12 col-sm-4">
                                                            <label for="<?= $descKey ?>"><?= $descVal ?>
                                                                <a href="#" data-toggle="modal" data-target="#myModal"
                                                                   title="Default <?= $descVal ?>">
                                                                    <i class="fa fa-info-circle"></i>
                                                                </a>
                                                            </label>
                                                        </div>
                                                        <?php if (!empty($attrDates)): ?>
                                                            <div class="col-xs-12 col-sm-8 text-right">
                                                                <div class="resort-edit-buttons">
                                                                    <?php foreach ($btns as $btnKey => $btnVal): ?>
                                                                        <label class="resort-edit-checkbox">
                                                                            <?= $btnVal ?>
                                                                            <input
                                                                                class="path-checkbox path-checkbox-<?= $btnKey ?> resort-edit-field"
                                                                                name="<?= $btnKey ?>" type="checkbox"
                                                                                value="1" <?= $thisBtn[$btnKey] ? 'checked' : '' ?> />
                                                                        </label>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="row form-group">
                                                        <div class="col-xs-10">
                                                            <textarea name="value"
                                                                      class="form-control form-element resort-description-edit resort-edit-field"
                                                                      rows="4" data-type="<?= $descKey ?>"
                                                                      data-resort="<?= $resort->ResortID ?>"
                                                                      disabled><?= $thisAttr; ?></textarea>
                                                        </div>
                                                        <div class="col-xs-1" style="cursor: pointer"><i
                                                                class="fa fa-lock col-xs-1 resort-lock"
                                                                aria-hidden="true" style="font-size: 20px"></i></div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>

                                        <div style="margin-top:10px;">
                                            <button class="btn btn-primary" type="submit">Submit</button>
                                        </div>
                                    </div>

                                </fieldset>
                            </form>
                            <?php
                            $msi++;
                        endforeach;
                        ?>
                    </div>
                    <div class="tab-pane fade tab-padding two-column-grid <?= esc_attr($activeClass['description']) ?>"
                         id="description">
                        <div class="repeatable well">
                            <div class="resort-edit">
                                <div class="two-column-grid">
                                    <?php foreach (Resort::descriptionFields()->where('enabled') as $field): ?>
                                        <?php gpx_admin_view('resorts/_edit-description', compact('resort', 'field')); ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade tab-padding <?= $activeClass['ada'] ?>" id="ada">
                        <?php
                        foreach ($resortDates['ada'] as $repeatableDate => $resortAttribute) {
                            $displayDateFrom = '';
                            $displayDateTo = '';
                            $dates = explode("_", $repeatableDate);
                            if (count($dates) == 1 && $dates[0] == '0') {
                                $displayDateFrom = date('Y-m-d');
                            } else {
                                $displayDateFrom = date('Y-m-d', $dates[0]);
                                if (isset($dates[1])) {
                                    $displayDateTo = date('Y-m-d', $dates[1]);
                                }
                            }
                            ?>
                            <div class="repeatable well">
                                <div class="clone-group" style="display: none;">
                                    <i class="fa fa-copy"></i>
                                    <i class="fa fa-times-circle-o" style="margin-left: 10px;" data-type="ada"
                                       data-resortid="<?= $resort->ResortID ?>"></i>
                                </div>
                                <div id="date-select" style="display: none;">
                                    <div class="filterRow">
                                        <div class="filterBox">
                                            <strong>Active Date</strong>
                                        </div>
                                    </div>
                                    <div class="filterRow">
                                        <div class="filterBox">
                                            <input type="date" id="" class=" from-date dateFilterFrom"
                                                   placeholder="from" value="<?= $displayDateFrom; ?>"
                                                   data-oldfrom="<?= $displayDateFrom; ?>"/><span
                                                class="hyphen">-</span>
                                        </div>
                                        <div class="filterBox">
                                            <input type="date" id="" class=" to-date dateFilterTo" placeholder="to"
                                                   value="<?= $displayDateTo; ?>" data-oldto="<?= $displayDateTo; ?>"/>
                                        </div>
                                        <div class="filterBox">
                                            <a href="#" class="btn btn-apply date-filter">Apply</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="two-column-grid">

                                    <?php
                                    $adaAtts = [
                                        'CommonArea' => 'Common Area Accessibility Features',
                                        'GuestRoom' => 'Guest Room Accessibility Features',
                                        'GuestBathroom' => 'Guest Bathroom Accessibility Features',
                                        'UponRequest' => 'Upon Request',
                                    ];
                                    $i = 0;
                                    foreach ($adaAtts as $attributeType => $attributeValue) {
                                        $thisAttr = Arr::last(json_decode($resort->$attributeType ?? '[]', true)) ?? [];
                                        if (empty($thisAttr) && !empty($defaultAttrs[$attributeType])) {
                                            $thisAttr = $defaultAttrs[$attributeType] ?? [];
                                        }
                                        $thisAttr = Arr::wrap($thisAttr);
                                        ?>
                                        <div class=" edit-resort-group well">
                                            <form class="resort-edit" data-parsley-validate>
                                                <input type="hidden" name="ResortID" class="resortID"
                                                       value="<?= $resort->ResortID ?>">
                                                <input type="hidden" name="attributeType" class="attributeType"
                                                       value="<?= $attributeType ?>">
                                                <div class="row">
                                                    <div class="col-xs-12 col-sm-6">
                                                        <label
                                                            for="<?= $resortFeeKey ?? '' ?>"><?= $attributeValue ?? '' ?></label>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 text-right">
                                                    </div>
                                                </div>
                                                <ul class="attribute-list">
                                                    <?php

                                                    foreach ($thisAttr as $attributeKey => $attributeItem) {
                                                        ?>
                                                        <li class="attribute-list-item"
                                                            id="<?= $attributeType ?>-<?= $attributeKey ?>"
                                                            data-id="<?= $attributeKey ?>"><?= stripslashes($attributeItem) ?>
                                                            <span class="attribute-list-item-remove"><i
                                                                    class="fa fa-times-circle-o"></i></span></li>
                                                        <?php
                                                    }
                                                    ?>
                                                </ul>
                                                <div class="row form-group attribute-group">
                                                    <input type="text" class="form-control form-element new-attribute"
                                                           name="new-attribute" data-type="<?= $attributeType ?>"
                                                           data-resort="<?= $resort->ResortID ?>" value="">
                                                    <input type="button" class="btn btn-primary insert-attribute"
                                                           value="Add Attribute" name="add-attribute"/>
                                                </div>
                                            </form>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="tab-pane fade tab-padding <?= $activeClass['attributes'] ?>" id="attributes">
                        <?php
                        foreach ($resortDates['attributes'] as $repeatableDate => $resortAttribute) {
                            $displayDateFrom = '';
                            $displayDateTo = '';
                            $dates = explode("_", $repeatableDate);
                            if (count($dates) == 1 && $dates[0] == '0') {
                                $displayDateFrom = date('Y-m-d');
                            } else {
                                $displayDateFrom = date('Y-m-d', $dates[0]);
                                if (isset($dates[1])) {
                                    $displayDateTo = date('Y-m-d', $dates[1]);
                                }
                            }
                            ?>
                            <div class="repeatable well">
                                <div class="clone-group" style="display: none;">
                                    <i class="fa fa-copy"></i>
                                    <i class="fa fa-times-circle-o" style="margin-left: 10px;" data-type="attributes"
                                       data-resortid="<?= $resort->ResortID ?>"></i>
                                </div>
                                <div id="date-select" style="display: none;">
                                    <div class="filterRow">
                                        <div class="filterBox">
                                            <strong>Active Date</strong>
                                        </div>
                                    </div>
                                    <div class="filterRow">
                                        <div class="filterBox">
                                            <input type="date" id="" class=" from-date dateFilterFrom"
                                                   placeholder="from" value="<?= $displayDateFrom; ?>"
                                                   data-oldfrom="<?= $displayDateFrom; ?>"/><span
                                                class="hyphen">-</span>
                                        </div>
                                        <div class="filterBox">
                                            <input type="date" id="" class=" to-date dateFilterTo" placeholder="to"
                                                   value="<?= $displayDateTo; ?>" data-oldto="<?= $displayDateTo; ?>"/>
                                        </div>
                                        <div class="filterBox">
                                            <a href="#" class="btn btn-apply date-filter">Apply</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="two-column-grid">

                                    <?php
                                    $attributes = [
                                        'UnitFacilities' => 'Unit Facilities',
                                        'ResortFacilities' => 'Resort Facilities',
                                        'AreaFacilities' => 'Area Facilities',
                                        'UnitConfig' => 'Unit Config',
                                    ];
                                    $i = 0;
                                    foreach ($attributes as $attributeType => $attributeValue) {
                                        $thisAttr = $resortAttribute[$attributeType] ?? [];
                                        if (empty($resortAttribute[$attributeType]) && !empty($defaultAttrs[$attributeType])) {
                                            $thisAttr = $defaultAttrs[$attributeType] ?? [];
                                        }
                                        if (is_scalar($thisAttr)) $thisAttr = [$thisAttr];
                                        ?>
                                        <div class=" edit-resort-group well">
                                            <form class="resort-edit" data-parsley-validate
                                                  class="form-horizontal form-label-left">
                                                <input type="hidden" name="ResortID" class="resortID"
                                                       value="<?= $resort->ResortID ?>">
                                                <input type="hidden" name="attributeType" class="attributeType"
                                                       value="<?= $attributeType ?>">
                                                <div class="row">
                                                    <div class="col-xs-12 col-sm-6">
                                                        <label
                                                            for="<?= $resortFeeKey ?? '' ?>"><?= $attributeValue ?? '' ?></label>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 text-right">
                                                    </div>
                                                </div>
                                                <ul class="attribute-list">
                                                    <?php

                                                    foreach ($thisAttr as $attributeKey => $attributeItem) {
                                                        ?>
                                                        <li class="attribute-list-item"
                                                            id="<?= $attributeType ?>-<?= $attributeKey ?>"
                                                            data-id="<?= $attributeKey ?>"><?= stripslashes($attributeItem) ?>
                                                            <span class="attribute-list-item-remove"><i
                                                                    class="fa fa-times-circle-o"></i></span></li>
                                                        <?php
                                                    }
                                                    ?>
                                                </ul>
                                                <div class="row form-group attribute-group">
                                                    <input type="text" class="form-control form-element new-attribute"
                                                           name="new-attribute" data-type="<?= $attributeType ?>"
                                                           data-resort="<?= $resort->ResortID ?>" value="">
                                                    <input type="button" class="btn btn-primary insert-attribute"
                                                           value="Add Attribute" name="add-attribute"/>
                                                </div>
                                            </form>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="tab-pane fade tab-padding <?= $activeClass['images'] ?>" id="images">
                        <form class="resort-edit" data-parsley-validate>
                            <input type="hidden" name="ResortID" class="resortID" value="<?= $resort->ResortID ?>">
                            <?php
                            $images = json_decode($resort->images);
                            ?>
                            <ul class="three-column-grid images-list images-sortable">
                                <?php
                                foreach ($images as $imageKey => $imageInfo) {
                                    $image = $imageInfo->src;
                                    $image_video = '';
                                    if (isset($imageInfo->video) && !empty($imageInfo->video)) {
                                        $image_video = $imageInfo->video;
                                    }

                                    ?>
                                    <li class="sortable-image well" id="image-<?= $imageKey ?>"
                                        data-id="<?= $imageKey ?>">
                                        <img src="<?= $image ?>" class="resort-set-image"/>
                                        <?php
                                        if ($imageInfo->type == 'uploaded') {
                                            //we can get the alt and title for this image
                                            $image_alt = get_post_meta($imageInfo->id, '_wp_attachment_image_alt', true);
                                            $image_video = get_post_meta($imageInfo->id, 'gpx_image_video', true);
                                            $image_title = get_the_title($imageInfo->id);
                                            ?>
                                            <br/>
                                            <div class="image-attr-row">
                                                <label>Alt: </label>
                                                <input type="text" name="alt" class="image_alt"
                                                       value="<?= $image_alt ?>" data-id="<?= $imageInfo->id ?>"/>
                                            </div>
                                            <div class="image-attr-row">
                                                <label>Title: </label>
                                                <input type="text" name="title" class="image_title"
                                                       value="<?= $image_title ?>" data-id="<?= $imageInfo->id ?>"/>
                                            </div>
                                            <div class="image-attr-row">
                                                <label>Video: </label>
                                                <input type="text" name="video" class="image_video"
                                                       value="<?= $image_video ?>" data-id="<?= $imageInfo->id ?>"/>
                                            </div>

                                            <?php
                                        }
                                        ?>
                                        <input type="hidden" class="image-input" name="resortImages[]"
                                               value="<?= $image ?>"/>
                                        <i class="fa fa-times-circle"></i>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </form>
                        <h2>Upload An Image</h2>
                        <!-- Form to handle the upload - The enctype value here is very important -->
                        <form method="post" enctype="multipart/form-data">
                            <input type='file' id='upload_image' name='new_image'></input>
                            <input type="text" id="image_alt" name="alt" placeholder="Alt Text"><br/>
                            <input type="text" id="image_title" name="title" placeholder="Title Text"><br/>
                            <input type="text" id="image_video" name="video" placeholder="Video URL"> (optional YouTube
                            video)
                            <?php submit_button('Upload') ?>
                        </form>
                    </div>
                    <div class="tab-pane fade tab-padding <?= $activeClass['resort-fees'] ?>" id="resort-fees">
                        <?php gpx_admin_view('resorts/_edit-fees', compact('resort', 'resortDates')); ?>
                    </div>
                    <div class="tab-pane fade tab-padding  <?= $activeClass['unittype'] ?>" id="unittype">
                        <?php $unittype = isset($_GET['unitID']) ? $unit_types[$_GET['unitID']] ?? null : null; ?>
                        <?php // gpx_admin_view('resorts/_edit-unittype', compact('resort', 'unit_types', 'unittype')); ?>
                        <div id="gpxadmin-resort-unitypes" data-props="<?= esc_attr(json_encode([
                            'resort_id' => (int)$resort->id,
                            'unit_types' => array_map(fn($type) => [
                                'record_id' => (int)$type->record_id,
                                'name' => $type->name,
                                'number_of_bedrooms' => $type->number_of_bedrooms,
                                'bedrooms_override' => $type->bedrooms_override,
                                'sleeps_total' => (int)$type->sleeps_total,
                            ], array_values($unit_types)),
                        ]))?>"></div>
                    </div>
                    <div class="tab-pane fade tab-padding  <?= $activeClass['resort-settings'] ?>" id="resort-settings">
                        <div class="row">
                            <div class="col-xs-12 title_right">
                                <?php
                                $settings = [
                                    'active-resort' => [
                                        'name' => 'Active',
                                        'type' => 'checkbox',
                                        'var' => 'active',
                                    ],
                                    'is-gpr' => [
                                        'name' => 'GPR',
                                        'type' => 'checkbox',
                                        'var' => 'gpr',
                                    ],
                                    'featured-resort' => [
                                        'name' => 'Featured',
                                        'type' => 'checkbox',
                                        'var' => 'featured',
                                    ],
                                    'ai-resort' => [
                                        'name' => 'All Inclusive',
                                        'type' => 'checkbox',
                                        'var' => 'ai',
                                    ],
                                    'guest-fees' => [
                                        'name' => 'Guest Fees Enabled',
                                        'type' => 'checkbox',
                                        'var' => 'guestFeesEnabled',
                                    ],
                                    'third-party-deposit-fees' => [
                                        'name' => 'Third Party Deposit Fee Enabled',
                                        'type' => 'checkbox',
                                        'var' => 'third_party_deposit_fee_enabled',
                                    ],
                                    'welcome-email' => [
                                        'name' => 'Send Welcome Email',
                                        'type' => 'button',
                                        'var' => 'gprID',
                                    ],
                                    'taxMethod' => [
                                        'name' => 'Tax Method (from price set)',
                                        'type' => 'radio',
                                        'class' => '',
                                        'options' => [
                                            'taxAdd' => 'Add',
                                            'taxDeduct' => 'Deduct',
                                        ],
                                    ],
                                    'taxID' => [
                                        'name' => 'Resort Tax',
                                        'type' => 'select',
                                        'custom' => true,
                                    ],
                                    'taID' => [
                                        'name' => 'Trip Advisor ID ',
                                        'type' => 'buttonContent',
                                        'var' => 'taID',
                                    ],
                                    'featured-resort',
                                    'ai-resort'
                                ];

                                foreach ($settings as $sKey => $sVal) {
                                    $btnStatus = 'default';
                                    if (!is_array($sVal)) continue;
                                    $var = $sVal['var'] ?? null;

                                    if (isset($resort->$var) && $resort->$var == 1) {
                                        $btnStatus = 'primary';
                                    }
                                    ?>
                                    <div class="row">
                                        <div class="col-xs-12 resort-settings-action">
                                            <?php
                                            if ($sVal['type'] == 'checkbox') {
                                                ?>
                                                <a href="" class="btn btn-<?= $btnStatus ?>" id="<?= $sKey ?>"
                                                   data-active="<?= $resort->$var ?>"
                                                   data-resort="<?= $resort->ResortID ?>"><?= $sVal['name'] ?>
                                                    <i class="active-status fa fa-<?php if ($resort->$var == '1') echo 'check-'; ?>square"
                                                       aria-hidden="true"></i>
                                                </a>
                                                <?php
                                            }

                                            if ($sVal['type'] == 'button') {
                                                $btnStatus = 'primary';

                                                $btnName = $sVal['name'];
                                                if ($sKey == 'welcome-email') {
                                                    //get the number of owners that need a welcome letter
                                                    if ($resort->mlOwners > 0) {
                                                        $btnName .= '<span style="display: none;">s (' . $resort->mlOwners . ')</span>';
                                                    }
                                                }
                                                ?>
                                                <a href="" class="btn btn-<?= $btnStatus ?>" id="<?= $sKey ?>"
                                                   data-resort="<?= $resort->$var ?>"><?= $btnName ?></a><br>
                                                <?php
                                            }

                                            if ($sVal['type'] == 'radio') {
                                                ?>
                                                <div class="row" style="margin-bottom: 5px;">
                                                    <div class="col-xs-12 resort-settings-action">
                                                        <label class="control-label">Tax Method (from price set)</label>
                                                        <div class="btn-group cg-btn-group" data-toggle="buttons">
                                                            <label
                                                                class="btn btn-<?php if ($resort->taxMethod == 1) echo 'primary'; else echo 'default'; ?>">
                                                                <input type="radio" data-toggle="toggle tax-method"
                                                                       data-resort="<?= $resort->ResortID ?>"
                                                                       id="taxAdd" name="taxMethod"
                                                                       value="1" <?php if ($resort->taxMethod == 1) echo 'checked'; ?>>
                                                                Add
                                                            </label>
                                                            <label
                                                                class="btn btn-<?php if ($resort->taxMethod == 2) echo 'primary'; else echo 'default'; ?>">
                                                                <input type="radio" data-toggle="toggle tax-method"
                                                                       data-resort="<?= $resort->ResortID ?>"
                                                                       id="taxDeduct" name="taxMethod"
                                                                       value="2" <?php if ($resort->taxMethod == 2) echo 'checked'; ?>>
                                                                Deduct
                                                            </label>
                                                        </div>
                                                        <div id="welcome-emails"></div>
                                                    </div>
                                                </div>
                                                <?php
                                            }

                                            if ($sVal['type'] == 'select') {
                                                ?>
                                                <div class="row" style="margin-bottom: 5px;">
                                                    <div class="col-xs-12 resort-settings-action">
                                                        <label class="control-label">Resort Tax</label>
                                                        <select name="taxID" id="taxID" class="selectpicker"
                                                                data-resort="<?= $resort->ResortID ?>">
                                                            <optgroup label="Existing">
                                                                <option></option>
                                                                <?php
                                                                foreach ($resort->taxes as $tax) {
                                                                    ?>
                                                                    <option
                                                                        value="<?= $tax->ID ?>" <?php if ($tax->ID == $resort->taxID) echo 'selected'; ?>><?= $tax->TaxAuthority ?> <?= $tax->City ?> <?= $tax->State ?> <?= $tax->Country ?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </optgroup>
                                                            <optgroup label="New" class="newTax">
                                                                <option value="new">New</option>
                                                            </optgroup>
                                                        </select>
                                                    </div>
                                                </div>
                                                <?php
                                            }

                                            if ($sVal['type'] == 'buttonContent') {
                                                ?>
                                                <div class="row">
                                                    <div class="col-xs-12 resort-settings-action">
                                                        <button type="button" id="btn-ta" class="btn btn-primary"
                                                                data-toggle="modal"
                                                                data-target="#modal-ta"
                                                        >
                                                            <?= esc_html($sVal['name']) ?> <span
                                                                class="taID"><?= esc_html($resort->$var) ?></span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="modal-ta" tabindex="-1" role="dialog" x-data="resortEditTripAdvisor(<?= esc_attr($resort->id) ?>, <?= $resort->taID ? esc_attr($resort->taID) : 'null' ?>)">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Trip Advisor Update</h4>
            </div>
            <div class="modal-body">
                <form x-ref="form" method="post" action="<?= admin_url('admin-ajax.php') ?>"
                      @submit.prevent="load">
                    <div class="row">
                        <div class="col-xs-12 col-sm-6">
                            <?= esc_html($resort->ResortName) ?><br>
                            <?= esc_html($resort->Address1) ?><br>
                            <?= esc_html($resort->Town) ?>, <?= esc_html($resort->Region) ?>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="row form-group">
                                <div class="col-xs-12 text-right">
                                    Current ID: <span class="taID" x-text="location_id"><?= esc_html($resort->taID) ?></span>
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-xs-12 text-right">
                                    <label for="coords">Coordinates</label>
                                    <input type="text" name="coords" id="coords" value="<?= esc_attr($resort->LatitudeLongitude) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-xs-12 text-center">
                            <input type="hidden" name="resort_id" value="<?= esc_attr($resort->id) ?>"/>
                            <button type="submit" class="btn btn-primary" :disabled="busy">
                                Refresh
                            </button>
                        </div>
                    </div>
                    <div x-show="busy && !loaded" class="text-center">
                        <i class="fa fa-spinner fa-spin fa-4x"></i>
                    </div>
                    <div x-show="message">
                        <div class="alert" :class="{'alert-success': success, 'alert-danger': !success}" x-text="message"></div>
                    </div>
                    <div x-show="loaded && locations.length > 0" class="row form-group">
                        <div class="col-xs-12 text-left" id="refresh-return">
                            <template x-for="location in locations" :key="location.location_id">
                                <div class="well">
                                    <div class="row form-group">
                                        <div class="col-xs-9">
                                            <strong class="font-weight-bold" x-text="location.name"></strong>
                                            <div x-text="location.address"></div>
                                            <div><strong>Location ID: </strong><span x-text="location.location_id"></span></div>
                                        </div>
                                        <div class="col-xs-3">
                                            <button
                                                class="btn btn-success newTA"
                                                :disabled="busy"
                                                @click.prevent="save(location.location_id)"
                                            >
                                                Select Location
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    function resortEditTripAdvisor(resort_id, location_id) {
        return {
            init() {
                jQuery('#modal-ta')
                    .on('show.bs.modal', this.reset.bind(this))
                    .on('hidden.bs.modal', this.reset.bind(this));
            },
            locations: [],
            busy: false,
            loaded: false,
            message: '',
            success: false,
            location_id: location_id,
            reset() {
                this.locations = [];
                this.loaded = false;
                this.busy = false;
                this.message = '';
                this.success = false;
            },
            load(e) {
                if (this.busy) return;
                this.busy = true;
                this.loaded = false;
                this.message = '';
                this.success = false;
                let form = new FormData(e.target);
                form.append('action', 'gpx_get_tripadvisor_locations')
                axios.post(e.target.action, form)
                    .then(response => {
                        if (response.data.success) {
                            this.locations = response.data.locations;
                            this.loaded = true;
                        } else {
                            this.locations = [];
                            this.success = false;
                            this.message = response.data.message;
                        }
                    })
                    .catch(error => {
                        this.success = false;
                        if (error.response && error.response.data && error.response.data.message) {
                            this.message = error.response.data.message;
                        } else {
                            this.message = 'Could not load Trip Advisor locations.';
                        }
                    })
                    .finally(() => this.busy = false);
            },
            save(location_id) {
                if (this.busy) return;
                this.message = '';
                this.success = false;
                this.busy = true;
                let form = new FormData(this.$refs.form);
                form.append('action', 'gpx_set_tripadvisor_location')
                form.append('location_id', location_id)
                axios.post(this.$refs.form.action, form)
                    .then(response => {
                        if (response.data.success) {
                            this.locations = [];
                            this.loaded = false;
                            this.location_id = response.data.location_id;
                            this.success = true;
                            this.message = response.data.message;
                        } else {
                            this.success = false;
                            this.message = response.data.message || 'Could not save Trip Advisor location.';
                        }
                    })
                    .catch(error => {
                        this.success = false;
                        if (error.response && error.response.data && error.response.data.message) {
                            this.message = error.response.data.message;
                        } else {
                            this.message = 'Could not save Trip Advisor location.';
                        }
                    })
                    .finally(() => this.busy = false);
            }
        };
    }
</script>
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4>Default Alert Note</h4>
            </div>
            <div class="modal-body">
                <p><?= nl2br(stripslashes($resort->HTMLAlertNotes)) ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<?php include $dir . '/templates/admin/footer.php'; ?>
