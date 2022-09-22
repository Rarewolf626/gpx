<div class="dgt-container g-w-modal">
    <div class="dialog__overlay">
        <div id="modal-special-request" class="dialog dialog--opaque" data-width="800"
             data-close-on-outside-click="false">
            <div class="w-modal">
                <div class="member-form">
                    <div class="w-form">
                        <h2>Custom Request</h2>
                        <!-- form start -->

                        <form name="special-request" id="form-special-request" class="form form-special-request"
                              action="<?php echo admin_url( 'admin-ajax.php?action=gpx_post_custom_request' ) ?>"
                              method="POST" novalidate>
                            <div class="special-request-columns">
                                <div>
                                    <div class="form-row">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Request by resort.</span>
                                        </div>
                                        <label for="special-request-resort" class="form-label">Preferred Resort</label>
                                        <input type="text" name="resort" id="special-request-resort" class="form-input"
                                               value="" autocomplete="off">
                                        <div class="resort-ac-error form-error hidden">Please select from available resorts.</div>
                                    </div>
                                    <div class="form-row">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Request nearby resort.  Resort availability within 30 miles will be returned when this field is checked.</span>
                                        </div>
                                        <label for="special-request-nearby" class="form-label">
                                            <input type="checkbox" name="nearby" id="special-request-nearby"
                                                   class="form-checkbox" value="1" checked>
                                            <span>I will Accept Nearby Resort Matches?</span>
                                        </label>
                                        <div class="nearby-ac-error form-error hidden"></div>
                                    </div>
                                    <div class="form-row">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Radius of flexibilty in miles if you are flexible</span>
                                        </div>
                                        <label for="special-request-miles" class="form-label">Flexible Miles?</label>
                                        <input type="number" step="5" min="0" max="350" name="miles"
                                               id="special-request-miles" class="form-input" value="30">
                                        <div class="miles-ac-error form-error hidden"></div>
                                    </div>
                                    <div class="bigorblock">OR</div>
                                    <div class="form-row">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Request by region or filter cities below by adding a region</span>
                                        </div>
                                        <label for="special-request-region" class="form-label">Region</label>
                                        <input type="text" name="region" id="special-request-region" class="form-input"
                                               value="" autocomplete="off">
                                        <div class="region-ac-error form-error hidden">
                                            Please select from available regions.
                                        </div>
                                    </div>
                                    <div class="form-row hidden">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">Request by city / sub region or filter resorts below by adding a value here.  When this field is used the region will be ignored.</span>
                                        </div>
                                        <label for="special-request-city" class="form-label">City / Sub Region</label>
                                        <input type="text" name="city" id="special-request-city" class="form-input"
                                               value="" autocomplete="off">
                                        <div class="city-ac-error form-error hidden">
                                            Please select from available cities.
                                        </div>
                                    </div>

                                </div>
                                <div>
                                    <div class="form-row">
                                        <label for="special-request-adults" class="form-label required">Number of
                                            Adults</label>
                                        <input type="number" step="1" min="0" name="adults" id="special-request-adults"
                                               class="form-input" value="1" required>
                                        <div class="adults-ac-error form-error hidden"></div>
                                    </div>
                                    <div class="form-row">
                                        <label for="special-request-children" class="form-label required">Number of
                                            Children</label>
                                        <input type="number" step="1" min="0" name="children"
                                               id="special-request-children" class="form-input" value="1">
                                        <div class="children-ac-error form-error hidden"></div>
                                    </div>
                                    <div class="form-row">
                                        <label for="special-request-email" class="form-label required">Your
                                            Email</label>
                                        <input type="email" class="form-input" id="special-request-email" maxlength="40"
                                               name="email" required autocomplete="off">
                                        <div class="email-ac-error form-error hidden"></div>
                                    </div>
                                    <div class="form-row">
                                        <label for="special-request-roomType" class="form-label required">Room
                                            Type</label>
                                        <select id="special-request-roomType" name="roomType" class="form-input"
                                                title="Room Type" required>
                                            <option value="Any" selected>Any</option>
                                            <option value="Studio">Studio</option>
                                            <option value="1BR">1BR</option>
                                            <option value="2BR">2BR</option>
                                            <option value="3BR">3BR</option>
                                        </select>
                                        <div class="roomType-ac-error form-error hidden"></div>
                                    </div>
                                    <div class="form-row">
                                        <div href="#" class="gpx_form_tooltip">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext tooltip-left">I would be willing to stay in a room at least the size of the room selected.  I understand an upgrade fee may be required in some cases.</span>
                                        </div>
                                        <label for="special-request-larger" class="form-label">
                                            <input type="checkbox" name="larger" id="special-request-larger"
                                                   class="form-checkbox" value="1" checked>
                                            <span>Or Larger</span>
                                        </label>
                                        <div class="larger-ac-error form-error hidden"></div>
                                    </div>
                                    <div class="form-row">
                                        <label for="special-request-preference" class="form-label required">Travel Week
                                            Preference</label>
                                        <select id="special-request-preference" name="preference" class="form-input"
                                                required>
                                            <option value="Exchange">Exchange</option>
                                            <option value="Rental">Rental</option>
                                            <option value="Any" selected>Any</option>
                                        </select>
                                        <div class="preference-ac-error form-error hidden"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div href="#" class="gpx_form_tooltip">
                                    <i class="fa fa-info-circle"></i>
                                    <span class="tooltiptext tooltip-left">Select a date or range of dates you are willing to arrive.</span>
                                </div>
                                <div class="form-label">Dates you are willing to arrive</div>
                                <div class="special-request-columns">
                                    <div class="form-row">
                                        <label for="special-request-checkIn" class="form-label required">From</label>
                                        <input id="special-request-checkIn" class="form-input" type="date" name="checkIn" value=""
                                               required>
                                        <div class="checkIn-ac-error form-error hidden"></div>
                                    </div>
                                    <div class="form-row">
                                        <label for="special-request-checkIn2" class="form-label">To</label>
                                        <input id="special-request-checkIn2" class="form-input" type="date" name="checkIn2" value="">
                                        <div class="checkIn2-ac-error form-error hidden"></div>
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: center;padding: 10px 45px;">
                                <div>
                                    <div style="font-size: 11px;color:red;">* Required</div>
                                    <div style="margin-top:10px;">
                                        <strong>Terms & Conditions</strong>
                                        <div>No requests will be taken for Southern California between June 1 and September 1.</div>
                                    </div>
                                </div>
                                <div style="margin-top:20px;">
                                    <button type="submit" class="form-button dgt-btn">Submit Request</button>
                                </div>
                            </div>
                        </form>
                        <div id="matchedContainer" style="display:none">
                            <div id="matchedModal">
                                <h3 style="color: #fff;">Matching Travel Found</h3>
                                <div class="row text-center"><a class="btn btn-primary" id="matchedTravelButton"
                                                                href="">Book Travel</a></div>
                                <div class="row text-center" style="margin: 20px">OR</div>
                                <div class="row text-center"><a class="btn btn-primary cr-cancel" href="#">Cancel</a>
                                </div>
                            </div>
                            <div id="notMatchedModal">
                                <div class="matched-modal">
                                    <h3 style="color: #fff;">Your request has been received. You'll receive an email
                                        when a match is found.</h3>
                                </div>
                            </div>
                            <div id="restrictedMatchModal">
                                <div class="matched-modal">
                                    <h3 style="color: #fff;">Note: Your special request included weeks that are
                                        restricted. These weeks have been removed from the results.</h3>
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
