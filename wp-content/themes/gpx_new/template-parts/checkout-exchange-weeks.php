<?php
/**
 * @var int $cid
 * @var \stdClass $week
 * @var \Illuminate\Database\Eloquent\Collection $creditWeeks
 * @var string $expiredFee
 * @var array $ownerships
 */
?>
<?php if ( $creditWeeks->isEmpty() ): ?>
    <?php gpx_theme_template_part( 'checkout-exchange-header' ) ?>
<?php else: ?>
    <hgroup>
        <h2>Exchange Credit</h2>
        <p>Choose an exchange credit to use for this exchange booking.</p>
    </hgroup>
    <div x-ref="credits" class="checkout__exchangelist checkout__exchangelist--credit">
        <?php foreach ( $creditWeeks as $i => $creditWeek ): ?>
            <?php $expired = $creditWeek->isExpired( $week->checkIn ) ?>
            <?php $upgradeFee = $creditWeek->calculateUpgradeFee( $week->bedrooms, $creditWeek->getCreditBedrooms(), $week->resortId ) ?>
            <label data-id="<?= esc_attr( $creditWeek->id ) ?>"
                   class="checkout__exchangelist__item"
                   :class="{'selected': exchange.deposit.credit === <?= (int) $creditWeek->id ?>}"
            >
                <div class="w-credit">
                    <div class="head-credit <?= $expired ? 'disabled' : '' ?>">
                        <input type="radio"
                               class="exchange-credit-check if-perks-credit"
                               value="<?= esc_attr( $creditWeek->id ) ?>"
                               x-model.number="exchange.deposit.credit"
                               name="deposit"
                               data-creditweekid="<?= esc_attr( $creditWeek->id ) ?>"
                               data-creditexpiredfee="<?= $expired ? $expiredFee : '' ?>"
                            <?= $expired ? 'disabled' : '' ?>
                        >
                        <span>Apply Credit</span>
                    </div>
                    <div class="cnt-credit">
                        <ul>
                            <li>
                                <p><strong><?= esc_html( $creditWeek->resort_name ) ?></strong></p>
                                <p><?= esc_html( $creditWeek->CreditWeekID ) ?></p>
                            </li>
                            <li>
                                <p><strong>Expires:</strong></p>
                                <span><?= esc_html( $pendingReview ?? $creditWeek->credit_expiration_date ?? '' ) ?></span>
                            </li>
                            <li>
                                <p><strong>Entitlement Year:</strong> <?= esc_html( $creditWeek->deposit_year ) ?></p>
                            </li>
                            <li>
                                <p><strong>Size:</strong> <?= esc_html( $creditWeek->unit_type ) ?></p>
                            </li>
                            <?php if ( $upgradeFee ): ?>
                                <li>
                                    <p>Please note: This booking requires an upgrade fee</p>
                                </li>
                            <?php endif; ?>
                            <?php if ( $expired ): ?>
                                <li>
                                    <p>
                                        In order to complete the transaction you must pay a credit extension fee or
                                        deposit/select a different week to book against.<br><br>
                                        <button class="btn btn-primary pay-extension" data-tocart="no-redirect">
                                            Add Fee To Cart
                                        </button>
                                    </p>
                                    <input type="hidden" name="expired-fee" class="expired-fee"
                                           value="<?= esc_attr( $expiredFee ) ?>"/>
                                </li>
                            <?php elseif ( $creditWeek->Delinquent__c != 'No' ): ?>
                                <li>
                                    <p>
                                        Please contact us at <a href="tel:+18775667519">(877) 566-7519</a> to use this
                                        deposit.
                                    </p>
                                    <input type="hidden" name="expired-fee" class="expired-fee"
                                           value="<?= esc_attr( $expiredFee ) ?>"/>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </label>
        <?php endforeach; ?>
    </div>
    <p style="font-size: 18px; margin-top: 35px;">
        Don't see the credit you want to use? <a
            @click.prevent="exchange.show_additional_weeks = !exchange.show_additional_weeks" href="#useDeposit"
            class="toggleElement use-deposit"
            style="color: #009ad6;">Click here</a> to <span
        >show</span> additional weeks to deposit and use for this booking.
    </p>
<?php endif; ?>
<div id="useDeposit" x-show="exchange.show_additional_weeks" style="margin-top: 35px;">
    <hgroup>
        <h2>Use New Deposit</h2>
        <p>Select the week you would like to deposit as credit for this exchange.</p>
    </hgroup>
    <div name="exchangendeposit" id="exchangendeposit">
        <div x-ref="deposits" class="checkout__exchangelist checkout__exchangelist--deposit">
            <?php foreach ( $ownerships as $i => $ownership ): ?>
                <label
                    data-id="<?= esc_attr( $ownership['id'] ) ?>"
                    class="checkout__exchangelist__item"
                    :class="{'selected': exchange.deposit.deposit === <?= (int) $ownership['id'] ?>}"
                >
                    <div>
                        <div class="bank-row">
                            <input type="radio"
                                   class="exchange-credit-check if-perks-ownership"
                                   value="<?= esc_attr( $ownership['id'] ) ?>"
                                   x-model.number="exchange.deposit.deposit"
                                   name="deposit" data-creditweekid="deposit">
                        </div>
                        <div class="bank-row">
                            <h3><?= esc_html( $ownership['ResortName'] ) ?></h3>
                        </div>
                        <?php if ( $ownership['is_delinquent'] ): ?>
                            <strong>Please contact us at <a href="tel:+18775667519">(877) 566-7519</a> to use this
                                deposit.</strong>
                        <?php else: ?>
                            <div class="bank-row">
                                <span class="dgt-btn bank-select">Select</span>
                            </div>
                            <input type="hidden" name="Year" class="disswitch" disabled="disabled">
                            <input type="hidden" name="OwnershipID" class="switch-deposit"
                                   value="<?= esc_attr( $week->ResortName ) ?>">
                        <?php endif; ?>
                        <div class="bank-row">
                            Unit Type:
                            <?php if ( $ownership['defaultUpgrade'] ): ?>
                                <select name="Unit_Type__c" class="sel_unit_type doe"
                                        x-model="exchange.deposit.unit_type"
                                        :class="{'invisible': exchange.deposit.deposit !== <?= (int) $ownership['id'] ?>}"
                                >
                                    <option value="studio"
                                            data-upgradefee="<?= esc_attr( $ownership['defaultUpgrade']['studio'] ) ?>">
                                        Studio
                                    </option>
                                    <option value="1"
                                            data-upgradefee="<?= esc_attr( $ownership['defaultUpgrade']['1'] ) ?>">1br
                                    </option>
                                    <option value="2"
                                            data-upgradefee="<?= esc_attr( $ownership['defaultUpgrade']['2'] ) ?>">2br
                                    </option>
                                    <option value="3"
                                            data-upgradefee="<?= esc_attr( $ownership['defaultUpgrade']['3'] ) ?>">3br
                                    </option>
                                </select>
                            <?php else: ?>
                                <?= esc_html( $ownership['Room_Type__c'] ) ?>
                            <?php endif; ?>
                        </div>
                        <?php if ( $ownership['Week_Type__c'] ): ?>
                            <div class="bank-row">Week
                                Type: <?= esc_html( $ownership['Week_Type__c'] ) ?></div>
                        <?php endif; ?>
                        <div class="bank-row">Ownership Type:</div>
                        <?php if ( $ownership['Contract_ID__c'] ): ?>
                            <div class="bank-row">Resort Member
                                Number: <?= esc_html( $ownership['Contract_ID__c'] ) ?></div>
                        <?php endif; ?>
                        <?php if ( $ownership['Year_Last_Banked__c'] ): ?>
                            <div class="bank-row">Last Year
                                Banked: <?= esc_html( $ownership['Year_Last_Banked__c'] ) ?></div>
                        <?php endif; ?>

                    </div>
                    <div>
                        <div class="bank-row" style="margin-top:10px;"
                             :class="{'invisible': exchange.deposit.deposit !== <?= (int) $ownership['id'] ?>}">
                            <?php if ( ! $ownership['is_delinquent'] ): ?>
                                <input type="date"
                                       placeholder="Check In Date"
                                       name="Check_In_Date__c"
                                       class="form-control"
                                    <?php if ( $ownership['nextyear'] ): ?>
                                        min="<?= esc_attr( $ownership['nextyear'] ) ?>"
                                    <?php endif; ?>
                                       x-model="exchange.deposit.date"
                                       required
                                >
                            <?php endif; ?>
                        </div>
                        <div class="reswrap"
                             :class="{'invisible': exchange.deposit.deposit !== <?= (int) $ownership['id'] ?>}">
                            <input type="text" name="Reservation__c" placeholder="Reservation Number"
                                   class="form-control"
                                   x-model="exchange.deposit.reservation"
                                <?= $ownership['gpr'] ? '' : 'required' ?> />
                        </div>

                        <?php if ( $ownership['upgradeFee'] > 0 || $ownership['defaultUpgrade'] ): ?>
                            <div
                                class="bank-row doe_upgrade_msg" <?= $ownership['defaultUpgrade'] ? 'style="display: none;"' : '' ?>>
                                Please note: This booking requires an upgrade fee
                            </div>
                        <?php endif; ?>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <p id="floatDisc" style="font-size: 18px; margin-top: 35px;">
        *Float reservations must be made with your home resort prior to deposit. Deposit transactions will automatically
        be system verified. Unverified deposits may result in the cancellation of exchange reservations.
    </p>
</div>
