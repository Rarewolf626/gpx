<?php

namespace GPX\Model\Checkout\Item;

use GPX\Model\Week;
use GPX\Model\Credit;
use GPX\Model\TaxRate;
use GPX\Model\Special;
use GPX\Model\Interval;
use GPX\Model\AutoCoupon;
use GPX\Model\Transaction;
use GPX\Model\Checkout\Guest;
use GPX\Model\Checkout\Totals;
use GPX\Model\Checkout\Deposit;
use GPX\Model\Checkout\Exchange;
use GPX\Model\DepositOnExchange;
use GPX\Model\OwnerCreditCoupon;
use Illuminate\Support\Collection;

/**
 * @property-read string $type
 * @property-read Week $week
 * @property-read int $week_id
 * @property-read bool $flex
 * @property-read ?Credit $credit
 * @property-read ?Interval $interval
 * @property-read Guest $guest
 * @property-read Exchange $exchange
 * @property-read ?Credit $exchange_credit
 * @property-read ?DepositOnExchange $exchange_deposit
 * @property-read ?Deposit $deposit
 * @property-read ?Interval $ownership
 * @property-read Totals $totals
 * @property-read Collection<OwnerCreditCoupon> $occoupons
 * @property-read Collection<Special> $coupons
 * @property-read Collection<AutoCoupon> $auto_coupons
 */
abstract class BaseItem implements \JsonSerializable {
    protected int $cid;
    protected ?int $week_id;
    protected ?Week $week;
    protected Guest $guest;
    protected Exchange $exchange;
    protected bool $flex = false;
    protected float $price = 0.00;
    protected float $special = 0.00;
    protected float $late_fee = 0.00;
    protected float $tp_deposit_fee = 0.00;
    protected float $upgrade_fee = 0.00;
    protected float $extension_fee = 0.00;
    protected float $discount = 0.00;
    protected float $coupon_discount = 0.00;
    protected float $occredit = 0.00;
    protected float $rental_fee = 0.00;
    protected float $cpo_fee = 0.00;
    protected float $guest_fee = 0.00;
    protected float $exchange_fee = 0.00;
    protected float $exchange_same_resort_fee = 0.00;
    protected float $tax = 0.00;
    protected float $total = 0.00;
    protected ?TaxRate $tax_rate = null;
    protected Collection $promos;
    protected ?string $promo = null;
    protected Collection $coupons;
    protected Collection $auto_coupons;
    protected Collection $occoupons;

    public function __construct(int $cid, int $week_id = null) {
        $this->cid = $cid;
        $this->promos = new Collection([]);
        $this->coupons = new Collection([]);
        $this->auto_coupons = new Collection([]);
        $this->occoupons = new Collection([]);
        $this->exchange = new Exchange();
        $this->guest = new Guest([], $cid);
        $this->setWeek($week_id);
    }

    public function getType(): ?string {
        return null;
    }

    public function getWeekId(): ?int {
        return $this->week_id;
    }

    public function clearWeek(): static {
        $this->week_id = null;
        $this->week = null;
        $this->loadFees();
        $this->calculateTotals();

        return $this;
    }

    public function setWeek(int $week_id = null): static {
        $this->week_id = $week_id;
        if ($week_id) {
            $this->week = Week::archived(false)
                              ->with(['theresort', 'unit'])
                              ->find($week_id);
        } else {
            $this->week = null;
        }
        $this->loadFees();
        $this->calculateTotals();

        return $this;
    }

    public function getWeek(): ?Week {
        return $this->week;
    }

    public function getPromos(): Collection {
        return $this->promos;
    }

    public function getPromo(): ?string {
        return $this->promo;
    }

    public function setFlex(bool $flex = true): static {
        return $this;
    }

    public function canAddFlex(): bool {
        return false;
    }

    public function setGuestInfo(array|Guest $guest): static {
        $this->guest = $guest instanceof Guest ? $guest : new Guest($guest);
        $this->calculateTotals();

        return $this;
    }

    public function getGuestInfo(): Guest {
        return $this->guest;
    }

    public function hasGuest(): bool {
        return $this->guest->hasGuest();
    }

    public function setExchangeInfo(array|Exchange $exchange): static {
        $this->exchange = new Exchange();
        $this->calculateTotals();

        return $this;
    }

    public function getExchangeInfo(): Exchange {
        return $this->exchange;
    }

    public function getInterval(): ?Interval {
        return null;
    }

    public function getCredit(): ?Credit {
        return null;
    }

    public function getExchangeCredit(): ?Credit {
        return null;
    }

    public function getExchangeDeposit(): ?DepositOnExchange {
        return null;
    }

    public function getDeposit(): ?Deposit {
        return null;
    }

    public function getOwnership(): ?Interval {
        return null;
    }

    public function getTransaction(): ?Transaction {
        return null;
    }

    public function getTransactionID(): ?int {
        return null;
    }

    public function getExtensionDate(): ?string {
        return null;
    }

    public function calculateLateFee(string|\DateTimeInterface $checkin = null): float {
        return 0.00;
    }

    public function getLateFee(): float {
        return $this->late_fee;
    }

    public function calculateThirdPartyDepositFee(): float {
        return 0.00;
    }

    public function getThirdPartyDepositFee(): float {
        return 0.00;
    }

    public function calculateUpgradeFee(string $unit_type = null): float {
        return 0.00;
    }

    public function getUpgradeFee(): float {
        return $this->upgrade_fee;
    }

    protected function calculateTax(): float {
        $tax = 0.00;
        $enabled = (bool) get_option($this->isExchange() ? 'gpx_tax_transaction_exchange' : 'gpx_tax_transaction_bonus');
        if (!$enabled || !$this->tax_rate) {
            return 0.00;
        }
        $subtotal = array_sum([
            $this->getPrice(),
            $this->getLateFee(),
            $this->getExtensionFee(),
        ]);
        // if there is no subtotal, there is no tax
        if ($subtotal <= 0) return 0.00;

        $percent = $this->tax_rate->total_percent;
        if ($percent > 0) {
            $tax += $subtotal * ($percent / 100);
        }
        $flat = $this->tax_rate->total_flat;
        if ($flat > 0) {
            $tax += $flat;
        }

        return round($tax, 2);
    }

    public function getTax(): float {
        return $this->tax;
    }

    public function getExtensionFee(): float {
        return 0.00;
    }

    public function getDiscount(): float {
        return $this->discount;
    }

    protected function calculateDiscount(): string {
        return 0.00;
    }

    public function getCouponDiscount(): float {
        return $this->coupon_discount;
    }

    public function getOwnerCreditDiscount(): float {
        return $this->occredit;
    }

    public function setCoupons(Collection $coupons): static {
        $this->coupons = $coupons;
        $this->calculateTotals();

        return $this;
    }

    public function setAutoCoupons(Collection $coupons): static {
        $this->auto_coupons = $coupons;

        return $this;
    }

    public function setOwnerCreditCoupons(Collection $coupons): static {
        $this->occoupons = $coupons;
        $this->calculateTotals();

        return $this;
    }

    protected function calculatePercentOffDiscount(Collection $coupons, float $subtotal): float
    {
        //  return $coupons->filter(fn(Special $coupon) => $coupon->isPercentOff())->sum(fn(Special $coupon) => $subtotal * ((float) $coupon->Amount / 100));

        // loop through the coupons and calculate the discount for each item in the cart
        $discount = 0.00;
        foreach ($coupons as $coupon) {
            if ($coupon->isPercentOff()) {

                // calculate if discount require for exchange or rental
                if ($this->isExchange() || $this->isRental()) {
                    $discount += $this->price * ((float) $coupon->Amount / 100);
                }
                // check the discount for CPO/flex fees
                // and 'CPO' in upsell options
                if ($this->getFlexFee() > 0 && in_array('CPO', $coupon->Properties->upsellOptions)) {
                    $discount += $this->getFlexFee() * ((float) $coupon->Amount / 100);
                }
                // check the discount for extension fees
                // and 'Extension Fees' in upsell options
                if ($this->getExtensionFee() > 0 && in_array('Extension Fees', $coupon->Properties->upsellOptions)) {
                    $discount += $this->getExtensionFee() * ((float) $coupon->Amount / 100);
                }
                // check the discount for upgrade fees
                // and 'Upgrade' in upsell options
                if ($this->getUpgradeFee() > 0 && in_array('Upgrade', $coupon->Properties->upsellOptions)) {
                    $discount += $this->getUpgradeFee() * ((float) $coupon->Amount / 100);
                }
                // check the discount for guest fees
                // and 'Guest Fees' in upsell options
                if ($this->getGuestFee() > 0 && in_array('Guest Fees', $coupon->Properties->upsellOptions)) {
                    $discount += $this->getGuestFee() * ((float) $coupon->Amount / 100);
                }
            }
        }
        return round($discount, 2);
    }

    protected function calculateCouponDiscount(): float {
        // if the subtotal is zero, there is no need for a discount
        if ($this->subtotal(true) <= 0) return 0.00;

        // calculations are made with tax excluded
        $subtotal = $this->subtotal(false);
        $discount = 0.00;
        $remaining = $this->coupons;
        if ($this->isExchange() || $this->isRental()) {
            // add discounts for rental / exchange fees
            $coupons = $remaining
                ->filter(fn(Special $coupon) => !!array_intersect($coupon->getAllowedTransactionTypes(), [
                    'any',
                    'RentalWeek',
                    'ExchangeWeek',
                ]))
                ->values();

            // add the set amount coupons
            $amounts = $coupons->filter(fn(Special $coupon) => $coupon->isSetAmount())->pluck('Amount')->values();
            if ($amounts->isNotEmpty()) {
                $amounts->add($this->price);
                $discount += $this->price - $amounts->min();
            }
            // add the percent off coupons
            /*
            $discount += $coupons->filter(fn(Special $coupon) =>
                    $coupon->isPercentOff())->sum(fn(Special $coupon) =>
                        $subtotal * ((float) $coupon->Amount / 100));
            */
            $percentOffDiscount = $this->calculatePercentOffDiscount($this->coupons, $subtotal);
            $discount += $percentOffDiscount;

            // add the dollar off coupons
            $discount += $coupons->filter(fn(Special $coupon) =>
                    $coupon->isDollarOff())->sum('Amount');

            // only upsell-only coupons should remain
            $remaining = $this->coupons->filter(fn(Special $coupon) => $coupon->isUpsell(true));
        }

        // add discounts for extension fees
        if ($this->isExtend() && $this->getExtensionFee() > 0) {
            $fee = $this->getExtensionFee();
            $coupons = $this->coupons
                ->filter(fn(Special $coupon) => !!array_intersect($coupon->getAllowedTransactionTypes(), [
                        'any',
                        'ExtensionWeek',
                        'upsell',
                    ]) && $coupon->isExtensionFeeUpsell())
                ->values();

            // add the set amount coupons
            $discount += $this->calculateDiscountsForFee($coupons, $fee);
        }
        // add discounts for CPO/flex fees
        $fee = $this->getFlexFee();
        if ($fee > 0) {
            $coupons = $remaining->filter(fn(Special $coupon) => $coupon->isCpoUpsell())->values();
            $discount += $this->calculateDiscountsForFee($coupons, $fee);
        }
        // add discounts for upgrade fees
        $fee = $this->getUpgradeFee();
        if ($fee > 0) {
            $coupons = $remaining->filter(fn(Special $coupon) => $coupon->isUpgradeUpsell())->values();
            $discount += $this->calculateDiscountsForFee($coupons, $fee);
        }
        // add discounts for guest fees
        $fee = $this->getGuestFee();
        if ($fee > 0) {
            $coupons = $remaining->filter(fn(Special $coupon) => $coupon->isGuestFeeUpsell())->values();
            $discount += $this->calculateDiscountsForFee($coupons, $fee);
        }

        return round(min($subtotal, $discount), 2);
    }

    protected function calculateDiscountsForFee(Collection $coupons, float $fee): float {
        if ($fee <= 0) return 0.00;
        $discount = 0.00;
        $amounts = $coupons->filter(fn(Special $coupon) => $coupon->isSetAmount())->pluck('Amount')->values();
        if ($amounts->isNotEmpty()) {
            $amounts->add($fee);
            $discount += $fee - $amounts->min();
        }
        // add the percent off coupons
        $discount += $coupons->filter(fn(Special $coupon) => $coupon->isPercentOff())->sum(fn(Special $coupon) => $fee * ((float) $coupon->Amount / 100));
        // add the dollar off coupons
        $discount += $coupons->filter(fn(Special $coupon) => $coupon->isDollarOff())->sum('Amount');

        return round(max(0.00, $discount), 2);
    }

    protected function calculateOwnerCreditDiscount(): float {
        $total = $this->calcTotal(false);
        if ($total <= 0) return 0.00;
        $discount = $this->occoupons->sum(function (OwnerCreditCoupon $coupon) {
            $balance = $coupon->calculateBalance();

            return $balance > 0 ? $balance : 0.00;
        });

        return round(min($total, $discount), 2);
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function getSpecialPrice(): float {
        return $this->special;
    }

    public function getFunctionalPrice(): float {
        $special = $this->getSpecialPrice();
        if ($special > 0) return $special;

        return $this->getPrice();
    }

    public function getFlexFee(bool $force = false): float {
        return ($this->flex || $force) ? $this->cpo_fee : 0.00;
    }

    public function hasFlex(): bool {
        return $this->flex;
    }

    public function getGuestFee(): float {
        return $this->guest->fee ? $this->guest_fee : 0.00;
    }

    protected function subtotal(bool $with_tax = true): float {
        $subtotal = array_sum([
            $this->getFunctionalPrice(),
            $this->getLateFee(),
            $this->getThirdPartyDepositFee(),
            $this->getFlexFee(),
            $this->getGuestFee(),
            $this->getUpgradeFee(),
            $this->getExtensionFee(),
        ]);
        if ($with_tax) $subtotal += $this->getTax();

        return round($subtotal, 2);
    }

    protected function calcTotal(bool $with_credits = true): float {
        $total = $this->subtotal(true) - array_sum([$this->getDiscount(), $this->getCouponDiscount()]);
        if ($with_credits) $total -= $this->getOwnerCreditDiscount();

        return round($total, 2);
    }

    public function getTotal(): float {
        return $this->total;
    }

    protected function calculateTotals(): void {
        $this->upgrade_fee = $this->calculateUpgradeFee();
        $this->late_fee = $this->calculateLateFee();
        $this->discount = $this->calculateDiscount();
        $this->coupon_discount = $this->calculateCouponDiscount();
        $this->occredit = $this->calculateOwnerCreditDiscount();
        $this->tax = $this->calculateTax();
        $this->total = $this->calcTotal(true);
    }

    public function getCoupons(): Collection {
        return $this->coupons;
    }

    public function getAutoCoupons(): Collection {
        return $this->auto_coupons;
    }

    public function addCoupon(Special $coupon, AutoCoupon $auto = null): static {
        $this->coupons->add($coupon);
        if ($auto && $this->coupons->pluck('id')->search($auto->coupon_id) !== false) {
            $this->auto_coupons->add($auto);
        }
        $this->calculateTotals();

        return $this;
    }

    public function addAutoCoupon(AutoCoupon $auto): static {
        if ($this->coupons->pluck('id')->search($auto->coupon_id) !== false) {
            $this->auto_coupons->add($auto);
        }

        return $this;
    }

    public function removeCoupon(int $coupon_id): static {
        $index = $this->coupons->search(fn($coupon) => $coupon->id === $coupon_id);
        if ($index !== false) {
            $coupon = $this->coupons->get($index);
            $this->coupons->forget($index);
            $auto = $this->auto_coupons->search(fn(AutoCoupon $auto_coupon) => $auto_coupon->coupon_id === $coupon->id);
            if ($auto !== false) {
                $this->auto_coupons->forget($auto);
            }
            $this->calculateTotals();
        }

        return $this;
    }

    public function clearCoupons(): static {
        if ($this->coupons->isEmpty()) return $this;
        $this->coupons = new Collection([]);
        $this->auto_coupons = new Collection([]);
        $this->calculateTotals();

        return $this;
    }

    public function getOwnerCreditCoupons(): Collection {
        return $this->occoupons;
    }

    public function addOwnerCreditCoupon(OwnerCreditCoupon $coupon): static {
        $this->occoupons->add($coupon);
        $this->calculateTotals();

        return $this;
    }

    public function removeOwnerCreditCoupon(int $coupon_id): static {
        $index = $this->occoupons->search(fn($coupon) => $coupon->id === $coupon_id);
        if ($index !== false) {
            $this->occoupons->forget($index);
            $this->calculateTotals();
        }

        return $this;
    }

    public function clearOwnerCreditCoupons(): static {
        if ($this->occoupons->isEmpty()) return $this;
        $this->occoupons = new Collection([]);
        $this->calculateTotals();

        return $this;
    }

    protected function loadFees(): static {
        $promo_id = get_user_meta($this->cid, 'lppromoid', true) ?: null;
        $pricing = gpx_get_pricing($this->week, $this->getType(), $this->cid, $promo_id);
        $this->rental_fee = $pricing['rental'];
        $this->exchange_fee = $pricing['exchange'];
        $this->exchange_same_resort_fee = $pricing['exchange_same_resort'];
        $this->extension_fee = $pricing['extension'];
        $this->cpo_fee = $pricing['flex'];
        $this->guest_fee = $pricing['guest'];
        $this->upgrade_fee = $pricing['upgrade'];
        $this->tp_deposit_fee = $pricing['tp_deposit'];
        $this->price = $pricing['special'] > 0 ? $pricing['special'] : $pricing['price'];
        $this->special = $pricing['special'];
        $this->promos = $pricing['promos'];
        $this->promo = $pricing['promo'];
        if ($this->week?->theresort?->taxID) {
            $this->tax_rate = TaxRate::find($this->week->theresort->taxID);
        } else {
            $this->tax_rate = null;
        }

        return $this;
    }

    public function getTotals(): Totals {
        return new Totals([
            'price' => $this->getPrice(),
            'special' => $this->getSpecialPrice(),
            'late' => $this->getLateFee(),
            'third_party' => $this->getThirdPartyDepositFee(),
            'flex' => $this->getFlexFee(),
            'upgrade' => $this->getUpgradeFee(),
            'guest' => $this->getGuestFee(),
            'extension' => $this->getExtensionFee(),
            'discount' => $this->getDiscount(),
            'credit' => 0.00,
            'occredit' => $this->getOwnerCreditDiscount(),
            'coupon' => $this->getCouponDiscount(),
            'tax' => $this->getTax(),
            'total' => $this->getTotal(),
        ]);
    }

    public function isExchange(): bool {
        return false;
    }

    public function isRental(): bool {
        return false;
    }

    public function isBooking(): bool {
        return $this->isExchange() || $this->isRental();
    }

    public function isExtend(): bool {
        return false;
    }

    public function isDeposit(): bool {
        return false;
    }

    public function isGuestFee(): bool {
        return false;
    }

    public function __get(string $name) {
        return match ($name) {
            "type" => $this->getType(),
            "week" => $this->getWeek(),
            "week_id", "weekid" => $this->getWeekId(),
            "guest" => $this->getGuestInfo(),
            "totals" => $this->getTotals(),
            "exchange" => $this->getExchangeInfo(),
            "flex" => $this->hasFlex(),
            "credit" => $this->getCredit(),
            "interval" => $this->getInterval(),
            "exchange_credit" => $this->getExchangeCredit(),
            "exchange_deposit" => $this->getExchangeDeposit(),
            "deposit" => $this->getDeposit(),
            "ownership" => $this->getOwnership(),
            "coupons" => $this->getCoupons(),
            "auto_coupons" => $this->getAutoCoupons(),
            "occoupons" => $this->getOwnerCreditCoupons(),
            "cpo_fee" => $this->cpo_fee,
            "guest_fee" => $this->guest_fee,
            // default => dd($name),
        };
    }

    public function toArray(): array {
        return [
            'id' => $this->getWeekId(),
            'type' => $this->getType(),
            'is_booking' => $this->isBooking(),
            'resort' => $this->week?->theresort->ResortName,
            'city' => $this->week?->theresort->ResortName,
            'region' => $this->week?->theresort->ResortName,
            'checkin' => $this->week?->check_in_date,
            'checkout' => $this->week?->check_out_date,
            'nights' => $this->week?->noNights,
            'bedrooms' => $this->week?->bedrooms,
            'sleep' => $this->week?->sleeps,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
