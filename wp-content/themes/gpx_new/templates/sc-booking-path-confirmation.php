<?php

use GPX\Model\Transaction;
use Illuminate\Support\Carbon;

/**
 * @var int $cid
 * @var Transaction $transaction
 * @var array $terms
 * @var bool $has_notes
 * @var ?Carbon $checkin
 * @var ?Carbon $checkout
 */
?>
<?php gpx_theme_template( 'booking-path-header', [ 'active' => 'confirm' ] ) ?>
<section class="booking booking-path booking-active booking-confirmation" id="booking-1">
    <div class="w-featured bg-gray-light w-result-home print-el">

        <div class="w-list-view dgt-container">

            <div class="confirm">
                <div class="cnt">
                    <h3>Payment Confirmation</h3>
                    <?php if ( $transaction->resort ): ?>
                        <p>Please take a moment to check the details of the reservation to ensure they are
                            correct. Any changes or cancellations to this reservation are subject to GPX’s
                            Terms & Conditions and must be made through GPX. This Confirmation must be
                            presented at the time of check-in at the Resort by the person whose name appears
                            as the Arriving Guest below.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ( $transaction->resort ): ?>
                <div class="w-item-view filtered">
                    <div class="view">
                        <div class="view-cnt">
                            <div class="descrip">
                                <hgroup>
                                    <h1><?= esc_html( $transaction->resort->ResortName ) ?></h1>
                                    <span><?= esc_html( $transaction->resort->Country ) ?> / <?= esc_html( $transaction->resort->Town ) ?> <?= esc_html( $transaction->resort->Region ) ?></span>
                                    <br>
                                    <span><strong>Resort ID:</strong> <?= esc_html( $transaction->resort->ResortID ) ?></span>
                                </hgroup>
                                <p>
                                    <?= gpx_format_address( $transaction->resort, false, ['Address1', 'Address2', 'Town', 'Region', 'Country'] ) ?>
                                </p>
                                <p>Assistance? Email: <a href="mailto:gpx@gpresorts.com" class="text-white">gpx@gpresorts.com</a>
                                    Call: <a href="tel:+18663256295" aria-label="call" class="text-white">866.325.6295</a></p>
                            </div>
                            <div class="w-status">
                                <a href="/resort-profile/?ResortID=<?= esc_attr( $transaction->resort->ResortID ) ?>"
                                   class="dgt-btn view" target="_blank">View Resort Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="container-profile">
                <div class="gateway">
                    <p>Reference Number: <?= esc_html( $transaction->id ) ?> Payment Gateway
                        Ref.: <?= esc_html( $transaction->paymentGatewayID ) ?></p>
                </div>
                <div class="list">
                    <?php if ( $transaction->resort ): ?>
                        <div class="item">
                            <ul>
                                <?php if ( isset( $transaction->data['MemberNumber'] ) ): ?>
                                    <li>
                                        <p>Membership Number:</p>
                                        <p><strong><?= esc_html( $transaction->data['MemberNumber'] ) ?></strong></p>
                                    </li>
                                <?php endif; ?>
                                <?php if ( isset( $transaction->data['MemberNumber'] ) ): ?>
                                    <li>
                                        <p>Member:</p>
                                        <p><strong><?= esc_html( $transaction->data['MemberName'] ) ?></strong></p>
                                    </li>
                                <?php endif; ?>
                                <?php if ( isset( $transaction->data['GuestName'] ) ): ?>
                                    <li>
                                        <p>Arriving Guest(s):</p>
                                        <?php if ( isset( $transaction->data['GuestName'] ) ): ?>
                                            <p><strong><?= esc_html( $transaction->data['GuestName'] ) ?></strong></p>
                                        <?php endif; ?>
                                        <?php if ( isset( $transaction->data['Adults'] ) || isset( $transaction->data['Children'] ) ): ?>
                                            <p><strong><?= (int) $transaction->data['Adults'] ?? 0 ?>
                                                    Adults, <?= (int) $transaction->data['Children'] ?? 0 ?>
                                                    Children</strong></p>
                                        <?php endif; ?>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="item">
                            <ul>
                                <?php if($checkin):?>
                                <li>
                                    <p>Check-In:</p>
                                    <p><strong><?= $transaction->resort->CheckInEarliest ? $checkin->format('d F, Y \a\t h:i A') : $checkin->format('d F, Y') ?></strong></p>
                                </li>
                                <?php endif;?>
                                <?php if($checkout):?>
                                <li>
                                    <p>Check-Out:</p>
                                    <p><strong><?= $checkout->format('d F, Y') ?></strong></p>
                                </li>
                                <?php endif;?>
                                <li>
                                    <p>Nights:</p>
                                    <p><strong><?= (int) $transaction->data['noNights'] ?></strong></p>
                                </li>
                                <li>
                                    <p>Unit Size:</p>
                                    <p><strong><?= (int) $transaction->data['bedrooms'] ?></strong>
                                        (sleeps <?= (int) $transaction->data['sleeps'] ?> max)</p>
                                </li>
                            </ul>
                        </div>
                        <div class="item last">
                            <ul>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <div class="item result">
                        <ul>
                            <li>
                                <div>
                                    <p>Total Paid:</p>
                                    <p><span><?= gpx_currency( $transaction->data['Paid'] ?? 0 ) ?></span></p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php if ( $transaction->resort ): ?>
                    <div class="w-expand">
                        <div class="expand_item" id="expand_item_1">
                            <div class="cnt-expand">
                                <h2>Important</h2>
                                <div class="w-list-availables" id="expand_3">
                                    <?php if ( $has_notes ): ?>
                                        <div class="cnt-list">
                                            <ul class="list-cnt full-list">
                                                <li>
                                                    <p><strong>Alert Note</strong></p>
                                                </li>
                                                <?php if ( ! empty( $transaction->resort->meta?->AlertNote ) ): ?>
                                                    <?php foreach ( $transaction->resort->meta?->AlertNote as $note ): ?>
                                                        <li class="mb-2">
                                                            Beginning <?php echo implode( " Ending ", array_map( fn( $date ) => date( 'm/d/y', $date ), $note['date'] ) ) ?>
                                                            :<br/><?= wp_kses_post( nl2br( $note['desc'] ) ) ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                <?php elseif ( ! empty( $transaction->resort->HTMLAlertNotes ) ): ?>
                                                    <li class="mb-2">
                                                        <div><?= wp_kses_post( nl2br( $transaction->resort->HTMLAlertNotes ) ) ?></div>
                                                    </li>
                                                <?php endif; ?>
                                                <?php if ( ! empty( $transaction->resort->AdditionalInfo ) ): ?>
                                                    <li>
                                                        <p><strong>Additional Info</strong></p>
                                                    </li>
                                                    <li>
                                                        <div><?= wp_kses_post( nl2br( $transaction->resort->AdditionalInfo ) ) ?></div>
                                                    </li>
                                                <?php endif; ?>

                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="cnt-seemore">
                                <a href="#" class="seemore">
                                    <span class="less">Read more</span>
                                    <i class="icon-arrow-down"></i>
                                </a>
                            </div>
                        </div>
                        <?php if ( $terms ): ?>
                            <div class="expand_item" id="expand_item_2">
                                <div class="cnt-expand">
                                    <h2>Terms & Conditions</h2>
                                    <div class="cnt">
                                        <?php foreach ( $terms as $promoTerm ): ?>
                                            <div class="whitespace-prewrap"><?= esc_html( $promoTerm ) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="cnt-seemore">
                                    <a href="#" class="seemore">
                                        <span class="less">Read more</span>
                                        <i class="icon-arrow-down"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="more-info">
                    <p>Grand Pacific Exchange • 5900 Pasteur Court Suite 100 • Carlsbad, CA 92008</p>
                    <p>Telephone: 1 (866) 325-6295 • (760) 827-4417 • Facsimile (760) 828-4242 •
                        GPX@gpresorts.com • www.gpxvacations.com</p>
                </div>
            </div>
        </div>
    </div>
</section>
