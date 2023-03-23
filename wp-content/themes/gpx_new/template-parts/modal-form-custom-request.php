<?php
use GPX\Model\CustomRequestMatch;
?>
<div class="dgt-container g-w-modal">
    <div class="dialog__overlay">
        <div id="modal-custom-request" class="dialog dialog--opaque" data-width="800" data-close-on-outside-click="false">
            <div class="w-modal">
                <div class="member-form">
                    <div class="w-form">
                        <h2>Custom Request</h2>
                        <!-- form start -->

                        <form name="custom-request" class="material" id="customRequestForm"  action="https://www.salesforce.com/servlet/servlet.WebToCase?encoding=UTF-8" method="POST" novalidate>
                            <input name="crID" value="" id="crID" type="hidden">
                            <input name="retURL" value="<?=home_url()?>/request-thankyou/" type="hidden">
                            <input name="orgid" value="00D0q0000000RJY" type="hidden">
                            <input name="recordType" value="01240000000MJdI" type="hidden">
                            <input name="origin" value="Web" type="hidden">
                            <input name="reason" value="GPX: Search Request" type="hidden">
                            <input name="status" value="Open" type="hidden">
                            <input name="priority" value="Standard" type="hidden">
                            <input name="subject" value="New GPX Search Request Submission" type="hidden">
                            <input name="description" value="Please validate request and complete search request workflow in EMS" type="hidden">

                            <input type="hidden" name="debugEmail" value="jfeng@gpresorts.com">
                            <input type="hidden" name="00N40000003DG4w" id="00N40000003DG4w" class=crNo value=""> <!-- Account Number -->
                            <input  type="hidden" placeholder="Guest First Names" id="00N40000003DGSO" class="crFirstName" maxlength="40" name="00N40000003DGSO">
                            <input  type="hidden" placeholder="Guest Last Name" id="00N40000003DGST" class="crLastName" maxlength="40" name="00N40000003DGST">
                            <input  type="hidden" placeholder="Home Phone" id="00N40000002yyD8" class="crPhone" maxlength="40" name="00N40000002yyD8">
                            <input  type="hidden" placeholder="Cell Phone" id="00N40000002yyDD" class="crMobile" maxlength="40" name="00N40000002yyDD">
                            <ul class="list-form guest-form-data">
                                <li>
                                    <div class="ginput_container material-input input">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Request by resort.  When this field is filled in region and city will be ignored.</span>
                                        </div>
                                        <input type="text" placeholder="Preferred Resort" name="00N40000003DG59" id="00N40000003DG59" class="crResort cr-for-miles location_autocomplete_resort guest-reset" value="" required>
                                    </div>
                                    <div style="display: none; color: #ff0000;" class="resort-ac-error">Please select from available resorts</div>
                                </li>
                                <li>
                                    <div class="ginput_container material-input input">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Request nearby resort.  Resort availability within <?php esc_html_e(CustomRequestMatch::MILES) ?> miles will be returned when this field is checked.</span>
                                        </div>
                                        <input type="checkbox" name="nearby" id="nearby" class="checkbox-checked nearby-reset guest-reset filled" value="1" checked="checked">
                                        <label for="nearby">I will Accept Nearby Resort Matches?</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="bigorblock">OR</div>
                                </li>
                                <li>
                                    <div class="ginput_container material-input input">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Request by region or filter cities below by adding a region</span>
                                        </div>
                                        <input type="text" placeholder="Region" name="00N40000003S58X" id="00N40000003S58X" class="ui-autocomplete-input location_autocomplete_cr_region cr-for-miles autocomplete-region guest-reset" value="">
                                    </div>
                                    <div style="display: none; color: #ff0000;" class="region-ac-error">Please select from available regions</div>
                                </li>
                                <li>
                                    <div class="ginput_container material-input input">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Request by city/sub region or filter resorts below by adding a value here.  When this field is used the region will be ignored.</span>
                                        </div>
                                        <input type="text" placeholder="City / Sub Region" name="00N40000003DG5S" id="00N40000003DG5S" class="ui-autocomplete-input cr-for-miles location_autocomplete_sub crLocality guest-reset" value="">
                                    </div>
                                    <div style="display: none; color: #ff0000;" class="city-ac-error">Please select from available cities</div>
                                </li>
                                <li class="miles_container" id="milesContainer">
                                    <div class="ginput_container material-input input">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Radius of flexibilty in miles if you are flexible</span>
                                        </div>
                                        <input type="text" placeholder="Flexible Miles?" data-max="350" name="miles" id="miles" class="" value="">
                                    </div>
                                </li>
                            </ul>
                            <ul class="list-form guest-form-data">
                                <li>
                                    <div class="ginput_container material-input input">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Adult tooltip</span>
                                        </div>
                                        <input name="00N40000003DG56" id="00N40000003DG56" placeholder="* Number of Adults" class="sleep-check"  required>
                                    </div>
                                </li>
                                <li>
                                    <div class="ginput_container material-input input">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Child tooltip</span>
                                        </div>
                                        <input name="00N40000003DG57" id="00N40000003DG57" placeholder="* Number of Children">
                                    </div>
                                </li>
                                <li>
                                    <div class="ginput_container material-input input">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Email Tooltip</span>
                                        </div>
                                        <input  type="text" placeholder="* Your Email" id="00N40000003DG50" class="crEmail" maxlength="40" name="00N40000003DG50" required>
                                    </div>
                                </li>
                                <li>
                                    <div class="ginput_container">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Minimum Unit Type</span>
                                        </div>
                                        <div class="group">
                                            <div class="ginput_container">
                                                <select id="00N40000003DG54" name="00N40000003DG54" placeholder="* Room Type" title="Room Type" required>
                                                    <option value="Any">Any</option>
                                                    <option value="Studio">Studio</option>
                                                    <option value="1BR">1BR</option>
                                                    <option value="2BR">2BR</option>
                                                    <option value="3BR">3BR</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="or-larger">
                                    <div class="ginput_container material-input input">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">I would be willing to stay in a room at least the size of the room selected.  I understand an upgrade fee may be required in some cases.</span>
                                        </div>
                                        <input type="checkbox" name="larger" id="or_larger" class="checkbox-checked guest-reset filled" value="1" checked="checked">
                                        <label for="nearby">Or Larger</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="ginput_container">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Travel Week Preference</span>
                                        </div>
                                        <div class="group">
                                            <div class="ginput_container">
                                                <select id="week_preference" name="preference" placeholder="* Travel Week Preference" title="Travel Week Preference" required>
                                                    <option value="Exchange">Exchange</option>
                                                    <option value="Rental">Rental</option>
                                                    <option value="Any">Any</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li style="clear: both;">
                                    <div id="crError"></div>
                                </li>
                            </ul>
                            <div id="" class="" style="text-align: center;padding: 20px 45px;clear: both;">
                                <ul>
                                    <li>
                                        <div class="ginput_container material-input input crrangepicker_container">
                                            <div href="#" class="gpx_form_tooltip">
                                                <i class="fa fa-info-circle"></i>
                                                <span class="tooltiptext tooltip-left">Select a date or range of dates you are willing to arrive.</span>
                                            </div>
                                            <input type="text" placeholder="* Dates you are willing to arrive" name="00N40000003DG5P" id="00N40000003DG5P" class="crDateFrom crrangepicker guest-reset filled" value="" required>
                                        </div>
                                    </li>
                                    <li>
                                        <div style="font-size: 11px;"><br /><br />* Required<br /><br /></div>
                                        <div id="restrictedTC"><strong>Terms & Conditions</strong><br />No requests will be taken for Southern California between June 1 and September 1.</div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <button href="#" class="dgt-btn submit-custom-request" data-id="booking-3" style="float: none;">Submit Request</button>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </form>
                        <div id="matchedContainer" style="display:none">
                            <div id="matchedModal">
                                <h3 style="color: #fff;">Matching Travel Found</h3>
                                <div class="row text-center"><a class="btn btn-primary" id="matchedTravelButton" href="">Book Travel</a></div>
                                <div class="row text-center" style="margin: 20px">OR</div>
                                <div class="row text-center"><a class="btn btn-primary cr-cancel" href="#" data-id="'.$lastID.'">Cancel</a></div>
                            </div>
                            <div id="notMatchedModal">
                                <div class="matched-modal">
                                    <h3 style="color: #fff;">Your request has been received.  You'll receive an email when a match is found.</h3>
                                </div>
                            </div>
                            <div id="restrictedMatchModal">
                                <div class="matched-modal">
                                    <h3 style="color: #fff;">Note: Your special request included weeks that are restricted.  These weeks have been removed from the results.</h3>
                                </div>
                            </div>
                        </div>

                        <!-- form end -->


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
