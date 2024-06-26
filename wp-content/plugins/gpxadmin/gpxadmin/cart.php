<?php


use GPX\Model\Cart;
use GPX\Model\Week;
use GPX\Model\Owner;
use GPX\Model\Credit;
use GPX\Model\PreHold;
use GPX\Model\Special;
use GPX\Model\Payment;
use GPX\Model\Partner;
use GPX\Model\UserMeta;
use GPX\Model\TaxAudit;
use GPX\Model\ResortMeta;
use GPX\Model\AutoCoupon;
use GPX\Model\Transaction;
use GPX\Model\CustomRequest;
use Illuminate\Support\Carbon;
use GPX\Model\Enum\ResortPath;
use GPX\Form\Checkout\GuestForm;
use GPX\Model\DepositOnExchange;
use GPX\Model\OwnerCreditCoupon;
use GPX\Model\FailedTransactions;
use GPX\Repository\WeekRepository;
use GPX\Form\Checkout\PaymentForm;
use GPX\ShiftFour\PaymentResponse;
use Illuminate\Support\Collection;
use GPX\Api\Salesforce\Salesforce;
use GPX\Repository\OwnerRepository;
use GPX\Model\Checkout\ShoppingCart;
use GPX\Repository\ResortRepository;
use GPX\Repository\SpecialsRepository;
use GPX\Form\Checkout\DepositWeekForm;
use GPX\Repository\IntervalRepository;
use GPX\Model\Checkout\Item\RentalWeek;
use GPX\Model\Checkout\Item\ExtendWeek;
use GPX\Model\Checkout\Item\DepositWeek;
use GPX\Model\OwnerCreditCouponActivity;
use GPX\Model\Checkout\Item\ExchangeWeek;
use GPX\Form\Checkout\BillingAddressForm;
use GPX\Repository\TransactionRepository;
use GPX\Command\Transaction\UpdateGuestInfo;
use GPX\Exception\ShiftFour\InvalidJsonResponse;

function gpx_book_link_savesearch() {
    $pid = gpx_request('pid');
    if (!$pid) {
        wp_send_json(['success' => false]);
    }
    $cid = gpx_get_switch_user_cookie();
    if ($cid) {
        $search = gpx_request()->input();
        $lpid = gpx_request('lpid');
        $type = gpx_request('type');
        if (!empty($lpid)) {
            $special = Special::promo()->active()->current()->find($lpid);
            if ($special) {
                update_user_meta($cid, 'lppromoid', $special->id);
                $search['lpid'] = $special->id;
            }
        }
        save_search_book($pid, $search);
    }

    $return['success'] = true;

    wp_send_json($return);
}

add_action("wp_ajax_gpx_book_link_savesearch", "gpx_book_link_savesearch");
add_action("wp_ajax_nopriv_gpx_book_link_savesearch", "gpx_book_link_savesearch");

function gpx_booking_path_sc($atts): string {
    global $wpdb;

    $atts = shortcode_atts(
        [
            'terms' => '',
        ],
        $atts,
        'gpx_booking_path');

    $cid = gpx_get_switch_user_cookie();
    $returnLink = $_SERVER['REQUEST_URI'] ?? null;
    $book = (int) gpx_request('book') ?: null;
    $type = gpx_request('type');
    $errorMessage = '';

    if (!$cid) {
        return gpx_theme_template('booking-path-unauthenticated.php', compact('cid', 'book', 'returnLink'), false);
    }
    $cart = gpx_get_cart($cid);
    if (($book && $cart->weekid != $book) || ($type && $cart->getWeekType() != $type)) {
        $cart = gpx_create_cart($cid);
        $item = $cart->createItem($type, $book);
        $cart->setItem($item);
    }
    if (!$book && ($cart->isDeposit() || $cart->isGuestFee())) {
        // deposits (and maybe extensions) should go straight to payment page
        // guest fee should go straight to payment page
        wp_redirect(site_url('booking-path-payment'));
    }
    $book = $book ?? $cart->weekid;
    $type = $type ?? $cart->getWeekType();
    if (!$book && !$cart->hasItem()) {
        // there is nothing in the cart
        return gpx_theme_template('sc-booking-path-payment-empty.php', compact('cid', 'returnLink'), false);
    }
    if (!$book && $cart->isBooking()) {
        // this is a booking request without providing a week
        return gpx_theme_template('booking-path-invalid.php', compact('returnLink', 'cid', 'book'), false);
    }

    $credits = OwnerRepository::instance()->get_credits($cid);

    $sql = $wpdb->prepare("SELECT a.*, b.ResortName, c.deposit_year FROM wp_owner_interval a
            INNER JOIN wp_resorts b ON b.gprID LIKE CONCAT(BINARY a.resortID, '%%')
            LEFT JOIN (SELECT MAX(deposit_year) as deposit_year, interval_number FROM wp_credit WHERE status != 'Pending' GROUP BY interval_number) c ON c.interval_number=a.contractID
            WHERE a.Contract_Status__c != 'Cancelled'
            AND a.userID = %d", $cid);
    $ownerships = $wpdb->get_results($sql, ARRAY_A);

    //Rule is # of Ownerships  (i.e. have 2 weeks, can have account go to negative 2, one per week)
    $newcredit = ($credits - 1) * -1;

    if ($newcredit > count($ownerships)) {
        $errorMessage = 'Please deposit a week to continue.';
    }

    $usermeta = UserMeta::load($cid);


    $owners = collect();

    Owner::select(['id', 'user_id', 'SPI_Owner_Name_1st__c', 'SPI_Email__c', 'SPI_Home_Phone__c'])
         ->where('user_id', '=', $cid)
         ->each(function ($owner) use ($owners) {
             if (!$owners->contains('name', $owner->SPI_Owner_Name_1st__c)) {
                 $name = explode(' ', $owner->SPI_Owner_Name_1st__c, 2);
                 $owners->push([
                     'id' => $owner->id,
                     'first_name' => trim($name[0] ?? ''),
                     'last_name' => trim($name[1] ?? ''),
                     'name' => $owner->SPI_Owner_Name_1st__c,
                     'email' => $owner->SPI_Email__c,
                     'phone' => $owner->SPI_Home_Phone__c,
                 ]);
             }
         });
    if (!$owners->contains('name', $usermeta->getName())) {
        $owners->push([
            'id' => -1,
            'first_name' => $usermeta->getFirstName(),
            'last_name' => $usermeta->getLastName(),
            'name' => $usermeta->getName(),
            'email' => $usermeta->getEmailAddress(),
            'phone' => $usermeta->getPhone(),
        ]);
    }
    if (!empty($usermeta->getSecondaryName()) && !str_contains(mb_strtolower($usermeta->getSecondaryName()), 'c/o') && !$owners->contains('name', $usermeta->getSecondaryName())) {
        $owners->push([
            'id' => 0,
            'first_name' => $usermeta->FirstName2,
            'last_name' => $usermeta->LastName2,
            'name' => $usermeta->getSecondaryName(),
            'email' => mb_strtolower($usermeta->Email2 ?? ''),
            'phone' => gpx_format_phone($usermeta->Mobile2),
        ]);
    }

    $property_details = [];
    $exchange = [
        'CPOFee' => 0.00,
        'CPOPrice' => get_option('gpx_fb_fee'),
        'error' => '',
        'alert' => '',
        'resortName' => '',
        'creditWeeks' => collect(),
        'ownerships' => [],
    ];
    if ($book) {
        $property_details = get_property_details($book, $cid);
        $property_error = isset($property_details['error']);

        if ($property_error) {
            return gpx_theme_template('booking-path-invalid.php', compact('returnLink', 'cid', 'book'), false);
        }


        if ($errorMessage && in_array($property_details['prop']->WeekType, ['ExchangeWeek', 'Exchange Week'])) {
            return gpx_theme_template('booking-path-error.php', compact('returnLink', 'cid', 'book', 'errorMessage'), false);
        }

        save_search($usermeta, '', 'select', '', $property_details);

        $exchange = gpx_get_exchange_details($property_details['prop']);
        if ($book !== $cart->weekid) {
            $item = $cart->createItem($type, $book);
            $cart->setItem($item);
        }

        $hold = PreHold::forUser($cid)->forWeek($book)->released(false)->orderBy('release_on', 'desc')->limit(1)->first();
        if ($hold?->isExpired()) {
            gpx_delete_user_week_hold($cid, $book);
            $hold = null;
        }
        $cart->setHold($hold);
    }


    $terms = $cart->item()->getPromos()->map(fn($promo) => $promo->Properties?->terms)->filter()->toArray();
    if ($atts['terms']) {
        $terms[] = $atts['terms'];
    }

    return gpx_theme_template('sc-booking-path', array_merge([
        'cid' => $cid,
        'book' => $book,
        'cart' => $cart,
        'cartid' => $cart->cartid,
        'hold' => $hold ?? null,
        'lpid' => $property_details['lpid'] ?? null,
        'property_error' => $property_error ?? false,
        'errorMessage' => $errorMessage,
        'usermeta' => $usermeta,
        'owners' => $owners,
        'exchange' => $exchange ?? null,
        'terms' => $terms,
        'late_fees' => gpx_get_late_fee_settings(),
        'tp_fees' => gpx_get_third_party_fee_settings(),

    ], $property_details), false);
}

add_shortcode('gpx_booking_path', 'gpx_booking_path_sc');

function gpx_booking_path_payment_sc($atts): string {
    $atts = shortcode_atts(
        [
            'terms' => '',
        ],
        $atts,
        'gpx_booking_path_payment');

    $errorMessage = '';
    $returnLink = $_SERVER['REQUEST_URI'] ?? null;
    $cid = gpx_get_switch_user_cookie();
    if (!$cid) {
        return gpx_theme_template('booking-path-unauthenticated.php', compact('cid', 'returnLink'), false);
    }

    $cart = gpx_get_cart($cid);
    $book = $cart->weekid;
    $type = $cart->getWeekType();

    if (!$cart->hasItem()) {
        // there is nothing in the cart
        return gpx_theme_template('sc-booking-path-payment-empty.php', compact('cid', 'returnLink'), false);
    }
    if ($cart->isBooking() && !$cart->weekid) {
        return gpx_theme_template('booking-path-invalid.php', compact('returnLink', 'cid', 'book'), false);
    }

    $usermeta = UserMeta::load($cid);
    $property_details = [];

    if ($cart->isBooking() && !gpx_is_week_available($cart->item->week, true, $cart->cid)) {
        return gpx_theme_template('booking-path-invalid.php', compact('returnLink', 'cid', 'book'), false);
    }

    if ($cart->weekid) {
        $property_details = get_property_details($book, $cid);
        $property_error = isset($property_details['error']);

        if ($property_error) {
            return gpx_theme_template('booking-path-invalid.php', compact('returnLink', 'cid', 'book'), false);
        }


        if ($errorMessage && in_array($property_details['prop']->WeekType, ['ExchangeWeek', 'Exchange Week'])) {
            return gpx_theme_template('booking-path-error.php', compact('returnLink', 'cid', 'book', 'errorMessage'), false);
        }

        save_search($usermeta, '', 'select', '', $property_details);

        $exchange = gpx_get_exchange_details($property_details['prop']);
        if ($book !== $cart->weekid) {
            $item = $cart->createItem($book, $type);
            $cart->setItem($item);
        }

        $hold = PreHold::forUser($cid)->forWeek($book)->released(false)->orderBy('release_on', 'desc')->first();
        if ($hold && $hold->isExpired()) {
            gpx_delete_user_week_hold($cid, $book);
            $hold = null;
        }
        $cart->setHold($hold);
    }
    // Do not show promo terms on payment page anymore.
    // $terms = $cart->item()->getPromos()->map(fn($promo) => $promo->Properties?->terms)->filter()->toArray();
    // if ($atts['terms']) {
    //   $terms[] = $atts['terms'];
    // }
    $terms = $atts['terms'] ?? '';

    return gpx_theme_template('sc-booking-path-payment', array_merge([
        'cid' => $cid,
        'book' => $book,
        'cart' => $cart,
        'cartid' => $cart->cartid,
        'hold' => $hold ?? null,
        'lpid' => null,
        'property_error' => $property_error ?? false,
        'errorMessage' => $errorMessage,
        'usermeta' => $usermeta,
        'exchange' => $exchange ?? null,
        'terms' => $terms,
        'late_fees' => gpx_get_late_fee_settings(),
        'tp_fees' => gpx_get_third_party_fee_settings(),

    ], $property_details), false);
}

add_shortcode('gpx_booking_path_payment', 'gpx_booking_path_payment_sc');


function gpx_booking_path_confirmation_cs(): string {

    $cid = gpx_get_switch_user_cookie();
    $cartID = gpx_request('confirmation');
    if (!$cartID) {
        return gpx_theme_template('booking-confirmation-transaction-invalid', compact('cid'), false);
    }

    $transaction = Transaction::forUser($cid)
                              ->byCart($cartID)
                              ->cancelled(false)
                              ->with('resort')
                              ->first();
    if (!$transaction) {
        return gpx_theme_template('booking-confirmation-transaction-invalid', compact('cid'), false);
    }

    $terms = [];
    if (!empty($transaction->data['promoName'])) {
        /** @var Special $promo */
        $promo = Special::where('Name', 'LIKE', '%' . $transaction->data['promoName'] . '%')->first();
        if ($promo) $terms = array_merge($terms, $promo->terms);
    }

    $has_notes = false;
    $checkin = null;
    $checkout = null;
    if ($transaction->resort) {
        $transaction->resort->meta = ResortMeta::load($transaction->resort->ResortID, [
            'AlertNote',
            'AdditionalInfo',
        ]);
        if ($transaction->resort->HTMLAlertNotes) $has_notes = true;
        if ($transaction->resort->meta->AdditionalInfo) $has_notes = true;
        if ($transaction->resort->meta->AlertNote) $has_notes = true;

        if (!empty($transaction->data['checkIn'])) {
            $checkin = Carbon::parse($transaction->data['checkIn']);
            if ($transaction->resort->CheckInEarliest) {
                $checkin->setTimeFromTimeString($transaction->resort->CheckInEarliest);
            }
            $checkout = $checkin->clone()->addDays($transaction->data['noNights'] ?? 0);
            if ($transaction->resort->CheckOutLatest) {
                $checkout->setTimeFromTimeString($transaction->resort->CheckOutLatest);
            }
        }
    }

    return gpx_theme_template('sc-booking-path-confirmation', compact('cid', 'transaction', 'terms', 'has_notes', 'checkin', 'checkout'), false);
}

add_shortcode('gpx_booking_path_confirmation', 'gpx_booking_path_confirmation_cs');

function gpx_create_cart(int $cid = null): ShoppingCart {
    $cid = $cid ?? gpx_get_switch_user_cookie();
    $cart = new ShoppingCart($cid);
    $cart->setAgent($cid !== get_current_user_id());

    return $cart;
}

function gpx_get_cart(int $cid = null): ShoppingCart {
    $cid = $cid ?? gpx_get_switch_user_cookie();
    /** @var ShoppingCart $cart */
    static $cart;
    if ($cart && $cart->cid === $cid) {
        return $cart;
    }

    /** @var ?Cart $data */
    $data = Cart::where('user', '=', $cid)->first();
    $cart = $data ? ShoppingCart::fromCart($data) : new ShoppingCart($cid);
    $cart->setAgent($cid !== get_current_user_id());

    return $cart;
}


/**
 * Places selected week on hold during checkout
 */
function gpx_checkout_hold(): void {
    $pid = gpx_request('pid');
    $type = gpx_request('weekType');
    $cid = gpx_get_switch_user_cookie();
    if (!is_user_logged_in()) {
        wp_send_json([
            'success' => false,
            'message' => 'You must be logged in to book a property.',
            'login' => true,
        ], 401);
    }
    $liid = get_current_user_id();
    $agentOrOwner = $cid != $liid ? 'agent' : 'owner';

    $week = Week::find($pid);
    if (!$week) {
        wp_send_json([
            'success' => false,
            'message' => 'Requested week was not found.',
        ], 404);
    }

    if (Transaction::forWeek($pid)->cancelled(false)->exists()) {
        // The week is already booked
        $week->update(['active' => false]);
        gpx_delete_cart(true);
        wp_send_json([
            'success' => false,
            'message' => 'This week is no longer available.',
            'inactive' => true,
        ], 403);
    }

    $hold = PreHold::where('weekId', '=', $pid)
                   ->released(false)
                   ->orderBy('release_on', 'desc')
                   ->limit(1)
                   ->first();

    if ($hold && $hold->user != $cid) {
        // The week is already on hold by another user
        gpx_delete_cart(true);
        wp_send_json([
            'success' => false,
            'message' => 'This week is no longer available.',
            'inactive' => true,
        ], 403);
    }

    $holdcount = count(WeekRepository::instance()->get_weeks_on_hold($cid));
    $credits = OwnerRepository::instance()->get_credits($cid);
    if ($agentOrOwner === 'owner' && $credits + 1 <= $holdcount) {
        // The user has reached their hold limit
        wp_send_json(['success' => false, 'message' => get_option('gpx_hold_error_message')], 403);
    }

    // Put week on hold

    $timeLimit = get_option('gpx_hold_limt_time') ?: '24';

    $activeUser = get_userdata($liid);
    $holdDets = $hold?->data ?? [
        time() => [
            'action' => 'held',
            'by' => $activeUser->first_name . " " . $activeUser->last_name,
        ],
    ];

    $release_date = Carbon::now()->addHours($timeLimit);
    if (!$hold) {
        // this week was not on hold by current user
        $hold = new PreHold(['release_on' => $release_date]);
    } elseif ($release_date->greaterThan($hold->release_on)) {
        // this week was on hold by current user, but the release date needs to be renewed
        $holdDets[time()] = [
            'action' => 'held',
            'by' => $activeUser->first_name . " " . $activeUser->last_name,
        ];
        $hold->release_on = $release_date;
    }

    $hold->fill([
        'propertyID' => $pid,
        'weekId' => $pid,
        'user' => $cid,
        'lpid' => $liid,
        'released' => 0,
        'data' => $holdDets,
        'weekType' => $type,
    ]);
    $hold->save();

    // Set held room to inactive
    $week->update(['active' => false]);

    wp_send_json([
        'success' => true,
        'message' => 'Success',
        'holdId' => $hold->id,
        'weekId' => $pid,
        'release_on' => $hold->release_on->format('m/d/Y H:i:s'),
    ]);
}

add_action('wp_ajax_gpx_checkout_hold', 'gpx_checkout_hold');
add_action('wp_ajax_nopriv_gpx_checkout_hold', 'gpx_checkout_hold');

function gpx_get_pricing(Week|int $week = null, string $type = 'ExchangeWeek', int $cid = null, Special $special = null): array {
    if (is_numeric($week)) {
        $week = Week::find($week);
    }
    $guestFee = 0.00;
    if (get_option('gpx_global_guest_fees') && (get_option('gpx_gf_amount') && get_option('gpx_gf_amount') > $guestFee)) {
        $guestFee = (float) get_option('gpx_gf_amount');
    }
    if (isset($fees->GuestFeeAmount)) {
        $guestFee = (float) $fees->GuestFeeAmount;
    }

    $exchangeFee = (float) gpx_get_exchange_fee($cid, $week);

    if (!$week) {
        return [
            'price' => 0.00,
            'exchange' => $exchangeFee,
            'exchange_same_resort' => 0.00,
            'extension' => (float) get_option('gpx_extension_fee'),
            'rental' => 0.00,
            'flex' => (float) get_option('gpx_fb_fee') ?? 0.00,
            'guest' => $guestFee,
            'upgrade' => 0.00,
            'sevenDays' => (int) get_option('gpx_late_deposit_fee_within'),
            'fifteenDays' => (int) get_option('gpx_late_deposit_fee'),
            'tp_deposit' => (int) get_option('gpx_third_party_fee', 50),
            'discount' => 0.00,
            'promo' => null,
            'special' => 0.00,
            'promos' => collect(),
        ];
    }
    $cid = $cid ?? gpx_get_switch_user_cookie();
    $is_exchange = str_contains(mb_strtolower($type), 'exchange');
    $week->loadMissing('theresort');
    $fees = ResortRepository::instance()->get_resort_meta($week->theresort->ResortID, [
        'resortFees',
        'ExchangeFeeAmount',
        'RentalFeeAmount',
        'CPOFeeAmount',
        'GuestFeeAmount',
        'UpgradeFeeAmount',
        'SameResortExchangeFee',
    ], ResortPath::BOOKING, $week->check_in_date->timestamp);
    $week->price = !empty($fees->RentalFeeAmount) ? (float) $fees->RentalFeeAmount : $week->price;

    // Load Promos
    $promo_id = $special ? $special->id : get_user_meta($cid, 'lppromoid', true);
    $promos = SpecialsRepository::instance()->get_promos_for_week($week);

    if ($promo_id) {
        Special::promo()
               ->active()
               ->current()
               ->where(fn($query) => $query
                   ->where('id', $promo_id)
                   ->orWhere('master', $promo_id)
               )
               ->each(fn(Special $promo) => $promos->prepend($promo));
    }
    $promos = $promos->unique('id')->sortBy('sort_order');
    $promos = $promos->filter(function (Special $promo) use ($type, $week, $is_exchange, $cid) {
        if ($promo->isResortExcluded($week->resort)) return false;
        if ($promo->isRegionExcluded($week->theresort->gpxRegionID)) return false;
        if ($promo->isHomeResortExcluded($week->theresort->ResortName, $cid)) return false;
        if ($promo->isExcludedByLeadTime($week->check_in_date)) return false;
        if ($promo->isExcludedByBookingDate()) return false;
        if ($promo->isExcludedByDae()) return false;
        if ($promo->isDateBlackedOut($week->check_in_date)) return false;
        if ($promo->isResortBlackedOut($week->resort, $week->check_in_date)) return false;
        if ($promo->isResortTravelDateBlackedOut($week->resort, $week->check_in_date)) return false;
        if (!$promo->isCustomerAllowedToUse($cid)) return false;
        if (!$promo->canBeUsedForResort($week->resort, $week->theresort->gpxRegionID)) return false;
        if (!$promo->canBeUsedForTransactionType($type)) return false;
        if ($promo->landingPageWasNotVisited($cid)) return false;

        return true;
    });
    $price = $is_exchange ? $exchangeFee : $week->price;
    $calculated = gpx_apply_promos($promos, $price);
    $applied = $calculated['applied'];
    $specialPrice = $calculated['special'];

    return [
        'price' => $price,
        'exchange' => $exchangeFee,
        'exchange_same_resort' => isset($fees->SameResortExchangeFee) ? (float) $fees->SameResortExchangeFee : 0.00,
        'extension' => (float) get_option('gpx_extension_fee'),
        'rental' => $week->price,
        'flex' => isset($fees->CPOFeeAmount) ? (float) $fees->CPOFeeAmount : (float) get_option('gpx_fb_fee') ?? 0.00,
        'guest' => isset($fees->GuestFeeAmount) ? (float) $fees->GuestFeeAmount : $guestFee,
        'upgrade' => isset($fees->UpgradeFeeAmount) ? (float) $fees->UpgradeFeeAmount : 0.00,
        'sevenDays' => (int) get_option('gpx_late_deposit_fee_within'),
        'fifteenDays' => (int) get_option('gpx_late_deposit_fee'),
        'tp_deposit' => (int) get_option('gpx_third_party_fee', 50),
        'discount' => $price - $specialPrice,
        'promo' => $applied->first()?->Name,
        'special' => $specialPrice,
        'promos' => $applied,
    ];
}

/**
 * @param Collection|array $promos
 * @param float $price
 *
 * @return array{applied: Collection<Special>, special: float}
 */
function gpx_apply_promos(Collection|array $promos, float $price): array {
    $specialPrice = $price;
    $stackedPrice = $price;

    $applied = collect();
    $stack = collect();
    foreach ($promos as $promo) {
        if ($promo->canStack()) {
            if ($promo->PromoType === 'Set Amt') {
                $stackedPrice = min($promo->Amount, $stackedPrice);
                $applied->add($promo);
            }
            if ($promo->PromoType === 'Dollar Off') {
                $stackedPrice = max(0.00, $stackedPrice - $promo->Amount);
                $applied->add($promo);
            }
            if ($promo->PromoType === 'Pct Off') {
                $stackedPrice = round($stackedPrice - ($stackedPrice * ($promo->Amount / 100)), 2);
                $applied->add($promo);
            }
        } else {
            if ($promo->PromoType === 'Set Amt') {
                $calc = min($promo->Amount, $price);
                if ($calc < $specialPrice) {
                    $specialPrice = $calc;
                    $stack = collect($promo);
                }
            }
            if ($promo->PromoType === 'Dollar Off') {
                $calc = max(0.00, $price - $promo->Amount);
                if ($calc < $specialPrice) {
                    $specialPrice = $calc;
                    $stack = collect($promo);
                }
            }
            if ($promo->PromoType === 'Pct Off') {
                $calc = round($price - ($price * ($promo->Amount / 100)), 2);
                if ($calc < $specialPrice) {
                    $specialPrice = $calc;
                    $stack = collect($promo);
                }
            }
        }
    }
    if ($specialPrice < $stackedPrice) {
        // the total calculated stacked price is less than the lowest non-stacked price
        //$specialPrice = $stackedPrice;
        $applied = $stack;
    } else {
        $specialPrice = $stackedPrice;
    }

    return [
        'applied' => $applied,
        'special' => $specialPrice,
    ];
}

function gpx_get_exchange_fee(int $cid = null, int|Week $week = null): int {
    $legacy = gpx_is_legacy_preferred_member($cid);
    if ($week) {
        $week = $week instanceof Week ? $week : Week::with(['theresort'])->find($week);
        $week->loadMissing(['theresort']);
        $fees = ResortRepository::instance()->get_resort_meta($week->theresort->ResortID, ['ExchangeFeeAmount',], 'booking', $week->check_in_date->timestamp);
        $fee = !empty($fees->ExchangeFeeAmount) ? (int) $fees->ExchangeFeeAmount : (int) get_option('gpx_exchange_fee', 0);
    } else {
        $fee = (int) get_option('gpx_exchange_fee', 0);
    }

    return $legacy ? (int) get_option('gpx_legacy_owner_exchange_fee', $fee) : $fee;
}

function gpx_checkout_add_to_cart(): void {
    $cart = match (gpx_request('type')) {
        'ExchangeWeek', 'Exchange Week', 'exchange' => gpx_add_exchange_to_cart(),
        'RentalWeek', 'Rental Week', 'rental' => gpx_add_rental_to_cart(),
        'deposit', 'Deposit Week', 'DepositWeek' => gpx_add_deposit_to_cart(),
        'extend', 'extension', 'Extend Week', 'ExtendWeek' => gpx_add_extension_to_cart(),
        default => (fn() => wp_send_json([
            'success' => false,
            'message' => 'Unknown item type. Must be exchange, deposit, rental, or extension.',
            'errors' => [
                'type' => 'Unknown item type. Must be exchange, deposit, rental, or extension.',
            ],
        ]))(),
    };

    wp_send_json([
        'success' => true,
        'cartID' => $cart->cartid,
        'cart' => $cart->toArray(),
        'redirect' => site_url('booking-path-payment'),
    ]);
}

add_action('wp_ajax_gpx_checkout_add_to_cart', 'gpx_checkout_add_to_cart');
add_action('wp_ajax_nopriv_gpx_checkout_add_to_cart', 'gpx_checkout_add_to_cart');

function gpx_add_exchange_to_cart(ShoppingCart $cart = null): ShoppingCart {
    $cart = $cart ?? gpx_create_cart();
    /** @var GuestForm $form */
    $form = gpx(GuestForm::class);
    $values = $form->validate();
    $values['guest']['has_guest'] = true;
    if (!gpx_is_agent()) {
        // must be an agent to waive fees
        $values['deposit']['waive_late_fee'] = false;
        $values['deposit']['waive_tp_fee'] = false;
        $values['deposit']['waive_tp_date'] = false;
    }

    $item = new ExchangeWeek($cart->cid, $values['week']);
    $item->setGuestInfo($values['guest']);
    $item->setExchangeInfo($values['deposit']);
    if ($item->week->check_in_date->clone()->endOfDay()->subDays(45)->isFuture()) {
        $item->setFlex(true);
    } else {
        $item->setFlex(false);
    }
    $cart->setItem($item);

    if (!gpx_is_week_available($item->week, true, $cart->cid)) {
        wp_send_json([
            'success' => false,
            'message' => 'Week is not available.',
            'redirect' => site_url('/'),
        ], 404);
    }

    if ($item->isCredit()) {
        if (!$item->hasCredit()) {
            wp_send_json([
                'success' => false,
                'message' => 'Submitted data was invalid.',
                'errors' => [
                    'deposit.credit' => ['Requested credit was invalid.'],
                ],
            ], 422);
        }
    }

    if ($item->getExchangeInfo()->isDeposit()) {
        if (!$item->hasInterval()) {
            wp_send_json([
                'success' => false,
                'message' => 'Submitted data was invalid.',
                'errors' => [
                    'deposit.deposit' => ['Requested deposit was invalid.'],
                ],
            ], 422);
        }
        if (!$item->getExchangeInfo()->date) {
            wp_send_json([
                'success' => false,
                'message' => 'Submitted data was invalid.',
                'errors' => [
                    'deposit.date' => ['You must enter a check in date.'],
                ],
            ], 422);
        }

        if (!$item->getInterval()->gpr && !$item->getExchangeInfo()->reservation) {
            wp_send_json([
                'success' => false,
                'message' => 'Submitted data was invalid.',
                'errors' => [
                    'deposit.reservation' => ['Reservation number is required.'],
                ],
            ], 422);
        }

        if ($item->getInterval()->third_party_deposit_fee_enabled) {
            $settings = gpx_get_third_party_fee_settings();
            $date = $cart->item->exchange->getDate();
            if ($date->subDays($settings['days'])->isPast() && !$values['deposit']['waive_tp_date']) {
                // only an agent can waive the third party dates
                $message = sprintf('Cannot deposit a third party week less than %d days from check in.', $settings['days']);
                wp_send_json([
                    'success' => false,
                    'message' => $message,
                    'show_fee' => true,
                    'errors' => [
                        'waive_tp_date' => [$message],
                    ],
                ], 422);
            }
        }

    }

    gpx_save_cart($cart);

    return $cart;
}

function gpx_add_rental_to_cart(ShoppingCart $cart = null): ShoppingCart {
    $cart = $cart ?? gpx_create_cart();

    /** @var GuestForm $form */
    $form = gpx(GuestForm::class);
    $values = $form->validate();
    $values['guest']['has_guest'] = true;

    $item = new RentalWeek($cart->cid, $values['week']);
    $item->setGuestInfo($values['guest']);
    $cart->setItem($item);

    if (!gpx_is_week_available($item->week, true, $cart->cid)) {
        wp_send_json([
            'success' => false,
            'message' => 'Week is not available.',
            'redirect' => site_url('/'),
        ], 404);
    }

    gpx_save_cart($cart);

    return $cart;
}

function gpx_add_deposit_to_cart(): void {
    $cart = gpx_create_cart();

    if (!is_user_logged_in()) {
        wp_send_json(['success' => false, 'message' => 'Must be logged in'], 403);
    }

    /** @var DepositWeekForm $form */
    $form = gpx(DepositWeekForm::class);
    $values = $form->validate();

    $cid = gpx_get_switch_user_cookie();
    $ownership = IntervalRepository::instance()->get_member_interval($cid, $values['id']);
    if (!$ownership) {
        wp_send_json(['success' => false, 'message' => 'Ownership not found'], 404);
    }
    if (!$ownership->gpr && empty($values['reservation_number'])) {
        wp_send_json(['success' => false, 'message' => 'Reservation number is required'], 422);
    }
    if (!gpx_is_agent() && Carbon::parse($values['checkin'])->endOfDay()->isPast()) {
        wp_send_json(['success' => false, 'message' => 'Checkin date must be in the future'], 422);
    }

    $item = new DepositWeek($cart->cid, $ownership);
    $item->setDeposit($values);
    $cart->setItem($item);

    if (!$cart->isAgent()) {
        if ($cart->getTotals()->late > 0 && !$cart->item->deposit->fee) {
            // only an agent can waive the late fee
            wp_send_json([
                'success' => false,
                'message' => 'A late fee is required.',
                'show_fee' => true,
                'fee' => $cart->getTotals()->late,
                'errors' => [
                    'waive_late_fee' => ['A late fee is required.'],
                ],
            ], 422);
        }

        if ($ownership->third_party_deposit_fee_enabled) {
            if ($cart->getTotals()->third_party > 0 && $cart->item->deposit->waive_tp_fee) {
                // only an agent can waive the third party fee
                wp_send_json([
                    'success' => false,
                    'message' => 'A third party fee is required.',
                    'show_fee' => true,
                    'fee' => $cart->getTotals()->third_party,
                    'errors' => [
                        'waive_tp_fee' => ['A third party deposit fee is required.'],
                    ],
                ], 422);
            }

            $settings = gpx_get_third_party_fee_settings();
            $checkin = Carbon::parse($cart->item->deposit->checkin)->startOfDay();
            if ($checkin->subDays($settings['days'])->isPast() && $values['waive_tp_date']) {
                // only an agent can waive the third party dates
                $message = sprintf('Cannot deposit a third party week less than %d days from check in.', $settings['days']);
                wp_send_json([
                    'success' => false,
                    'message' => $message,
                    'show_fee' => true,
                    'fee' => $cart->getTotals()->third_party,
                    'errors' => [
                        'waive_tp_date' => [$message],
                    ],
                ], 422);
            }
        }
    }


    gpx_save_cart($cart);

    if ($cart->getTotals()->total <= 0 || ($cart->isAgent() && !$cart->item->deposit->fee)) {
        // There is no fee or fee was waved by agent, deposit the week now
        $credit = gpx_deposit_week($cart);
        Cart::where('cartID', '=', $cart->cartid)->delete();
        wp_send_json([
            'success' => true,
            'message' => 'Your week has been banked. Please allow 48-72 hours for our system to verify the transaction.',
        ]);
    }

    wp_send_json([
        'success' => true,
        'message' => 'Added to cart.',
        'redirect' => site_url('booking-path-payment'),
    ]);
}

add_action('wp_ajax_gpx_add_deposit_to_cart', 'gpx_add_deposit_to_cart');

function gpx_add_extension_to_cart(): void {

    if (!is_user_logged_in()) {
        wp_send_json(['success' => false, 'message' => 'Must be logged in'], 403);
    }

    $cart = gpx_create_cart();

    $values = [
        'credit' => gpx_request('credit'),
    ];
    if (!$values['credit']) {
        wp_send_json(['success' => false, 'message' => 'No Credit provided'], 422);
    }

    $credit = Credit::forUser($cart->cid)->approved()->hasExpiration()->find($values['credit']);
    if (!$credit) {
        wp_send_json(['success' => false, 'message' => 'Credit not found'], 422);
    }
    $expires = $credit->credit_expiration_date->endOfDay();
    // new expiration date will be after current expiration date
    $date = $expires->clone()->addYear();

    $item = new ExtendWeek($cart->cid, $credit);
    $item->setExtensionDate($date->format('Y-m-d'));
    $cart->setItem($item);

    gpx_save_cart($cart);

    wp_send_json([
        'success' => true,
        'message' => 'Added to cart.',
        'redirect' => site_url('booking-path-payment'),
    ]);
}

add_action("wp_ajax_gpx_add_extension_to_cart", "gpx_add_extension_to_cart");


function gpx_i4go_auth(): void {
    $shift4 = new Shiftfour();
    try {
        $i4go = $shift4->i_four_go_auth();
    } catch (InvalidJsonResponse $e) {
        gpx_logger()->error("Shift4 API returned a response with invalid JSON", [
            'error' => $e->getPrevious()->getMessage(),
            'response' => $e->response(),
            'exception' => $e,
        ]);

        wp_send_json([
            'success' => false,
            'message' => 'Cannot process payment right now, try again later.',
        ], 500);
    }
    if (empty($i4go['i4go_response']) || $i4go['i4go_response'] !== 'SUCCESS') {
        wp_send_json([
            'success' => false,
            'message' => 'Cannot process payment right now, try again later.',
        ], 500);
    }

    wp_send_json([
        'success' => true,
        'access' => $i4go,
    ], 201);
}

add_action('wp_ajax_gpx_i4go_auth', 'gpx_i4go_auth');
add_action('wp_ajax_nopriv_gpx_i4go_auth', 'gpx_i4go_auth');

function gpx_i4go_process(): void {

    if (get_option('gpx_booking_disabled_active') && !gpx_is_administrator()) {
        wp_send_json([
            'success' => false,
            'message' => get_option('gpx_booking_disabled_msg'),
            'redirect' => site_url('/'),
        ]);
    }

    $cart = gpx_get_cart();
    if ($cart->cartid != gpx_request('cart')) {
        wp_send_json([
            'success' => false,
            'message' => 'Shopping cart has expired.',
            'reason' => 'Passed cartid does not match.',
            'redirect' => $cart->weekid ? site_url('booking-path') . "?book={$cart->weekid}&type={$cart->getCartType()}&step=review" : site_url('/'),
        ], 422);
    }

    $totals = $cart->getTotals();
    if ($totals->total != gpx_request('amount')) {
        wp_send_json([
            'success' => false,
            'message' => 'Shopping cart has expired.',
            'reason' => sprintf('Passed cart total of %s does not match calculated value of %s.', gpx_currency(gpx_request('amount')), gpx_currency($totals->total)),
            'redirect' => $cart->weekid ? site_url('booking-path') . "?book={$cart->weekid}&type={$cart->getCartType()}&step=review" : site_url('/'),
        ], 422);
    }

    if ($cart->isBooking() && !gpx_is_week_available($cart->week, true, $cart->cid)) {
        wp_send_json([
            'success' => false,
            'message' => 'Week is no longer available.',
            'redirect' => site_url('/'),
        ], 422);
    }

    /** @var PaymentForm $form */
    $form = gpx(BillingAddressForm::class);
    if ($totals->total > 0) {
        $address = $form->validate(gpx_request('billing', []));
    } else {
        $address = $form->filter(gpx_request('billing', []));
    }

    $message = $cart->validateForCheckout();
    if ($message) {
        wp_send_json([
            'success' => false,
            'message' => $message,
            'redirect' => $cart->weekid ? site_url('booking-path') . "?book={$cart->weekid}&type={$cart->getCartType()}&step=review" : site_url('/'),
        ], 422);
    }

    $response = null;
    if ($totals->total > 0) {
        $form = gpx(PaymentForm::class);
        $billing = gpx_request('payment', []);
        $form->validate($billing);
        $response = gpx_shif4_make_payment($cart, $billing);
    }

    $transaction = gpx_save_transaction($cart, $response);

    wp_send_json([
        'success' => true,
        'message' => 'Transaction processed.',
        'redirect' => site_url('/booking-path-confirmation/') . '?' . http_build_query(['confirmation' => $cart->cartid]),
    ]);
}

add_action('wp_ajax_gpx_i4go_process', 'gpx_i4go_process');
add_action('wp_ajax_nopriv_gpx_i4go_process', 'gpx_i4go_process');

function gpx_save_transaction(ShoppingCart $cart, PaymentResponse $response = null): Transaction {
    $sf = Salesforce::getInstance();
    $usermeta = UserMeta::load($cart->cid);
    $partner = Partner::where('user_id', $cart->cid)->first();
    $totals = $cart->getTotals();
    $deposit = null;
    $transaction = new Transaction([
        'cartID' => $cart->cartid,
        'sessionID' => $usermeta->searchSessionID,
        'userID' => $cart->cid,
        'paymentGatewayID' => $cart->getPayment()?->id,
        'returnTime' => $response?->duration(),
    ]);

    if ($cart->isBooking()) {

        // Release any holds on week (for all users as the week is now booked)
        PreHold::forWeek($cart->weekid)->update(['released' => true]);

        $credit = $cart->item->getCredit();
        if ($cart->isExchange()) {
            if ($cart->item->isInterval()) {
                // this is a deposit on exchange
                // a new credit needs to be created
                $exchange = $cart->item->getExchangeInfo();
                $interval = $cart->item->getInterval();

                $credit = Credit::create([
                    'created_date' => Carbon::now(),
                    'deposit_year' => date('Y', strtotime($exchange->date)),
                    'resort_name' => $interval->ResortName,
                    'check_in_date' => $exchange->date,
                    'owner_id' => $cart->cid,
                    'interval_number' => $interval->Contract_ID__c,
                    'unit_type' => $exchange->unit_type,
                    'status' => 'DOE',
                    'coupon' => $cart->promo,
                    'reservation_number' => $exchange->reservation,
                ]);

                $deposit = DepositOnExchange::create([
                    'creditID' => $credit->id,
                    'data' => [
                        'created_date' => Carbon::now()->format('Y-m-d H:i:s'),
                        'OwnershipID' => $interval->ResortName,
                        'resort_name' => $interval->ResortName,
                        'GPX_Resort__c' => $interval->resortID,
                        'Resort_Name__c' => $interval->ResortName,
                        'Check_In_Date__c' => $exchange->date,
                        'check_in_date' => $exchange->date,
                        'unit_type' => $exchange->unit_type,
                        'Resort_Unit_Week__c' => $interval->unitweek,
                        'Reservation__c' => $exchange->reservation ?? '',
                        'unitweek' => $interval->unitweek,
                        'Delinquent__c' => $interval->Delinquent__c,
                        'GPX_Deposit_ID__c' => $credit->id,
                        'Contract_ID__c' => $interval->Contract_ID__c,
                        'contractID' => $interval->Contract_ID__c,
                        'interval_number' => $interval->Contract_ID__c,
                        'RIOD_Key_Full' => $interval->RIOD_Key_Full,
                        'Account_Name__c' => $interval->Property_Owner__c ?? '',
                        'resortID' => $interval->resortID,
                        'creditID' => $credit->id,
                        'cid' => $cart->cid,
                        'pid' => $cart->weekid,
                        'cartID' => $cart->cartid,
                        'userID' => $cart->cid,
                        'owner_id' => $cart->cid,
                        'Room_Type__c' => $interval->Room_Type__c,
                        'status' => 'DOE',
                        'deposit_year' => date('Y', strtotime($exchange->date)),
                    ],
                ]);
                $cart->item->setDepositOnExchange($credit, $deposit);

                $sfFields = new SObject();
                $sfFields->type = 'GPX_Deposit__c';
                $sfFields->fields = [
                    'GPX_Deposit_ID__c' => $credit->id,
                    'Check_In_Date__c' => $exchange->date,
                    'Deposit_Year__c' => date('Y', strtotime($exchange->date)),
                    'Account_Name__c' => $interval->Property_Owner__c ?? '',
                    'GPX_Member__c' => $cart->cid,
                    'Deposit_Date__c' => date('Y-m-d'),
                    'Resort__c' => $interval->gprID,
                    'Resort_Name__c' => $interval->ResortName,
                    'Resort_Unit_Week__c' => $interval->unitweek,
                    'Unit_Type__c' => $exchange->unit_type ?: $interval->Room_Type__c,
                    'Member_Email__c' => $usermeta->getEmailAddress(),
                    'Member_First_Name__c' => $usermeta->getFirstName(),
                    'Member_Last_Name__c' => $usermeta->getLastName(),
                ];
                $sfDeposit = $sf->gpxUpsert('GPX_Deposit_ID__c', [$sfFields]);
                if (isset($sfDeposit[0]->id)) {
                    $credit->record_id = $sfDeposit[0]->id;
                    $tsData['GPX_Deposit__c'] = $sfDeposit[0]->id;
                } else {
                    // failed to create deposit in Salesforce
                    gpx_logger()->error('Failed to add deposit to Salesforce', [
                        'result' => $sfDeposit,
                        'data' => $sfFields->fields,
                        'credit' => $credit,
                    ]);
                }
            }

            if ($credit) {
                $credit->fill(['credit_used' => min(1, $credit->credit_used + 1)]);
                $credit->save();

                $sfFields = new SObject();
                $sfFields->type = 'GPX_Deposit__c';
                $sfFields->fields = [
                    'GPX_Deposit_ID__c' => $credit->id,
                    'Credits_Used__c' => $credit->credit_used,
                ];
                $sf->gpxUpsert('GPX_Deposit_ID__c', [$sfFields]);
            }
        }

        if ($cart->isExtend() && $credit) {
            $sfFields = new SObject();
            $sfFields->type = 'GPX_Deposit__c';
            $sfFields->fields = [
                'GPX_Deposit_ID__c' => $credit->id,
                'Credit_Extension_Date__c' => date('Y-m-d'),
                'Expiration_Date__c' => date('Y-m-d', strtotime($cart->item->getExtensionDate())),
            ];
            $sf->gpxUpsert('GPX_Deposit_ID__c', [$sfFields]);
        }

        $transaction->fill([
            'transactionType' => 'booking',
            'resortID' => $cart->week->theresort->ResortID,
            'weekId' => $cart->weekid,
            'check_in_date' => $cart->week->check_in_date->format('Y-m-d'),
            'depositID' => $credit?->id,
        ]);
    }

    $tsData = [
        'DAEMemberNo' => $cart->cid,
        'MemberNumber' => $cart->cid,
        'MemberName' => $partner?->name ?? $usermeta->getName(),
        'Email' => $partner?->email ?? $usermeta->getEmailAddress(),
        'Phone' => $partner?->phone ?? $usermeta->getPhone(),
        'PaymentID' => $cart->getPayment()?->id,
        'Paid' => $totals->total,
        'processedBy' => get_current_user_id(),
    ];
    if ($cart->isExtend()) {
        $transaction->fill([
            'transactionType' => 'extension',
            'weekId' => null,
            'resortID' => null,
        ]);
        $tsData = array_merge($tsData, [
            "id" => $cart->item->credit->id,
            "newdate" => date('m/d/Y', strtotime($cart->item()->getExtensionDate())),
            "interval" => $cart->item()->getCredit()->unitinterval,
            "fee" => $totals->extension,
            "actextensionFee" => $totals->extension,
        ]);
    }
    if ($cart->isDeposit()) {
        $deposit = $cart->item()->getDeposit();
        $ownership = $cart->item()->getOwnership();
        $transaction->fill([
            'transactionType' => 'deposit',
            'weekId' => null,
            'resortID' => null,
        ]);
        $tsData = array_merge($tsData, [
            'OwnershipID' => $ownership?->ownership_id,
            'Unit_Type__c' => $deposit->unit_type ?? $ownership?->Room_Type__c,
            'Check_In_Date__c' => $deposit->checkin,
            'Contract_ID__c' => $ownership?->contractID,
            'Usage__c' => $ownership?->Usage__c,
            'Account_Name__c' => $ownership?->Property_Owner__c ?? '',
            'GPX_Member__c' => $cart->cid,
            'GPX_Resort__c' => $ownership?->resortID,
            'Resort_Name__c' => $ownership?->ResortName,
            'Resort_Unit_Week__c' => $ownership?->unitweek,
            'Reservation__c' => $deposit->reservation_number,
            'twofer' => '',
            'lateDepositFee' => $totals->late,
            'thirdPartyDepositFee' => $totals->third_party,
            'coupon' => $deposit->coupon ?? $cart->promo ?? '',
        ]);
    }
    if ($cart->isGuestFee()) {
        $transaction->fill([
            'transactionType' => 'guest',
            'resortID' => $cart->week?->theresort?->ResortID,
            'parent_id' => $cart->item->getTransactionID(),
            'weekId' => $cart->weekid,
            'check_in_date' => $cart->week?->check_in_date?->format('Y-m-d'),
        ]);
    }
    if ($cart->isExchange()) {
        $tsData['creditweekID'] = $credit?->id;
        if ($cart->item()->getExchangeDeposit()) {
            $tsData['ExchangeDepositID'] = $cart->item()->getExchangeDeposit()?->id;
        }
    }
    if ($cart->isBooking()) {
        $tsData = array_merge($tsData, [
            'GuestName' => $cart->guest->getName(),
            'GuestFirstName' => $cart->guest->first_name,
            'GuestLastName' => $cart->guest->last_name,
            'GuestEmail' => $cart->guest->email,
            'GuestPhone' => $cart->guest->phone,
            'Adults' => $cart->guest->adults,
            'Children' => $cart->guest->children,
            'UpgradeFee' => $totals->upgrade,
            'CPO' => match (true) {
                $totals->flex > 0 => 'Taken',
                $cart->item->getFlexFee(true) > 0 => 'NotTaken',
                default => 'NotApplicable'
            },
            'CPOFee' => $totals->flex,
            'WeekType' => ucfirst(str_replace("week", "", mb_strtolower($cart->type))),
            'resortName' => $cart->week->theresort->ResortName,
            'ResortName' => $cart->week->theresort->ResortName,
            'WeekPrice' => $totals->price,
            'Balance' => 0,
            'ResortID' => $cart->week->resort,
            'sleeps' => $cart->week->unit?->sleeps_total,
            'bedrooms' => $cart->week->unit?->number_of_bedrooms,
            'Size' => $cart->week->unit?->name,
            'noNights' => $cart->week->no_nights,
            'checkIn' => $cart->week->check_in_date->format('Y-m-d'),
            'specialRequest' => $cart->guest->special_request,
            'actWeekPrice' => $totals->price,
            'actcpoFee' => $totals->flex,
            'actextensionFee' => $totals->extension,
            'actguestFee' => $totals->guest,
            'actupgradeFee' => $totals->upgrade,
            'acttax' => $totals->tax,
            'HasGuestFee' => $cart->item->guest->fee,
        ]);
    }

    if ($totals->late > 0) {
        $tsData['lateDepositFee'] = $totals->late;
        $tsData['actlatedepositFee'] = $totals->late;
    }
    if ($totals->third_party > 0) {
        $tsData['thirdPartyDepositFee'] = $totals->third_party;
        $tsData['actthirdpartydepositFee'] = $totals->third_party;
    }
    if ($totals->guest > 0) {
        $tsData['GuestFeeAmount'] = $totals->guest;
    }
    if ($totals->extension > 0) {
        $tsData['creditextensionfee'] = $totals->extension;
    }
    if ($totals->tax > 0) {
        $tsData['taxCharged'] = $totals->tax;
        $tsData['acttax'] = $totals->tax;
    }
    if ($totals->discount > 0) {
        $tsData['discount'] = $totals->discount;
    }
    if ($totals->coupon > 0) {
        $tsData['couponDiscount'] = $totals->coupon;
    }
    if ($cart->promo) {
        $tsData['promoName'] = $cart->promo;
    }
    if ($cart->hasCoupon()) {
        $tsData['coupon'] = $cart->coupons->first()?->code;
        $tsData['GPX_Coupon_Code__c'] = $cart->coupons->pluck('Name')->implode(',');
    }
    if ($cart->hasOwnerCreditCoupon()) {
        $tsData['ownerCreditCouponID'] = $cart->occoupons->pluck('id')->join(',');
        $tsData['ownerCreditCouponAmount'] = $cart->totals->occredit;
        $tsData['GPX_Coupon_Code__c'] = $cart->coupons->pluck('Name')->add('Monetary Coupon')->implode(',');
    }
    if ($cart->isGuestFee()) {
        $tsData['transactionID'] = $cart->item->getTransactionID();
        $tsData['GuestName'] = $cart->guest->getName();
        $tsData['GuestFirstName'] = $cart->guest->getName();
        $tsData['FirstName1'] = $cart->guest->first_name;
        $tsData['GuestLastName'] = $cart->guest->last_name;
        $tsData['LastName1'] = $cart->guest->last_name;
        $tsData['GuestEmail'] = $cart->guest->email;
        $tsData['Email'] = $cart->guest->email;
        $tsData['GuestPhone'] = $cart->guest->phone;
        $tsData['Adults'] = $cart->guest->adults;
        $tsData['Children'] = $cart->guest->children;
        $tsData['fee'] = $cart->item->getGuestFee();
        $tsData['actguestFee'] = $cart->item->getGuestFee();
        $tsData['HasGuestFee'] = true;
    }

    if ($cart->isDeposit()) {
        // do the deposit
        $credit = gpx_deposit_week($cart);
        $transaction->depositID = $credit->id;
        $tsData['creditid'] = $credit->id;
        $tsData['DepositId'] = $credit->id;
        $tsData['GPX_Deposit__c'] = $credit->record_id;
    }

    $transaction->fill([
        'data' => $tsData,
        'transactionData' => $tsData,
    ]);
    $transaction->save();
    $cart->getPayment()?->update(['transactionID' => $transaction->id]);


    if ($cart->isBooking()) {
        // Disable the week as it is now booked
        $cart->week->update(['active' => false]);
        if ($deposit) {
            $deposit->update(['transactionID' => $transaction->id]);
        }
    }
    if ($cart->isExtend()) {
        $credit = gpx_extend_credit($cart);
    }

    if ($cart->isGuestFee()) {
        // update the parent transaction
        $parent = Transaction::find($cart->item->getTransactionID());
        if ($parent) {
            gpx_dispatch(new UpdateGuestInfo($parent, [
                'first_name' => $cart->guest->first_name,
                'last_name' => $cart->guest->last_name,
                'email' => $cart->guest->email,
                'phone' => $cart->guest->phone,
                'adults' => $cart->guest->adults,
                'children' => $cart->guest->children,
                'owner' => $cart->guest->owner,
                'HasGuestFee' => true,
            ]));
        }
    }

    if ($partner) {
        //debit the partner
        $balance_id = DB::table('wp_partner_debit_balance')->insertGetId([
            'user' => $partner->user_id,
            'data' => json_encode([
                'cartID' => $transaction->cartID,
                'transactionType' => $transaction->transactionType,
                'userID' => $transaction->userID,
                'resortID' => $transaction->resortID,
                'weekId' => $transaction->weekId,
                'check_in_date' => $transaction->check_in_date->format('Y-m-d'),
                'depositID' => $transaction->depositID,
                'paymentGatewayID' => $transaction->paymentGatewayID,
                'transactionData' => $transaction->transactionData,
                'returnTime' => $transaction->returnTime,
                'transactionID' => $transaction->id,
            ]),
            'amount' => $transaction->data['Paid'],
        ]);
        $debitID = $partner->debit_id ?? [];
        $debitID[] = $balance_id;

        $partner->debit_id = $debitID;
        $partner->debit_balance = $partner->debit_balance + $tsData['Paid'];
        $partner->save();
    }

    // send transaction to salesforce
    TransactionRepository::instance()->send_to_salesforce($transaction);

    if ($totals->tax) {
        TaxAudit::create([
            'transactionDate' => $transaction->datetime,
            'emsID' => $cart->cid,
            'resortID' => $cart->week->theresort->ResortID,
            'arrivalDate' => $cart->week->check_in_date->format('Y-m-d'),
            'unitType' => $cart->getWeekType(),
            'transactionType' => 'DAECompleteBooking',
            'baseAmount' => $totals->total,
            'taxAmount' => $totals->tax,
            'gpxTaxID' => $cart->week->theresort->taxID,
        ]);
    }

    if ($cart->hasCoupon()) {
        Special::whereIn('id', $cart->coupons->pluck('id'))->update(['redeemed' => DB::raw("redeemed + 1")]);
        DB::table('wp_redeemedCoupons')->insert($cart->coupons->map(fn($coupon) => [
            'userID' => $cart->cid,
            'specialID' => $coupon->id,
        ])->toArray());
        if ($cart->hasAutoCoupon()) {
            AutoCoupon::whereIn('id', '=', $cart->getAutoCoupons()->pluck('id'))
                      ->update(['used' => true, 'transaction_id' => $transaction->id]);
        }
    }
    if ($cart->hasOwnerCreditCoupon()) {
        $remaining = $cart->totals->occredit;
        foreach ($cart->occoupons as $occoupon) {
            if ($remaining <= 0) break;
            if (!$occoupon->hasBalance()) continue;
            $redeem = min($occoupon->calculateBalance(), $remaining);
            OwnerCreditCouponActivity::create([
                'couponID' => $occoupon->id,
                'activity' => 'transaction',
                'amount' => $redeem,
                'xref' => $transaction->id,
                'userID' => $cart->cid,
            ]);
            $remaining -= $redeem;
            if ($remaining <= 0) break;
        }
    }

    if ($cart->isBooking()) {
        // mark any matching custom requests as converted
        CustomRequest::notConverted()
                     ->where('userID', '=', $cart->cid)
                     ->matchedTo($cart->weekid)
                     ->update(['matchConverted' => $transaction->id]);
    }

    // Delete the cart record
    Cart::where('cartID', '=', $cart->cartid)->delete();

    return $transaction;
}

function gpx_save_cart(ShoppingCart $cart): ShoppingCart {
    $record = Cart::where('user', '=', $cart->cid)->firstOrNew();
    if ($record->weekId && $record->weekId !== $cart->weekid) {
        // the associated week changed, release the old week
        gpx_delete_user_week_hold($cart->cid, $record->weekId);
    }
    if ($record->cartID && $record->cartID !== $cart->cartid) {
        // if this cart belongs to another user delete the old one to prevent duplicate cart ids
        Cart::where('cartID', '=', $cart->cartid)->delete();
    }

    $record->cartID = $cart->cartid;
    $record->user = $cart->cid;
    $record->sessionID = get_user_meta($cart->cid, 'searchSessionID', true) ?: null;
    $record->propertyID = $cart->weekid;
    $record->weekId = $cart->weekid;
    $record->weekType = $cart->getWeekType();
    $record->datetime = Carbon::now();
    $record->data = $cart->getCartData();
    $record->save();

    $cart->setCartRecordId($record->id);

    return $cart;
}

function gpx_shif4_make_payment(ShoppingCart $cart, array $billing): PaymentResponse {
    $totals = $cart->getTotals();

    $payment = new Payment();
    $payment->cartID = $cart->cartid;
    $payment->userID = $cart->cid;
    $payment->i4go_accessblock = null;
    $payment->transactionID = null;
    $payment->i4go_responsetext = $billing['i4go_responsetext'] ?? null;
    $payment->i4go_cardtype = $billing['i4go_cardtype'] ?? null;
    $payment->i4go_response = $billing['i4go_response'] ?? null;
    $payment->i4go_responsecode = $billing['i4go_responsecode'] ?? null;
    $payment->i4go_object = $billing['otn'] ?? null;
    $payment->i4go_streetaddress = $billing['i4go_streetaddress'] ?? null;
    $payment->i4go_postalcode = $billing['i4go_postalcode'] ?? null;
    $payment->i4go_cardholdername = $billing['i4go_cardholdername'] ?? null;
    $payment->i4go_expirationmonth = $billing['i4go_expirationmonth'] ?? null;
    $payment->i4go_expirationyear = $billing['i4go_expirationyear'] ?? null;
    $payment->i4go_uniqueid = $billing['i4go_uniqueid'] ?? null;
    $payment->i4go_utoken = $billing['i4go_utoken'] ?? null;
    $payment->save();

    $cart->setPayment($payment);

    if ($payment->i4go_responsecode != 1) {
        wp_send_json([
            'success' => false,
            'message' => 'Invalid Credit Card',
        ], 422);
    }

    $shift4 = new Shiftfour();
    try {
        $response = $shift4->shift_sale(
            $payment->i4go_uniqueid,
            $totals->total,
            $totals->tax,
            $payment->id,
            $payment->userID
        );
    } catch (InvalidJsonResponse $e) {
        wp_send_json([
            'success' => false,
            'message' => 'Invalid Credit Card',
        ], 500);
    }

    if ($response->isError()) {
        if ($response->error()->isTimeout()) {
            $invoice = $shift4->shift_invoice($payment->id);
            if ($invoice->isError() && $invoice->error()->isInvoiceNotFound()) {
                $payment->update([
                    'i4go_responsetext' => $invoice->error()->message(),
                    'i4go_responsecode' => $invoice->error()->code(),
                ]);
                FailedTransactions::create([
                    'cartID' => $cart->cartid,
                    'userID' => $cart->cid,
                    'data' => $invoice->error()->toArray(),
                    'returnTime' => $invoice->duration(),
                ]);

                wp_send_json([
                    'success' => false,
                    'message' => 'Please try again later.',
                ], 500);
            }

            $payment->update([
                'i4go_responsetext' => $response->error()->message(),
                'i4go_responsecode' => $response->error()->code(),
            ]);

            FailedTransactions::create([
                'cartID' => $cart->cartid,
                'userID' => $cart->cid,
                'data' => $response->error()->toArray(),
                'returnTime' => $response->duration(),
            ]);

            wp_send_json([
                'success' => false,
                'message' => 'Please try again later.',
            ], 500);
        }

    }

    if (!$response->transaction()->isSuccessful()) {
        FailedTransactions::create([
            'cartID' => $cart->cartid,
            'userID' => $cart->cid,
            'data' => $response->toArray(),
            'returnTime' => $response->duration(),
        ]);

        wp_send_json([
            'success' => false,
            'message' => 'Your credit card could not be processed.',
        ], 500);
    }

    return $response;
}

function gpx_delete_cart(bool $quiet = false): void {
    $cart = gpx_get_cart();
    $week_id = $cart->weekid;
    $record = Cart::where('user', '=', $cart->cid)->first();
    if ($record) {
        if ($record->weekId !== $cart->weekid) {
            gpx_delete_user_week_hold($cart->cid, $week_id);
        }
        $record->delete();
    }

    if ($cart->weekid) {
        gpx_delete_user_week_hold($cart->cid, $cart->weekid);
    }
    if ($quiet) return;

    wp_send_json([
        'success' => true,
        'rr' => 'redirect',
        'redirect' => site_url('/'),
    ]);
}

add_action('wp_ajax_gpx_delete_cart', 'gpx_delete_cart');
add_action('wp_ajax_nopriv_gpx_delete_cart', 'gpx_delete_cart');

/**
 * @return array{days: int, fee: float}
 */
function gpx_get_third_party_fee_settings(): array {
    return [
        'days' => (int) get_option('gpx_third_party_fee_days', 60),
        'fee' => (float) get_option('gpx_third_party_fee', 50),
    ];

}

/**
 * @return array{days: int, extra_days: int, fee: float, extra_fee: float}
 */
function gpx_get_late_fee_settings(): array {
    return [
        'days' => (int) get_option('gpx_late_deposit_days', 14),
        'extra_days' => (int) get_option('gpx_late_deposit_fee_extra_days', 7),
        'fee' => (float) get_option('gpx_late_deposit_fee'),
        'extra_fee' => (float) get_option('gpx_late_deposit_fee_within'),
    ];

}

function gpx_calculate_late_fee(string|\DateTimeInterface $checkin = null): float {
    if (null === $checkin) {
        return 0.00;
    }
    $settings = gpx_get_late_fee_settings();
    $checkin = $checkin instanceof \DateTimeInterface ? Carbon::instance($checkin)->endOfDay() : Carbon::parse($checkin)->endOfDay();
    if ($checkin->clone()->subDays($settings['extra_days'])->isPast()) {
        return $settings['extra_fee'];
    }
    if ($checkin->clone()->subDays($settings['days'])->isPast()) {
        return $settings['fee'];
    }

    return 0.00;
}

function gpx_checkout_flex_fee(): void {
    $cart = gpx_get_cart();
    $fee = (bool) gpx_request('flex', $cart->flex);
    if ($cart->isExchange()) {
        $cart->item->setFlex($fee);
    }
    gpx_save_cart($cart);

    wp_send_json([
        'success' => true,
        'cart' => $cart->toArray(),
    ]);
}

add_action('wp_ajax_gpx_checkout_flex_fee', 'gpx_checkout_flex_fee');
add_action('wp_ajax_nopriv_gpx_checkout_flex_fee', 'gpx_checkout_flex_fee');

function gpx_delete_user_week_hold(int $cid, int $week_id): void {
    $hold = PreHold::where('user', '=', $cid)->where('weekId', '=', $week_id)->released(false)->first();
    if (!$hold) {
        return;
    }
    $week = Week::find($week_id);
    if (!$week) {
        return;
    }
    if (Transaction::forWeek($week_id)->cancelled(false)->doesntExist()) {
        if ($week->active_specific_date->isPast()) {
            $week->update(['active' => true]);
        }
    }

    $activeUser = get_userdata(get_current_user_id());
    $holdDets = $hold->data;
    $holdDets[time()] = [
        'action' => 'released',
        'by' => $activeUser->first_name . " " . $activeUser->last_name,
    ];
    $hold->update([
        'released' => true,
        'data' => $holdDets,
    ]);
}

function gpx_is_week_available(int|Week $week = null, bool $check_holds = false, int $cid = null): bool {
    if (!$week) return false;
    if (is_numeric($week)) {
        $week = Week::find($week);
    }
    // could not find the week
    if (!$week) return false;

    if ($week->check_in_date->endOfDay()->isPast()) {
        // the checkin date is on the past
        return false;
    }

    if (Transaction::forWeek($week->record_id)->cancelled(false)->exists()) {
        // the week was booked so set it to inactive
        if ($week->active) {
            $week->update(['active' => false]);
        }

        return false;
    }

    if ($check_holds) {
        return !gpx_is_week_on_hold($week->record_id, $cid);
    }

    return true;
}

function gpx_is_week_on_hold(int $week_id = null, int $cid = null): bool {
    if (!$week_id) return false;
    $hold = PreHold::where('weekId', '=', $week_id)->released(false)->first();
    if (!$hold) return false;

    return (!$cid || $hold->user !== $cid);
}

function gpx_cart_add_coupon(): void {
    $code = gpx_request('coupon');
    if (empty($code)) {
        wp_send_json([
            'success' => false,
            'message' => 'Please enter a coupon code.',
        ]);
    }
    $cart = gpx_get_cart();
    $cid = gpx_get_switch_user_cookie();

    //check if it is an auto create coupon
    $couponParts = preg_split("(-|\s+)", $code);
    $autoCouponHash = end($couponParts);
    $auto = AutoCoupon::forUser($cid)->whereHash($autoCouponHash)->used(false)->first();
    if ($auto) {
        /** @var Special $coupon */
        $coupon = Special::coupon()->active()->find($auto->coupon_id);
    } else {
        /** @var Special $coupon */
        $coupon = Special::coupon()->active()->code($code)->first();
    }
    if (!$coupon) {
        gpx_cart_add_owner_credit_coupon($cart, $code);

        return;
    }

    if ($coupon->isExpired()) {
        wp_send_json([
            'success' => false,
            'message' => 'This coupon has expired.',
        ]);
    }
    if (!$coupon->hasStarted()) {
        wp_send_json([
            'success' => false,
            'message' => 'This coupon is invalid.',
        ]);
    }
    if ($cart->hasCoupon($coupon->id)) {
        wp_send_json([
            'success' => false,
            'message' => 'This coupon has already been applied!',
        ]);
    }

    if ($coupon->isAlreadyRedeemed($cid)) {
        wp_send_json([
            'success' => false,
            'message' => 'You have already used this coupon!',
        ]);
    }

    // Check if the coupon is valid for this cart
    if (!$coupon->isValidForCart($cart)) {
        wp_send_json([
            'success' => false,
            'message' => "This coupon isn't available for this transaction.",
        ]);
    }

    $property_details = get_property_details($cart->weekid, $cid);
    if (!$coupon->canStack() && !empty($property_details['specialPrice'])) {
        wp_send_json([
            'success' => false,
            'message' => "A promotional price has been applied to your transaction.  This coupon is not allowed.",
        ]);
    }

    // add the coupon to the cart
    $cart->addCoupon($coupon, $auto);
    gpx_save_cart($cart);

    wp_send_json([
        'success' => true,
        'message' => 'Coupon added.',
        'cart' => $cart->toArray(),
    ]);
}

add_action("wp_ajax_gpx_cart_add_coupon", "gpx_cart_add_coupon");
add_action("wp_ajax_nopriv_gpx_cart_add_coupon", "gpx_cart_add_coupon");

function gpx_cart_remove_coupon(): void {
    $type = gpx_request('type', 'coupon');
    if ($type === 'owner') {
        gpx_cart_remove_owner_credit_coupon();

        return;
    }
    $cart = gpx_get_cart();

    $coupon_id = gpx_request('couponID');
    if ($coupon_id) {
        $cart->removeCoupon($coupon_id);
    } else {
        $cart->clearCoupons();
    }

    gpx_save_cart($cart);

    wp_send_json(['success' => true, 'message' => 'Coupon removed', 'cart' => $cart->toArray()]);
}

add_action("wp_ajax_gpx_cart_remove_coupon", "gpx_cart_remove_coupon");
add_action("wp_ajax_nopriv_gpx_cart_remove_coupon", "gpx_cart_remove_coupon");

function gpx_cart_add_owner_credit_coupon(ShoppingCart $cart, string $code): void {
    $coupon = OwnerCreditCoupon::query()
                               ->active()
                               ->withRedeemed()
                               ->withAmount()
                               ->byCode($code)
                               ->byOwner($cart->cid)
                               ->first();
    if (!$coupon) {
        wp_send_json([
            'success' => false,
            'message' => 'This coupon is invalid.',
        ], 422);
    }

    if (!$coupon->hasBalance()) {
        wp_send_json([
            'success' => false,
            'message' => 'This coupon is invalid.',
        ], 422);
    }

    // add the coupon to the cart
    $cart->addOwnerCreditCoupon($coupon);
    gpx_save_cart($cart);

    wp_send_json([
        'success' => true,
        'message' => 'Coupon added.',
        'cart' => $cart->toArray(),
    ]);
}

function gpx_cart_remove_owner_credit_coupon(): void {
    $cart = gpx_get_cart();
    if ($cart->hasOwnerCreditCoupon()) {
        $cart->clearOwnerCreditCoupons();
        gpx_save_cart($cart);
    }

    wp_send_json(['success' => true, 'message' => 'Coupon removed', 'cart' => $cart->toArray()]);
}

add_action("wp_ajax_gpx_cart_remove_owner_credit_coupon", "gpx_cart_remove_owner_credit_coupon");
add_action("wp_ajax_nopriv_gpx_cart_remove_owner_credit_coupon", "gpx_cart_remove_owner_credit_coupon");
