<?php
/**
 * @var int $cid
 * @var int $book
 * @var stdClass $prop
 * @var string $returnLink
 * @var array $exchange
 * @var \GPX\Model\UserMeta $usermeta
 * @var string $gfSlash
 */

?>

<section class="booking booking-exchange" :class="step == 'exchange' && 'booking-active'" id="booking-2">
    <div class="w-filter dgt-container">
        <div class="left">
            <h3>Your Next Booking</h3>
        </div>
        <div class="right">
            <a href="<?= site_url(); ?>/" class="remove-hold" data-pid="<?= $book ?>"
               data-cid="<?= $cid ?>" data-redirect="<?= $returnLink ?>" data-bookingpath="1">
                <h3><span>Cancel and Start New Search </span> <i class="icon-close"></i></h3>
            </a>
        </div>
    </div>
    <div class="w-featured bg-gray-light w-result-home">
        <div class="w-list-view dgt-container">
            <div class="w-item-view filtered">
                <div class="view">
                    <div class="view-cnt">
                        <img src="<?= $prop->image['thumbnail'] ?>" alt="<?= $prop->image['alt']; ?>"
                             title="<?= $prop->image['title'] ?>">
                    </div>
                    <div class="view-cnt">
                        <div class="descrip">
                            <hgroup>
                                <h2><?= $prop->ResortName ?></h2>
                                <span><?= $prop->Town ?>, <?= $prop->Region ?></span>
                            </hgroup>
                            <p>Check-In <?= date( 'd M Y', strtotime( $prop->checkIn ) ) ?></p>
                            <p>
                                Check-Out <?= date( 'd M Y', strtotime( $prop->checkIn . ' + ' . $prop->noNights . ' days' ) ) ?></p>
                        </div>
                        <div class="w-status">
                            <div class="result"></div>
                            <ul class="status">
                                <?php
                                if ( $prop->WeekType === 'ExchangeWeek' ): ?>
                                    <li>
                                        <div class="status-exchange"></div>
                                    </li>
                                <?php
                                elseif ( $prop->WeekType === 'BonusWeek' ): ?>
                                    <li>
                                        <div class="status-rental"></div>
                                    </li>
                                <?php
                                endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php if ( $prop->WeekType == 'ExchangeWeek' ): ?>
                    <div class="exchange-credit">
                        <div id="exchangeList" data-weekendpointid="<?= $prop->WeekEndpointID ?? '' ?>"
                             data-weekid="<?= $prop->weekId ?>" data-weektype="<?= $prop->WeekType ?>"
                             data-id="<?= $book ?>">
                            <?php if ( ! $exchange['error'] ): ?>
                                <?= $exchange['html'] ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bonus-week-details">
                        <div id="bonusWeekDetails" data-weekendpointid="<?= $prop->WeekEndpointID ?? '' ?>"
                             data-weekid="<?= $prop->weekId ?>" data-weektype="<?= $prop->WeekType ?>"
                             data-id="<?= $book ?>">
                        </div>
                    </div>
                <?php
                endif; ?>
            </div>
            <div class="member-form">
                <hgroup>
                    <h2>Member / Guest Information</h2>
                    <h2>GPX Member: <strong><?= $usermeta->getLastName() ?>
                            , <?= $usermeta->getFirstName() ?></strong></h2>
                </hgroup>
                <div class="w-form">
                    <form method="post" @submit.prevent="exchange.submitGuestInfo">
                        <div class="head-form">
                            <input type="checkbox" id="exchange-form-guest"
                                   @click="exchange.changeGuest" x-model="exchange.guest">
                            <label for="exchange-form-guest">
                                Click here to assign this reservation to a guest
                                <?php if ( $prop->guestFeesEnabled ): ?>
                                    <?php $gfAmount = $gfSlash ? '<span style="text-decoration: line-through;">$' . $gfSlash . '</span> ' : '$' . $prop->gfAmt; ?>
                                    (a fee of <?= $gfAmount ?> will be applied)
                                <?php endif; ?>
                            </label>
                            <dialog id="modal-guest-fees" class="dialog dialog--opaque"
                                    data-width="800" data-close-button="false" data-move-to-body="false"
                                    data-close-on-outside-click="false">
                                <div class="w-modal">
                                    <div class="member-form">
                                        <div class="w-form">
                                            <h2>Guest Fees Required</h2>
                                            <div class="gform_wrapper">
                                                <h4>By continuing you acknowledge that
                                                    a <?= $gfAmount ?> fee will be added to this
                                                    transaction at checkout.</h4>
                                                <button type="button"
                                                        @click="exchange.cancelGuest"
                                                        class="dgt-btn">Cancel
                                                </button>
                                                <button type="button" class="dgt-btn"
                                                        @click="exchange.acceptGuest"
                                                        style="margin-left: 10px;">Continue
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </dialog>
                        </div>
                        <div class="">
                            <div class="form w-1/2">
                                <div class="form-row">
                                    <label for="guest-form-first_name" class="form-label required">First
                                        Name</label>
                                    <input type="text" name="first_name" id="guest-form-first_name"
                                           @focus="if(!exchange.guest_asked && exchange.guest_fee && !exchange.guest) exchange.showModal()"
                                           @input="if(exchange.guest_fee && !exchange.guest) exchange.showModal()"
                                           class="form-input" x-model="exchange.first_name"
                                           required
                                           maxlength="255"
                                    >
                                    <div class="form-error" x-show="exchange.errors.first_name"
                                         x-text="exchange.errors.first_name"></div>
                                </div>
                                <div class="form-row">
                                    <label for="guest-form-last_name" class="form-label required">Last
                                        Name</label>
                                    <input type="text" name="last_name" id="guest-form-last_name"
                                           @focus="if(!exchange.guest_asked && exchange.guest_fee && !exchange.guest) exchange.showModal()"
                                           @input="if (exchange.guest_fee && !exchange.guest) exchange.showModal()"
                                           class="form-input" x-model="exchange.last_name" required
                                           maxlength="255">
                                    <div class="form-error" x-show="exchange.errors.last_name"
                                         x-text="exchange.errors.last_name"></div>
                                </div>
                                <div class="form-row">
                                    <label for="guest-form-email" class="form-label required">Email</label>
                                    <input type="email" name="email" id="guest-form-email"
                                           class="form-input" x-model="exchange.email" required
                                           maxlength="255">
                                    <div class="form-error" x-show="exchange.errors.email"
                                         x-text="exchange.errors.email"></div>
                                </div>
                                <div class="form-row">
                                    <label for="guest-form-phone" class="form-label required">Phone</label>
                                    <input type="tel" name="phone" id="guest-form-phone"
                                           class="form-input" x-model="exchange.phone" required
                                           maxlength="25">
                                    <div class="form-error" x-show="exchange.errors.phone"
                                         x-text="exchange.errors.phone"></div>
                                </div>
                                <div class="form-row">
                                    <label for="guest-form-adults"
                                           class="form-label required">Adults</label>
                                    <input type="number" min="1" max="<?= $prop->sleeps ?>" name="adults"
                                           id="guest-form-adults"
                                           class="form-input" x-model.number="exchange.adults" required>
                                    <div class="form-error" x-show="exchange.errors.adults"
                                         x-text="exchange.errors.adults"></div>
                                </div>
                                <div class="form-row">
                                    <label for="guest-form-children"
                                           class="form-label required">Children</label>
                                    <input type="number" min="0" max="<?= $prop->sleeps ?>" name="children"
                                           id="guest-form-children"
                                           class="form-input" x-model.number="exchange.children">
                                </div>
                                <div class="form-row">
                                    <label for="guest-form-special_request" class="form-label">Special
                                        Request</label>
                                    <textarea name="special_request" id="guest-form-special_request"
                                              class="form-input" rows="4"
                                              x-model="exchange.special_request"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="gform_footer">
                            <button type="submit" class="dgt-btn" :disabled="busy">
                                Checkout
                                <i x-show="busy" class="fa fa-refresh fa-spin fa-fw"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="dgt-container g-w-modal">
        <div class="dialog__overlay">
            <div id="modal-late-fee" class="dialog"
                 data-width="400" data-close-button="false" data-move-to-body="false"
                 data-close-on-outside-click="false">
                <div class="w-modal">
                    <h5>You will be required to pay a late deposit fee of $<span
                            x-text="exchange.deposit.late_fee_amount"></span> to complete this transaction.
                    </h5>
                    <br><br>
                    <div
                        class="flex flex-col flex-justify-center flex-align-stretch usw-button max-w-none gap-em">
                        <button type="button" class="dgt-btn w-full" @click.prevent="exchange.acceptFee()">
                            Add To Cart
                        </button>
                        <?php if ( $cid != get_current_user_id() ): ?>
                            <button type="button" class="dgt-btn af-agent-skip w-full"
                                    @click.prevent="exchange.waiveFee()">Waive Fee
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
