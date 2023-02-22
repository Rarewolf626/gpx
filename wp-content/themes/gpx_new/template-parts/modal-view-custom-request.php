<div class="dgt-container g-w-modal">
    <div class="dialog__overlay">
        <div id="modal-view-custom-request" class="dialog dialog--opaque" data-width="800" data-close-on-outside-click="true">
            <div class="w-modal">
                <div class="member-form">
                    <div class="w-form">
                        <h2>Custom Request</h2>
                        <div id="view-custom-request" class="form"></div>
                        <script type="text/template" id="tmpl-view-custom-request">
                            <div class="special-request-columns">
                                <div>
                                    <# if ( data.resort ) { #>
                                    <div class="form-row">
                                        <strong>Preferred Resort</strong>
                                        <div>{{ data.resort }}</div>
                                        <# if ( data.nearby ) { #>
                                        <div>Also include resorts within {{ data.miles }} miles.</div>
                                        <# } #>
                                    </div>
                                    <# } else if (data.region) { #>
                                    <div class="form-row">
                                        <strong>Region</strong>
                                        <div>{{ data.region }}</div>
                                    </div>
                                    <# if ( data.city ) { #>
                                    <div class="form-row">
                                        <strong>City / Sub Region</strong>
                                        <div>{{ data.city }}</div>
                                    </div>
                                    <# } #>
                                    <# } #>
                                    <div class="form-row">
                                        <strong>Your Email</strong>
                                        <div>{{ data.email }}</div>
                                    </div>
                                    <div class="form-row">
                                        <strong>Dates you are willing to arrive</strong>
                                        <div>
                                            <span>{{ data.checkIn }}</span>
                                            <# if ( data.checkIn2 ) { #>
                                            <span>to {{ data.checkIn2 }}</span>
                                            <# } #>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="form-row">
                                        <strong>Number of Adults</strong>
                                        <div>{{ data.adults }}</div>
                                    </div>
                                    <div class="form-row">
                                        <strong>Number of Children</strong>
                                        <div>{{ data.children }}</div>
                                    </div>

                                    <div class="form-row">
                                        <strong>Room Type</strong>
                                        <div>
                                            <span>{{ data.roomType }}</span>
                                            <# if ( data.larger && data.roomType != 'Any' ) { #>
                                            <em>or larger</em>
                                            <# } #>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <strong>Travel Week Preference</strong>
                                        <div>{{ data.preference }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row text-center" style="margin-top:2rem;">
                                <button type="button" class="form-button dialog-close">Close</button>
                            </div>
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
