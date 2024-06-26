<?php
/**
 * @var ?stdClass $prop
 * @var ?int $cid
 * @var int $book
 * @var int $lpid
 * @var UserMeta $usermeta
 * @var wpdb $wpdb
 * @var string $role
 * @var array $terms
 * @var string $promoName
 * @var string $discountAmt
 * @var float $gfAmt
 * @var string $returnLink
 * @var array $upsellDisc
 * @var bool $property_error
 * @var \GPX\Model\Checkout\ShoppingCart $cart
 * @var ?PreHold $hold
 * @var ?array $exchange
 * @var array $owners
 * @var array $late_fees
 * @var array $tp_fees
 */

use GPX\Model\UserMeta;
use GPX\Model\PreHold;
use Illuminate\Support\Arr;

gpx_expired_member_redirect();
$gfSlash = 0;

$bogo = $bogo ?? $property_details['bogo'] ?? null;

$addLink = $bogo ? site_url() . "/promotion/" . $bogo : site_url();
$prop = $prop ?? null;
$hold = $hold ?? null;
$exchange = $exchange ?? null;
?>

<?php
if (get_option('gpx_booking_disabled_active') && gpx_user_has_role('gpx_member')): ?>
    <div id="bookingDisabledMessage" class="booking-disabled-check"
         data-msg="<?= esc_attr(get_option('gpx_booking_disabled_msg')); ?>"></div>
<?php
endif; ?>

<?php if (isset($unsetFilterMost)): ?>
    <div class="unset-filter-false"></div>
<?php endif; ?>

<?php gpx_theme_template_part('checkout-progress', [
    'step' => 'book',
    'hide_book' => false,
]) ?>
<?php gpx_theme_template_part('universal-search-widget') ?>

<?php if (!gpx_user_has_role('gpx_member') && $cid == get_current_user_id()): ?>
    <div class="agentLogin"></div>
<?php endif; ?>

<div id="checkout-app"
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
            'resort_id' => (int) $prop->resortId,
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
            'resort_id' => $ownership['resort_id'],
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
            'resort' => $creditWeek->resort_name,
            'expires' => gpx_format_date($creditWeek->credit_expiration_date, 'm/d/Y'),
            'year' => $creditWeek->deposit_year,
            'size' => $creditWeek->unit_type,
            'upgradeFee' => $creditWeek->upgradeFee,
            'expired' => $creditWeek->isExpired($prop->checkIn),
            'delinquent' => $creditWeek->Delinquent__c === 'Yes',
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
     data-owners="<?= esc_attr(json_encode($owners)) ?>"
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
