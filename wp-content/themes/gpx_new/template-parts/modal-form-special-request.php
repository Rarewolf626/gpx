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
                              action="<?php echo admin_url( 'admin-ajax.php?action=gpx_post_special_request' ) ?>"
                              method="POST" novalidate>
                            <div class="special-request-columns">
                                <div>
                                    <div class="form-row">
                                        <label for="special-request-resort" class="form-label">Preferred Resort</label>
                                        <input type="text" name="resort" id="special-request-resort" class="form-input"
                                               value="" autocomplete="off">
                                        <div class="resort-ac-error form-error hidden">Please select from available resorts.</div>
                                    </div>
                                    <div class="form-row">
                                        <label for="special-request-nearby" class="form-label">
                                            <input type="checkbox" name="nearby" id="special-request-nearby"
                                                   class="form-checkbox" value="1" checked>
                                            <span>I will Accept Nearby Resort Matches?</span>
                                        </label>
                                        <div class="nearby-ac-error form-error hidden"></div>
                                    </div>

                                    <div class="bigorblock">OR</div>
                                    <div class="form-row">
                                        <label for="special-request-region" class="form-label">Region</label>
                                        <input type="text" name="region" id="special-request-region" class="form-input"
                                               value="" autocomplete="off">
                                        <div class="region-ac-error form-error hidden">
                                            Please select from available regions.
                                        </div>
                                    </div>
                                    <div class="form-row hidden">
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
                                <div class="form-label">Dates you are willing to arrive</div>
                                <div class="special-request-columns">
                                    <div class="form-row">
                                        <label for="special-request-checkIn" class="form-label required">From</label>
                                        <input id="special-request-checkIn" class="form-input" type="date" name="checkIn" value=""
                                               required>
                                        <div class="checkIn-ac-error form-error hidden"></div>
                                    </div>
                                    <div class="form-row">
                                        <label for="special-request-checkIn2" class="form-label required">To</label>
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
                        <div id="special-request-debug" class="hidden" style="margin:10px 0;text-center;">
                            <div class="message" style="color:red;text-align:center;font-weight:bold;font-size:1.2em;"></div>
                            <table style="width:auto;margin:0 auto;">
                                <tr>
                                    <th style="text-align:right;font-weight:bold;padding-right:5px;">Active Intervals:</th>
                                    <td class="intervals-count">0</td>
                                </tr>
                                <tr>
                                    <th style="text-align:right;font-weight:bold;padding-right:5px;">Credit Balance:</th>
                                    <td class="credits-count">0</td>
                                </tr>
                                <tr>
                                    <th style="text-align:right;font-weight:bold;padding-right:5px;">Open Custom Requests:</th>
                                    <td class="requests-count">0</td>
                                </tr>
                            </table>
                        </div>
                        <template id="special-request-results">
                            <h3 style="color: #fff;"></h3>
                            <div class="special-request-results-matched">
                                <div class="row text-center">
                                    <a class="btn btn-primary matchedTravelButton" href="">Book Travel</a>
                                </div>
                                <div class="row text-center" style="margin: 20px">OR</div>
                                <div class="row text-center">
                                    <a class="btn btn-primary dialog-close" href="#">Cancel</a>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
