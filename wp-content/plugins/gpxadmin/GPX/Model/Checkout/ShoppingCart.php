<?php

namespace GPX\Model\Checkout;

use GPX\Model\Week;
use GPX\Model\Cart;
use Ramsey\Uuid\Uuid;
use GPX\Model\Credit;
use GPX\Model\PreHold;
use GPX\Model\Payment;
use GPX\Model\Special;
use GPX\Model\Interval;
use GPX\Model\AutoCoupon;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use GPX\Model\DepositOnExchange;
use GPX\Model\OwnerCreditCoupon;
use Illuminate\Support\Collection;
use GPX\Model\Checkout\Item\CartItem;
use GPX\Model\Checkout\Item\NullItem;
use GPX\Model\Checkout\Item\GuestFee;
use GPX\Model\Checkout\Item\RentalWeek;
use GPX\Model\Checkout\Item\ExtendWeek;
use GPX\Model\Checkout\Item\DepositWeek;
use GPX\Model\Checkout\Item\ExchangeWeek;

/**
 * @property-read string $cartid
 * @property-read int $cid
 * @property-read int $user
 * @property-read ?int $weekid
 * @property-read ?Week $week
 * @property-read string $type
 * @property-read ?Credit $credit
 * @property-read ?Interval $interval
 * @property-read CartItem $item
 * @property-read Exchange $exchange
 * @property-read Guest $guest
 * @property-read Collection<OwnerCreditCoupon> $occoupons
 * @property-read Collection<Special> $coupons
 * @property-read ?string $promo
 * @property-read bool $is_agent
 * @property-read Totals $totals
 * @property-read ?PreHold $hold
 * @property-read ?Credit $exchange_credit
 * @property-read ?DepositOnExchange $exchange_deposit
 */
class ShoppingCart {

    private string $cartid;
    private int $cid;
    private CartItem $item;
    private bool $is_agent = false;
    private ?PreHold $hold = null;
    private ?Payment $payment = null;
    private ?int $record_id = null;

    public function __construct(int $cid, string $cartid = null) {
        $cartid = $cartid ?? Uuid::uuid4()->toString();
        $this->cartid = $cartid;
        $this->cid = $cid;
        $this->item = new NullItem($cid);
    }

    public function setCartRecordId(int $record_id = null): static {
        $this->record_id = $record_id;

        return $this;
    }

    public function getCartRecordId(): ?int {
        return $this->record_id;
    }

    public function isSaved(): bool {
        return $this->record_id !== null;
    }

    public function getType(): ?string {
        return match (true) {
            $this->item instanceof ExchangeWeek => 'exchange',
            $this->item instanceof RentalWeek => 'rental',
            $this->item instanceof ExtendWeek => 'extend',
            $this->item instanceof DepositWeek => 'deposit',
            $this->item instanceof GuestFee => 'guest',
            default => null,
        };
    }

    /**
     * @return 'ExchangeWeek'|'RentalWeek'|'ExtendWeek'|'DepositWeek'|'GuestFee'|null
     */
    public function getWeekType(): ?string {
        return match (true) {
            $this->item instanceof ExchangeWeek => 'ExchangeWeek',
            $this->item instanceof RentalWeek => 'RentalWeek',
            $this->item instanceof ExtendWeek => 'ExtendWeek',
            $this->item instanceof DepositWeek => 'DepositWeek',
            $this->item instanceof GuestFee => 'GuestFee',
            default => null,
        };
    }

    public function item(): CartItem {
        return $this->item;
    }

    public function createItem(?string $type = 'ExchangeWeek', int $id = null): CartItem {
        if ($id) {
            return match ($type) {
                "exchange", "ExchangeWeek", "Exchange Week" => new ExchangeWeek($this->cid, $id),
                "rental", "RentalWeek", "Rental Week" => new RentalWeek($this->cid, $id),
                "extend", 'extension', "ExtendWeek", "Extend Week" => new ExtendWeek($this->cid, $id),
                "deposit", "DepositWeek", "Deposit Week", "late_deposit_fee" => new DepositWeek($this->cid, $id),
                "guest", "GuestFee", "Guest Fee" => new GuestFee($this->cid, $id),
                default => new NullItem($this->cid, $id),
            };
        } else {
            return new NullItem($this->cid);
        }
    }

    public function setItem(CartItem $item): static {
        $this->item = $item;
        $this->hold = null;
        $this->payment = null;

        return $this;
    }

    public function hasItem(): bool {
        return !($this->item instanceof NullItem);
    }

    public static function fromCart(Cart $cart): static {
        $instance = new static($cart->user, $cart->cartID);

        $data = $cart->data;
        $data['propertyID'] = $cart->propertyID;
        $data['weekId'] = $cart->weekId;
        $data['record'] = $cart->id;

        $instance->setData($data);

        return $instance;
    }

    public function setData(array $data = []): static {
        $type = $data['weekType'] ?? $data['type'];
        $record_id = $data['propertyID'] ?? $data['weekId'] ?? null;
        $this->setCartRecordId($data['record'] ?? $data['id'] ?? null);
        if (in_array($type, ["deposit", "DepositWeek", "Deposit Week", "late_deposit_fee"])) {
            $record_id = $data['OwnershipID'] ?? null;
        }
        if (in_array($type, ['extend', 'extension', 'Extend Week', 'ExtendWeek'])) {
            $record_id = $data['ExtendedCredit'] ?? null;
        }
        if (in_array($type, ["guest", "GuestFee", "Guest Fee"])) {
            $record_id = $data['transactionID'] ?? null;
        }
        $item = $this->createItem($type, $record_id);
        $this->setItem($item);

        if (array_key_exists('agent', $data)) {
            $this->setAgent($data['agent']);
        } elseif (array_key_exists('user_type', $data)) {
            $this->setAgent($data['user_type'] === 'Agent');
        } else {
            $this->setAgent($this->cid === get_current_user_id());
        }


        if (array_key_exists('guest', $data)) {
            $this->item->setGuestInfo($data['guest']);
        } else {
            $this->item->setGuestInfo([
                'has_guest' => false,
                'owner' => $this->cid,
                'adults' => $data['adults'] ?? 1,
                'children' => $data['children'] ?? 0,
                'email' => $data['email'] ?? null,
                'fee' => ((int) $data['GuestFeeAmount'] ?? 0) > 0,
                'first_name' => $data['FirstName1'] ?? null,
                'last_name' => $data['LastName1'] ?? null,
                'phone' => $data['phone'] ?? null,
                'special_request' => $data['SpecialRequest'] ?? null,
            ]);
        }

        if ($this->isExchange()) {
            if (array_key_exists('exchange', $data)) {
                if (!$data['exchange']['fee'] && !$this->isAgent()) {
                    // must be an agent to waive fee
                    $data['exchange']['fee'] = true;
                }
                $this->item->setExchangeInfo($data['exchange']);
            } else {
                $this->item->setExchangeInfo([
                    'type' => $data['creditweekid'] === 'deposit' ? 'deposit' : 'credit',
                    'deposit' => $data['creditweekid'] === 'deposit' ? $data['deposit'] : null,
                    'credit' => $data['creditweekid'] !== 'deposit' ? $data['creditweekid'] : null,
                    'fee' => !$this->isAgent() || ((float) $data['late_deposit_fee'] ?? 0) > 0,
                    'date' => null,
                    'reservation' => null,
                ]);
            }

            if (array_key_exists('exchange_credit', $data)) {
                $this->item->setDepositOnExchange($data['exchange_credit']);
            }
        }

        $this->item->setFlex($data['flex'] ?? true);

        if ($this->isDeposit()) {
            if (array_key_exists('DepositedWeek', $data)) {
                $this->item->setDeposit($data['DepositedWeek']);
            } else {
                $this->item->setDeposit([
                    'id' => $data['DepositID'],
                    'checkin' => $data['DepositCheckin'],
                    'fee' => true,
                    'reservation_number' => $data['DepositReservationNumber'],
                    'unit_type' => $data['DepositUnitType'],
                    'coupon' => $data['DepositCoupon'],
                ]);
            }
        }

        if ($this->isExtend()) {
            if (array_key_exists('ExtendedCredit', $data)) {
                $this->item->setCredit($data['ExtendedCredit']);
            }
            if (array_key_exists('ExtendedDate', $data)) {
                $this->item->setExtensionDate($data['ExtendedDate']);
            }
        }

        if (array_key_exists('coupon', $data)) {
            $this->setCoupons($data['coupon']);
        }
        if (array_key_exists('occoupon', $data)) {
            $this->setOwnerCreditCoupons($data['occoupon']);
        }
        if (array_key_exists('acHash', $data)) {
            $this->setAutoCoupons($data['acHash']);
        }

        return $this;
    }

    public function getCartData(): array {
        return [
            'user' => $this->cid,
            'type' => $this->getCartType(),
            'weekType' => $this->getCartType(),
            'propertyID' => $this->weekid,
            'weekId' => $this->weekid,
            'guest' => $this->item->getGuestInfo(),
            'exchange' => $this->item->getExchangeInfo(),
            'promoName' => $this->item->getPromo(),
            'discount' => $this->item->getDiscount(),
            'cartID' => $this->cartid,
            'CPOPrice' => $this->item->getFlexFee(),
            'flex' => $this->item->hasFlex(),
            'GuestFeeAmount' => $this->item->getGuestFee(),
            'HasGuestFee' => $this->item->guest->fee,
            'FirstName1' => $this->item->guest->first_name,
            'LastName1' => $this->item->guest->last_name,
            'email' => $this->item->guest->email,
            'phone' => $this->item->guest->phone,
            'adults' => $this->item->guest->adults,
            'children' => $this->item->guest->children,
            'SpecialRequest' => $this->item->guest->special_request,
            'credit' => $this->credit?->id,
            'exchange_credit' => $this->item->getExchangeCredit()?->id,
            'exchange_deposit' => $this->item->getExchangeDeposit()?->id,
            'creditweekid' => $this->isDeposit() ? 'deposit' : $this->credit->id,
            'creditvalue' => $this->item->getUpgradeFee(),
            'creditextensionfee' => $this->item->getExtensionFee(),
            'payment_id' => $this->payment?->id,
            'deposit' => $this->item->interval?->id,
            'agent' => $this->isAgent(),
            'user_type' => $this->isAgent() ? 'Agent' : 'Owner',
            'late_deposit_fee' => $this->item->getLateFee(),
            'coupon' => $this->getCoupons()->pluck('id')->toArray(),
            'acHash' => $this->getAutoCoupons()->pluck('coupon_hash')->toArray(),
            'occoupon' => $this->getOwnerCreditCoupons()->pluck('id')->toArray(),
            'hold' => $this->hold ? [
                'id' => $this->hold->id,
                'release_on' => $this->hold->release_on->format('Y-m-d H:i:s'),
            ] : null,
            "OwnershipID" => $this->item->getOwnership()?->id,
            "DepositedWeek" => $this->item->getDeposit()?->toArray(),
            "DepositID" => $this->item->getDeposit()?->id,
            "DepositCheckin" => $this->item->getDeposit()?->checkin,
            "DepositReservationNumber" => $this->item->getDeposit()?->reservation_number,
            "DepositUnitType" => $this->item->getDeposit()?->unit_type,
            "DepositCoupon" => $this->item->getDeposit()?->coupon,
            "ExtendedCredit" => $this->item->getCredit()?->id,
            "ExtendedDate" => $this->item->getExtensionDate(),
            "transactionID" => $this->item->getTransactionID(),
        ];
    }

    public function getCartType(string $type = null): ?string {
        $type = $type ?? $this->item->getType();

        return match ($type) {
            'Exchange Week', 'ExchangeWeek', 'exchange' => 'ExchangeWeek',
            'Rental Week', 'RentalWeek', 'rental' => 'RentalWeek',
            'extension', 'extend', 'ExtendWeek', 'Extend Week' => 'ExtendWeek',
            'late_deposit_fee', 'deposit', 'DepositWeek', 'Deposit Week' => 'DepositWeek',
            "guest", "GuestFee", "Guest Fee" => 'GuestFee',
            default => null,
        };
    }

    public function __get(string $name) {
        return match ($name) {
            "cartid" => $this->cartid,
            "cid", "user" => $this->cid,
            "weekid" => $this->item->getWeekId(),
            "propertyID" => $this->item->getWeekId(),
            "week" => $this->item->getWeek(),
            "type", "WeekType" => $this->item->getType(),
            "cart" => $this->cart,
            "payment" => $this->payment,
            "guest" => $this->item->getGuestInfo(),
            "exchange" => $this->item->getExchangeInfo(),
            "interval" => $this->item->getInterval(),
            "credit" => $this->item->getCredit(),
            "exchange_credit" => $this->item->getExchangeCredit(),
            "exchange_deposit" => $this->item->getExchangeDeposit(),
            "promo" => $this->item->getPromo(),
            'flex' => $this->item->hasFlex(),
            'flex_fee' => $this->item->getFlexFee(true),
            "totals" => $this->getTotals(),
            "hold" => $this->hold,
            "coupons" => $this->getCoupons(),
            "auto_coupons" => $this->getAutoCoupons(),
            "occoupons" => $this->getOwnerCreditCoupons(),
            "item" => $this->item,
            default => dd('invalid cart property', $name)
        };
    }

    public function toArray(): array {
        $credit = $this->item->getCredit();
        $interval = $this->item->getInterval();
        return [
            'cid' => $this->cid,
            'is_agent' => $this->isAgent(),
            'cartid' => $this->cartid,
            'weekid' => $this->item->getWeekId(),
            'type' => $this->item->getType(),
            'week' => $this->item->toArray(),
            'flex' => $this->item->hasFlex(),
            'guest' => $this->item->getGuestInfo()->toArray(),
            'promo' => $this->item->getPromo(),
            'exchange' => $this->item->getExchangeInfo()->toArray(),
            'credit' => $credit ? [
                'id' => $credit->id,
                'resort' => $credit->resort_name,
                'expires' => $credit->credit_expiration_date?->format('Y-m-d'),
                'year' => $credit->deposit_year,
                'size' => $credit->unit_type,
            ] : null,
            'interval' => $interval ? [
                'id' => $interval->id,
                'resort' => $interval->ResortName,
                'unit_type' => $interval->Room_Type__c,
                'week_type' => $interval->Week_Type__c,
                'contract' => $interval->Contract_ID__c,
                'year' => $interval->deposit_year,
                'third_party_deposit_fee_enabled' => $interval->third_party_deposit_fee_enabled,
            ] : null,
            'totals' => $this->item->getTotals(),
            'hold' => $this->hold?->release_on->format('Y-m-d H:i:s'),
        ];
    }

    public function setAgent(bool $is_agent): static {
        $this->is_agent = $is_agent;

        return $this;
    }

    public function isAgent(): bool {
        return $this->is_agent;
    }

    public function isExchange(): bool {
        return $this->item->isExchange();
    }

    public function isRental(): bool {
        return $this->item->isRental();
    }

    public function isBooking(): bool {
        return $this->item->isBooking();
    }

    public function isDeposit(): bool {
        return $this->item->isDeposit();
    }

    public function isExtend(): bool {
        return $this->item->isExtend();
    }

    public function isGuestFee(): bool {
        return $this->item->isGuestFee();
    }

    public function setHold(PreHold $hold = null): static {
        $this->hold = $hold;

        return $this;
    }

    public function getTotals(): Totals {
        return $this->item->getTotals();
    }

    public function validateForCheckout(): ?string {
        if ($this->isDeposit() && !$this->item->getDeposit()->id) {
            return 'No deposit selected';
        }
        if ($this->isBooking() && (!$this->weekid || !$this->week)) {
            return 'No week selected';
        }
        if (!$this->hasItem()) {
            return 'Week is no longer available.';
        }

        if ($this->isExchange()) {
            if (!$this->exchange['type']) {
                return 'Have not selected exchange credit or deposit';
            }
            if ($this->item->isInterval()) {
                if (!$this->exchange['deposit']) {
                    return 'Have not selected a deposit';
                }
                if (!$this->exchange['date'] || $this->exchange->getDate()->isPast()) {
                    return 'Deposited week cannot be in the past.';
                }
            }
            if ($this->item->isCredit() && !$this->exchange['credit']) {
                return 'Have not selected a credit';
            }
        }
        if ($this->isBooking() && !$this->guest['has_guest']) {
            return 'Have not provided guest info';
        }

        return null;
    }

    public function setPayment(Payment|int $payment): static {
        $payment = $payment instanceof Payment ? $payment : Payment::find($payment);
        $this->payment = $payment;

        return $this;
    }

    public function getPayment(): ?Payment {
        return $this->payment;
    }

    /**
     * @param (int|Special)[] $coupons
     *
     * @return Collection<Special>
     */
    public function setCoupons(array $coupons = []): Collection {
        $coupons = array_filter(array_map(function ($coupon) {
            if ($coupon instanceof Special) return $coupon->id;
            if (empty($coupon)) return null;
            if (is_numeric($coupon)) return (int) $coupon;

            return null;
        }, $coupons));

        Special::coupon()
               ->active()
               ->current()
               ->whereIn('id', $coupons)
               ->get()
               ->filter(fn(Special $coupon) => $coupon->isValidForCart($this))
               ->each(fn(Special $coupon) => $this->item->addCoupon($coupon));

        return $this->getCoupons();
    }

    public function setAutoCoupons(array|string $hash = []): Collection {
        $hash = Arr::wrap($hash);
        AutoCoupon::forUser($this->cid)
                  ->whereHash($hash)
                  ->used(false)
                  ->whereIn('coupon_id', $this->getCoupons()->pluck('id'))
                  ->get()
                  ->each(fn(AutoCoupon $coupon) => $this->item->addAutoCoupon($coupon));

        return $this->getAutoCoupons();
    }

    /**
     * @return Collection<Special>
     */
    public function getCoupons(): Collection {
        return $this->item->getCoupons();
    }

    /**
     * @return Collection<AutoCoupon>
     */
    public function getAutoCoupons(): Collection {
        return $this->item->getAutoCoupons();
    }

    public function hasCoupon(int $coupon_id = null): bool {
        if (null === $coupon_id) return $this->getCoupons()->isNotEmpty();

        return $this->getCoupons()->where('id', '==', $coupon_id)->isNotEmpty();
    }

    public function hasAutoCoupon(int $id = null): bool {
        if (null === $id) return $this->getAutoCoupons()->isNotEmpty();

        return $this->getAutoCoupons()->where('id', '==', $id)->isNotEmpty();
    }

    /**
     * @param Special $coupon
     * @param ?AutoCoupon $auto
     *
     * @return Collection<Special>
     */
    public function addCoupon(Special $coupon, AutoCoupon $auto = null): Collection {

        if ($this->hasCoupon($coupon->id)) {
            throw new InvalidArgumentException('Coupon already added');
        }

        $this->item->addCoupon($coupon, $auto);

        return $this->getCoupons();
    }

    /**
     * @param int|Special $coupon
     *
     * @return Collection<Special>
     */
    public function removeCoupon(int|Special $coupon): Collection {
        $coupon_id = $coupon instanceof Special ? $coupon->id : $coupon;
        $this->item->removeCoupon($coupon_id);

        return $this->getCoupons();
    }

    /**
     * @return Collection<Special>
     */
    public function clearCoupons(): Collection {
        $this->item->clearCoupons();

        return $this->getCoupons();
    }

    /**
     * @return Collection<OwnerCreditCoupon>
     */
    public function getOwnerCreditCoupons(): Collection {
        return $this->item->getOwnerCreditCoupons();
    }

    /**
     * @param (int|OwnerCreditCoupon)[] $coupons
     *
     * @return Collection<OwnerCreditCoupon>
     */
    public function setOwnerCreditCoupons(array $coupons = []): Collection {
        $coupons = array_filter(array_map(function ($coupon) {
            if ($coupon instanceof OwnerCreditCoupon) return $coupon->id;
            if (!empty($coupon) && is_numeric($coupon)) return (int) $coupon;

            return null;
        }, $coupons));
        OwnerCreditCoupon::query()
                         ->whereIn('wp_gpxOwnerCreditCoupon.id', $coupons)
                         ->byOwner($this->cid)
                         ->active()
                         ->withRedeemed()
                         ->withAmount()
                         ->get()
                         ->filter(fn(OwnerCreditCoupon $coupon) => $coupon->hasBalance())
                         ->each(fn(OwnerCreditCoupon $coupon) => $this->item->addOwnerCreditCoupon($coupon));

        return $this->getOwnerCreditCoupons();
    }

    public function hasOwnerCreditCoupon(int $coupon_id = null): bool {
        $coupons = $this->getOwnerCreditCoupons();
        if (null === $coupon_id) return $coupons->isNotEmpty();

        return $coupons->where('id', '==', $coupon_id)->isNotEmpty();
    }

    public function addOwnerCreditCoupon(OwnerCreditCoupon $coupon): Collection {
        if ($this->hasOwnerCreditCoupon($coupon->id)) {
            // already added
            return $this->getOwnerCreditCoupons();
        }
        $this->item->addOwnerCreditCoupon($coupon);

        return $this->getOwnerCreditCoupons();
    }

    public function removeOwnerCreditCoupon(int|OwnerCreditCoupon $coupon): Collection {
        $coupon_id = $coupon instanceof OwnerCreditCoupon ? $coupon->id : $coupon;
        $this->item->removeOwnerCreditCoupon($coupon_id);

        return $this->getOwnerCreditCoupons();
    }

    /**
     * @return Collection<OwnerCreditCoupon>
     */
    public function clearOwnerCreditCoupons(): Collection {
        $this->item->clearOwnerCreditCoupons();

        return $this->getOwnerCreditCoupons();
    }
}
