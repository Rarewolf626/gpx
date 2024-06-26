<?php
/**
 * @var int $cid
 * @var int $book
 * @var stdClass $prop
 * @var string $returnLink
 */

?>

<section class="booking booking-path" :class="step == 'review' && 'booking-active'" id="booking-1">
    <div class="w-filter dgt-container">
        <div class="left">
            <h3>Your Next Vacation</h3>
        </div>
        <div class="right">
            <a href="<?= $returnLink ?>" class="remove-hold" data-pid="<?= $book ?>"
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
                                Check-Out <?= date( 'd M Y', strtotime( $prop->checkIn . ' + ' . $prop->noNights . ' days' ) ) ?>
                            </p>
                        </div>
                        <div class="w-status">
                            <div class="result">
                            </div>
                            <ul class="status">
                                <?php if ( $prop->WeekType === 'ExchangeWeek' ): ?>
                                    <li>
                                        <div class="status-exchange"></div>
                                    </li>
                                <?php elseif ( $prop->WeekType === 'BonusWeek' ): ?>
                                    <li>
                                        <div class="status-rental"></div>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="view-detail">
                    <ul class="list-result">
                        <li>
                            <p><strong>Select Week Number</strong></p>
                            <p><?= $prop->weekId ?></p>
                        </li>
                        <li>
                            <p><strong>Week Type</strong></p>
                            <p><?= $prop->DisplayWeekType ?></p>
                        </li>
                        <li>
                            <p><strong><?= $prop->priceorfee ?></strong></p>
                            <p>
                                <?= $prop->displayPrice ?>
                            </p>
                        </li>
                        <li>
                            <p><strong>Check In</strong></p>
                            <p><?= date( 'd M Y', strtotime( $prop->checkIn ) ) ?></p>
                        </li>
                        <li>
                            <p><strong>Check Out</strong></p>
                            <p><?= date( 'd M Y', strtotime( $prop->checkIn . ' + ' . $prop->noNights . ' days' ) ) ?></p>
                        </li>
                    </ul>
                    <ul class="list-result">
                        <li>
                            <p><strong>Nights</strong></p>
                            <p><?= $prop->noNights ?></p>
                        </li>
                        <li>
                            <p><strong>Bedrooms</strong></p>
                            <p><?= $prop->bedrooms ?></p>
                        </li>
                        <li>
                            <p><strong>Sleep</strong></p>
                            <p><?= $prop->sleeps ?></p>
                        </li>
                    </ul>
                </div>
                <?php if ( isset( $prop->specialDesc ) ): ?>
                    <dialog class="modal-special" id="spDesc<?= $prop->weekId ?>"
                            data-close-on-outside-click="false">
                        <div class="w-modal stupidbt-reset">
                            <p><?= $prop->specialDesc ?></p>
                        </div>
                    </dialog>
                <?php endif; ?>
            </div>
            <div class="tabs">
                <h2>Please Review Booking Policies Before Proceeding</h2>
                <div class="head-tab">
                    <ul>
                        <li>
                            <a href="" data-id="tab-1" class="head-active">Know Before You Go</a>
                        </li>
                        <li>
                            <a href="" data-id="tab-2">Terms & Conditions</a>
                        </li>
                    </ul>
                    <br><br>
                    <h2><strong>All transactions are non-refundable</strong></h2>
                    <br><br>
                </div>
                <div class="content-tabs">
                    <div id="tab-1" class="item-tab tab-active">
                        <div class="item-tab-cnt">
                            <?php if ( ! empty( $prop->AlertNote ) ): ?>
                                <?php if ( is_array( $prop->AlertNote ) ): ?>
                                    <ul class="albullet">
                                        <?php foreach ( $prop->AlertNote as $ral ): ?>
                                            <?php $theseDates = []; ?>
                                            <?php foreach ( $ral['date'] as $thisdate ): ?>
                                                <?php $theseDates[] = date( 'm/d/y', $thisdate ); ?>
                                            <?php endforeach; ?>
                                            <li>
                                                <strong>Beginning <?= implode( " Ending ", $theseDates ) ?>
                                                    :</strong><br/>
                                                <div
                                                    style="white-space: pre-wrap;"><?= stripslashes( $ral['desc'] ) ?></div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <div
                                        style="white-space: pre-wrap;"><?= stripslashes( $prop->AlertNote ) ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div
                                style="white-space: pre-wrap;"><?= stripslashes( $prop->AdditionalInfo ) ?></div>
                            <?php if ( ! empty( $prop->HTMLAlertNotes ) && empty( $prop->AlertNote ) ): ?>
                                <br><br>
                                <div
                                    style="white-space: pre-wrap;"><?= stripslashes( $prop->HTMLAlertNotes ) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="item-seemore">
                            <a href="#" class="seemore"> <span>See more</span> <i
                                    class="icon-arrow-down"></i></a>
                        </div>
                    </div>
                    <div id="tab-2" class="item-tab">
                        <div class="item-tab-cnt">
                            <?php if(isset($promoTerms) && is_array($promoTerms)):?>
                                <?php foreach ( $promoTerms as $promoTerm ): ?>
                                    <div
                                        style="white-space: pre-wrap;margin-bottom:10px;"><?= stripslashes( $promoTerm ) ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if ( isset( $terms ) && ! empty( $terms ) ): ?>
                                <p><?= $terms ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="item-seemore">
                            <a href="#" class="seemore"> <span>See more</span> <i
                                    class="icon-arrow-down"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <form class="check"
                  @submit.prevent="review.hold"
                  :class="!review.agree && review.error && 'error'"
            >
                <div x-show="review.message" class="hold-error" x-html="review.message"></div>
                <div class="cnt">
                    <input type="checkbox" id="chk_terms" x-model="review.agree"
                           @input="review.error = false">
                    <label for="chk_terms">
                        I have reviewed and understand the terms and conditions above
                    </label>
                </div>
                <div class="cnt">
                    <button type="submit" href="" class="dgt-btn btn-next"
                            :disabled="busy || review.error"
                            data-id="booking-2">
                        Next
                        <i x-show="busy" class="fa fa-refresh fa-spin fa-fw"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
