<?php
/**
 * @var ?stdClass $prop
 * @var int $cid
 * @var \GPX\Model\Checkout\ShoppingCart $cart
 * @var UserMeta $usermeta
 * @var ?PreHold $hold
 * @var ?array $exchange
 * @var array $terms
 * @var array $late_fees
 * @var array $tp_fees
 */

use GPX\Model\PreHold;
use GPX\Model\UserMeta;
use Illuminate\Support\Arr;

gpx_expired_member_redirect();
get_template_part('booking-disabled');
$fbFee = get_option('gpx_fb_fee');
$prop = $prop ?? null;
$hold = $hold ?? null;
$exchange = $exchange ?? null;
?>

<?php gpx_theme_template_part( 'checkout-progress', [ 'step' => 'pay', 'hide_book' => !$cart->isBooking() ] ) ?>

<div id="checkout-app"
     data-payment
    <?php if ($prop): ?>
        data-week="<?= esc_attr(json_encode([
            'id' => $prop->id,
            'week_id' => $prop->weekId,
            'WeekType' => $cart->getWeekType(),
            'isExchange' => $prop->WeekType === 'ExchangeWeek',
            'week_type_display' => $prop->DisplayWeekType,
            'available' => (bool) $prop->active,
            'WeekPrice' => $prop->WeekPrice,
            'display_price' => $prop->displayPrice,
            'priceorfee' => $prop->priceorfee,
            'ResortName' => $prop->ResortName,
            'city' => $prop->Town,
            'region' => $prop->Region,
            'checkin' => $prop->checkIn,
            'no_nights' => (int) $prop->noNights,
            'sleeps' => (int) $prop->sleeps,
            'bedrooms' => (int) $prop->bedrooms,
            'special_desc' => $prop->specialDesc ?? '',
            'image' => $prop->image,
            'specialIcon' => $prop->specialIcon ?? '',
            'slash' => $prop->slash ?? '',
            'alert_notes' => is_array($prop->AlertNote) ? $prop->AlertNote : [],
            'notes' => is_string($prop->AlertNote) && !empty($prop->AlertNote) ? $prop->AlertNote : $prop->HTMLAlertNotes ?? '',
            'AdditionalInfo' => $prop->AdditionalInfo ?? '',
            'guestFeesEnabled' => $prop->guestFeesEnabled,
            'gfAmt' => $prop->gfAmt,
            'gfSlash' => $gfSlash ?? null,
        ])) ?>"
    <?php endif; ?>
    <?php if ($exchange): ?>
        data-error="<?= esc_attr($exchange['error'] ?: '') ?>"
        data-alert="<?= esc_attr($exchange['alert'] ?: '') ?>"
        data-exchange="<?= esc_attr(json_encode($exchange)) ?>"
        data-ownerships="<?= esc_attr(json_encode(empty($exchange['ownerships']) ? [] : array_map(fn($ownership) => [
            'id' => (int) $ownership['id'],
            'is_delinquent' => (bool) $ownership['is_delinquent'],
            'ResortName' => $ownership['ResortName'],
            'Room_Type__c' => $ownership['Room_Type__c'],
            'Week_Type__c' => $ownership['Week_Type__c'],
            'Contract_ID__c' => $ownership['Contract_ID__c'],
            'Year_Last_Banked__c' => $ownership['Year_Last_Banked__c'],
            'next_year' => $ownership['next_year'],
            'gpr' => (bool)$ownership['gpr'],
            'defaultUpgrade' => $ownership['defaultUpgrade'],
            'upgradeFee' => $ownership['upgradeFee'],
            'third_party_deposit_fee_enabled' => $ownership['third_party_deposit_fee_enabled'],
        ], $exchange['ownerships']->toArray()))) ?>"
        data-credits="<?= esc_attr(json_encode($exchange['creditWeeks']->map(fn($creditWeek) => [
            'id' => (int) $creditWeek->id,
        ]))) ?>"
    <?php endif; ?>
     data-user="<?= esc_attr(json_encode([
         'first_name' => $usermeta->getFirstName(),
         'last_name' => $usermeta->getLastName(),
         'name' => $usermeta->getName(),
         'email' => $usermeta->getEmailAddress(),
         'phone' => $usermeta->getDayPhone(),
         'address' => $usermeta->getAddress(),
         'city' => $usermeta->getCity(),
         'state' => $usermeta->getState(),
         'zip' => $usermeta->getPostalCode(),
         'country' => $usermeta->getCountry(),
         'is_agent' => $cart->isAgent(),
     ])) ?>"
     data-fees="<?= esc_attr(json_encode([
         'flex' => $cart->item()->cpo_fee,
         'show_flex' => $cart->item()->canAddFlex(),
         'guest' => $cart->item()->guest_fee,
         'late_days' => $late_fees['days'],
         'late_extra_days' => $late_fees['extra_days'],
         'late_fee' => $late_fees['fee'],
         'late_extra_fee' => $late_fees['extra_fee'],
         'third_party_fee' => $tp_fees['fee'],
         'third_party_days' => $tp_fees['days'],
         'expired' => (int) get_option('gpx_extension_fee'),
         'resort_fee' => $prop?->ResortFeeSettings ?? ['enabled' => false, 'fee' => 0, 'total' => 0],
     ])) ?>"
    <?php if ($hold): ?>
        data-hold="<?= esc_attr($hold?->release_on->format('c')) ?>"
    <?php endif; ?>
     data-expiredfee="<?= esc_attr((int) get_option('gpx_extension_fee')) ?>"
     data-terms="<?= esc_attr(json_encode(Arr::wrap($terms))) ?>"
     data-cart="<?= esc_attr(json_encode($cart->toArray())) ?>"
></div>



<div id="flexbooking" style="display: none;">
	<p>Grand Pacific Exchange offers the option of purchasing Flex Booking for an additional fee of $<?=$fbFee?> paid
	   concurrently when booking an Exchange provided that the Exchange is made more than 45 days from the Check-In Date.
	   If purchased, Flex Booking covers you in the event you are not able to utilize your reserved vacation and need to
	   request cancellation of your Exchange. Provided your cancellation is submitted more than forty-five (45) days prior
	   to the Confirmed Exchange Check-In date, under Flex Booking (i) the original Exchange Credit will be returned to the
	   your GPX Account and will expire two (2) years from the original Exchange Credit start date; and (ii) the Exchange and
	   any Upgrade Fees paid for the Confirmed Exchange will be refunded in the form of a coupon code valid for one year that
	   may be applied towards your next Exchange or Rental Booking. If at the time of the new booking, the cost exceeds the
	   amount of the coupon code, the GPX Member must pay the incremental increase. Flex Booking may be purchased for any Exchange.
	   No monetary refunds are distributed for cancellations at any time.</p>
	<p>Flex Booking cannot be used to cancel a Confirmed Exchange and then re-book the same Resort Week as either a Rental Week or
	   Additional Benefit.</p>
	<p>Flex Booking is optional. Members who decline Flex Booking in connection with a Confirmed Exchange will forfeit their Exchange
	   Fee upon cancellation of a Confirmed Exchange, which includes any change in dates, unit type, vacation area or Resorts. Flex
	   Booking is not available for Rental Weeks including, without limitation, special or promotional offers.</p>
</div>
