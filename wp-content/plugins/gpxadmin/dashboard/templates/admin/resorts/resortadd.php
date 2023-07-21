<?php
/**
 * @var ?string $message
 * @var Resort $resort
 * @var MessageBag $errors
 */

use GPX\Model\Resort;
use Illuminate\Support\MessageBag;
?>
<?php gpx_admin_view('header.php', ['active' => 'resorts']); ?>
<div class="right_col" role="main">
    <div class="">

        <div class="page-title">
            <div class="title_left">
                <h3>Add Resort</h3>
            </div>
        </div>

        <div class="clearfix"></div>
        <div class="row">
            <div class="col-md-12">
                <div class="x_content">
                    <br/>
                    <?php if ($message): ?>
                        <div class="alert <?= $errors->isNotEmpty() ? 'alert-danger' : 'alert-success'?>"><?= esc_html($message) ?></div>
                    <?php endif; ?>
                    <form id="resort-add" class="form-horizontal form-label-left usage_exclude"
                          method="POST" action="<?= gpx_admin_route('resorts_add') ?>" novalidate>
                        <div id="usage-add" class="usage_exclude" data-type="usage">
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ResortID">
                                    Resort ID
                                    <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="ResortID" name="ResortID"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('ResortID') ? 'parsley-error' : '' ?>"
                                           maxlength="255" required
                                           value="<?= esc_attr($resort->ResortID) ?>"
                                    />
                                    <?php if ($errors->has('ResortID')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('ResortID')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ResortName">
                                    Resort Name
                                    <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="ResortName" name="ResortName"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('ResortName') ? 'parsley-error' : '' ?>"
                                           maxlength="255" required
                                           value="<?= esc_attr($resort->ResortName) ?>"
                                    />
                                    <?php if ($errors->has('ResortName')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('ResortName')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Website">
                                    Website
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="url" id="Website" name="Website"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('Website') ? 'parsley-error' : '' ?>"
                                           maxlength="255"
                                           value="<?= esc_attr($resort->Website) ?>"
                                    />
                                    <?php if ($errors->has('Website')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('Website')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Address1">
                                    Address 1
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="Address1" name="Address1"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('Address1') ? 'parsley-error' : '' ?>"
                                           value="<?= esc_attr($resort->Address1) ?>"
                                    />
                                    <?php if ($errors->has('Address1')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('Address1')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Address2">
                                    Address 2
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="Address2" name="Address2"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('Address2') ? 'parsley-error' : '' ?>"
                                           value="<?= esc_attr($resort->Address2) ?>"
                                    />
                                    <?php if ($errors->has('Address2')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('Address2')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Town">
                                    City
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="Town" name="Town"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('Town') ? 'parsley-error' : '' ?>"
                                           maxlength="255"
                                           value="<?= esc_attr($resort->Town) ?>"
                                    />
                                    <?php if ($errors->has('Town')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('Town')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Region">
                                    State
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="Region" name="Region"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('Region') ? 'parsley-error' : '' ?>"
                                           maxlength="255"
                                           value="<?= esc_attr($resort->Region) ?>"
                                    />
                                    <?php if ($errors->has('Region')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('Region')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="PostCode">
                                    ZIP
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="PostCode" name="PostCode"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('PostCode') ? 'parsley-error' : '' ?>"
                                           maxlength="255"
                                           value="<?= esc_attr($resort->PostCode) ?>"
                                    />
                                    <?php if ($errors->has('PostCode')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('PostCode')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Country">
                                    Country
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="Country" name="Country"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('Country') ? 'parsley-error' : '' ?>"
                                           maxlength="255"
                                           value="<?= esc_attr($resort->Country) ?>"
                                    />
                                    <?php if ($errors->has('Country')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('Country')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Phone">
                                    Phone
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="tel" id="Phone" name="Phone"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('Phone') ? 'parsley-error' : '' ?>"
                                           maxlength="255"
                                           value="<?= esc_attr($resort->Phone) ?>"
                                    />
                                    <?php if ($errors->has('Phone')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('Phone')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Fax">
                                    Fax
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="tel" id="Fax" name="Fax"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('Fax') ? 'parsley-error' : '' ?>"
                                           maxlength="255"
                                           value="<?= esc_attr($resort->Fax) ?>"
                                    />
                                    <?php if ($errors->has('Fax')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('Fax')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Email">
                                    Email
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="email" id="Email" name="Email"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('Email') ? 'parsley-error' : '' ?>"
                                           maxlength="255"
                                           value="<?= esc_attr($resort->Email) ?>"
                                    />
                                    <?php if ($errors->has('Email')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('Email')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="CheckInDays">
                                    Check In Days
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="CheckInDays" name="CheckInDays"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('CheckInDays') ? 'parsley-error' : '' ?>"
                                           maxlength="255"
                                           value="<?= esc_attr($resort->CheckInDays) ?>"
                                    />
                                    <?php if ($errors->has('CheckInDays')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('CheckInDays')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="CheckInEarliest">
                                    Check In Time
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="CheckInEarliest" name="CheckInEarliest"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('CheckInEarliest') ? 'parsley-error' : '' ?>"
                                           maxlength="255"
                                           value="<?= esc_attr($resort->CheckInEarliest) ?>"
                                    />
                                    <?php if ($errors->has('CheckInEarliest')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('CheckInEarliest')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="CheckOutLatest">
                                    Check Out
                                    Time
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="CheckOutLatest" name="CheckOutLatest"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('CheckOutLatest') ? 'parsley-error' : '' ?>"
                                           maxlength="255"
                                           value="<?= esc_attr($resort->CheckOutLatest) ?>"
                                    />
                                    <?php if ($errors->has('CheckOutLatest')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('CheckOutLatest')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Airport">
                                    Airport
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="Airport" name="Airport"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('Airport') ? 'parsley-error' : '' ?>"
                                           value="<?= esc_attr($resort->Airport) ?>"
                                    />
                                    <?php if ($errors->has('Airport')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('Airport')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Directions">
                                    Directions
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="Directions" name="Directions"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('Directions') ? 'parsley-error' : '' ?>"
                                           value="<?= esc_attr($resort->Directions) ?>"
                                    />
                                    <?php if ($errors->has('Directions')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('Directions')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Description">
                                    Description
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="Description" name="Description"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('Description') ? 'parsley-error' : '' ?>"
                                           value="<?= esc_attr($resort->Description) ?>"
                                    />
                                    <?php if ($errors->has('Description')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('Description')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="AdditionalInfo">
                                    Additional Info
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" id="AdditionalInfo" name="AdditionalInfo"
                                           class="form-control col-md-7 col-xs-12 <?= $errors->has('AdditionalInfo') ? 'parsley-error' : '' ?>"
                                           value="<?= esc_attr($resort->AdditionalInfo) ?>"
                                    />
                                    <?php if ($errors->has('AdditionalInfo')): ?>
                                        <div class="form-error"><?= esc_html($errors->first('AdditionalInfo')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button id="submit-button" type="submit" class="btn btn-success">
                                        Submit
                                        <i id="loading-spinner" class="fa fa-circle-o-notch fa-spin fa-fw"
                                           style="display: none;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('resort-add').addEventListener('submit', function () {
        document.getElementById('loading-spinner').style.display = 'inline-block';
        document.getElementById('submit-button').setAttribute('disabled', 'disabled');
    });
</script>

<?php gpx_admin_view('footer.php'); ?>
