<?php

namespace GPX\Model;

use DB;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use GPX\Model\Checkout\ShoppingCart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use GPX\Model\ValueObject\Admin\Promo\PromoSearch;
use GPX\Model\ValueObject\Admin\Resort\ResortSearch;

/**
 * @property-read int $id
 * @property \stdClass $Properties
 * @property float $min_week_price
 * @property float|int $Amount
 * @property string $Name
 * @property string $PromoType
 * @property-read array $terms
 */
class Special extends Model {
    protected $table = 'wp_specials';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'master' => 'integer',
        'Active' => 'boolean',
        'StartDate' => 'datetime',
        'EndDate' => 'datetime',
        'TravelStartDate' => 'date',
        'TravelEndDate' => 'date',
        'redeemed' => 'integer',
        'reworked' => 'integer',
        'Properties' => 'array',
        'revisedBy' => 'array',
    ];


    public function getTransactionTypeAttribute(): ?string {
        $properties = $this->Properties;
        if (empty($properties->transactionType)) {
            return null;
        }
        $type = array_filter(Arr::wrap($properties->transactionType));
        if (empty($type)) {
            return null;
        }
        if (count($type) === 1) {
            return Arr::first($type);
        }

        return implode(', ', $type);
    }

    public function getPropertiesAttribute($value) {
        if (null === $value || $value === '') {
            return new \stdClass();
        }
        if (is_object($value)) {
            return $value;
        }
        if (is_array($value)) {
            return (object) $value;
        }
        if (is_string($value)) {
            return json_decode($value, false);
        }

        return new \stdClass();
    }

    public function setPropertiesAttribute($value) {
        if ($value === null || $value === '') {
            $this->attributes['Properties'] = json_encode('{}');
        } elseif (is_string($value)) {
            $this->attributes['Properties'] = json_encode(json_decode($value, false));
        } elseif (is_array($value) || is_object($value)) {
            $this->attributes['Properties'] = json_encode($value);
        }
    }

    public function isLandingPage(): bool {
        return ($this->Properties->availability ?? null) === 'Landing Page';
    }

    public function getTermsAttribute(): array {
        return array_values(array_unique(array_filter(Arr::wrap($properties->terms ?? []))));
    }

    public function getAllowedTransactionTypes(): array {
        $allowed = Arr::wrap($this->Properties->transactionType ?? []);
        if (in_array('BonusWeek', $allowed)) $allowed[] = 'RentalWeek';

        return $allowed;
    }


    public function canStack(): bool {
        $properties = $this->properties;
        if (isset($properties->stacking) && $properties->stacking === 'No') return false;

        return true;
    }

    public function getUpsellOptions(): array {
        return Arr::wrap($this->Properties->upsellOptions ?? []);
    }

    public function isForAnyTransactionType(): bool {
        $allowed = $this->getAllowedTransactionTypes();

        return in_array('any', $allowed);
    }

    public function isForRentals(): bool {
        $allowed = $this->getAllowedTransactionTypes();
        if (in_array('any', $allowed)) return true;

        return in_array('RentalWeek', $allowed);
    }

    public function isForExchanges(): bool {
        $allowed = $this->getAllowedTransactionTypes();
        if (in_array('any', $allowed)) return true;

        return in_array('ExchangeWeek', $allowed);
    }

    public function isUpsell(bool $strict = false): bool {
        $allowed = $this->getAllowedTransactionTypes();
        if ($strict) return $allowed == ['upsell'];

        return in_array('upsell', $allowed);
    }

    public function isCpoUpsell(): bool {
        $options = $this->getUpsellOptions();

        return in_array('CPO', $options);
    }

    public function isUpgradeUpsell(): bool {
        $options = $this->getUpsellOptions();

        return in_array('Upgrade', $options);
    }

    public function isGuestFeeUpsell(): bool {
        $options = $this->getUpsellOptions();

        return in_array('Guest Fees', $options);
    }

    public function isExtensionFeeUpsell(): bool {
        $options = $this->getUpsellOptions();

        return in_array('Extension Fees', $options);
    }

    public function isResortExcluded(int $resort_id): bool {
        $excluded = $this->Properties?->exclude_resort ?? null;
        if (is_string($excluded) && !is_numeric($excluded) && !empty($excluded)) $excluded = json_decode(stripslashes($excluded), true);
        if (empty($excluded)) return false;

        return in_array($resort_id, Arr::wrap($excluded));
    }

    public function isRegionExcluded(int $region_id): bool {
        $excluded = $this->Properties?->exclude_region ?? null;
        if (is_string($excluded) && !is_numeric($excluded) && !empty($excluded)) $excluded = json_decode(stripslashes($excluded), true);
        if (empty($excluded)) return false;
        $excluded = Arr::wrap($excluded);
        $regions = Region::tree($excluded)->select('wp_gpxRegion.id')->pluck('id')->toArray();

        return in_array($region_id, $regions);
    }

    public function isHomeResortExcluded(string $resort_name, ?int $cid = null): bool {
        if (!$cid) return false;
        if (!str_contains($this->Properties->exclusions ?? null, 'home-resort')) return false;

        return in_array($resort_name, [
            get_user_meta($cid, 'OwnResort1', true),
            get_user_meta($cid, 'OwnResort2', true),
            get_user_meta($cid, 'OwnResort3', true),
        ]);
    }

    public function isExcludedByLeadTime(DateTimeInterface|string $checkin): bool {
        $checkin = $checkin instanceof DateTimeInterface ? Carbon::instance($checkin)->startOfDay() : Carbon::parse($checkin)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $min = (int) $this->Properties->leadTimeMin ?? 0;
        $diff = $today->diffInDays($checkin, false);
        if ($min > 0 && $diff < $min) {
            return true;
        }
        $max = (int) $this->Properties->leadTimeMax ?? 0;
        if ($max > 0 && $diff > $max) {
            return true;
        }

        return false;
    }

    public function isExcludedByBookingDate(): bool {
        $min = $this->Properties->bookStartDate ?? null;
        if ($min && Carbon::parse($min)->isFuture()) {
            return true;
        }
        $max = $this->Properties->bookEndDate ?? null;
        if ($max && Carbon::parse($max)->isPast()) {
            return true;
        }

        return false;
    }

    public function isExcludedByDae(): bool {
        $excluded = (bool) ($this->Properties->exclude_dae ?? false);

        // this exclusion does not seem to be used
        return false;
    }

    public function isDateBlackedOut(DateTimeInterface|string $checkin): bool {
        $blackouts = $this->Properties->blackout ?? [];
        if (empty($blackouts)) return false;
        $checkin = $checkin instanceof DateTimeInterface ? Carbon::instance($checkin) : Carbon::parse($checkin);
        foreach ($blackouts as $blackout) {
            $start = Carbon::parse($blackout->start)->startOfDay();
            $end = Carbon::parse($blackout->end)->endOfDay();
            // is the checkin date between the blackout dates
            if ($checkin >= $start && $checkin <= $end) return true;
        }

        return false;
    }

    public function isResortBlackedOut(int $resort_id, DateTimeInterface|string $checkin): bool {
        $blackouts = $this->Properties->resortBlackout ?? [];
        if (empty($blackouts)) return false;
        $checkin = $checkin instanceof DateTimeInterface ? Carbon::instance($checkin) : Carbon::parse($checkin);
        foreach ($blackouts as $blackout) {
            if (in_array($resort_id, $blackout->resorts ?? [])) {
                $start = Carbon::parse($blackout->start)->startOfDay();
                $end = Carbon::parse($blackout->end)->endOfDay();
                // is the checkin date between the blackout dates
                if ($checkin >= $start && $checkin <= $end) return true;
            }
        }

        return false;
    }

    public function isResortTravelDateBlackedOut(int $resort_id, DateTimeInterface|string $checkin): bool {
        $blackouts = $this->Properties->resortBlackout ?? [];
        if (empty($blackouts)) return false;
        $checkin = $checkin instanceof DateTimeInterface ? Carbon::instance($checkin) : Carbon::parse($checkin);
        foreach ($blackouts as $blackout) {
            if (in_array($resort_id, $blackout->resorts ?? [])) {
                $start = Carbon::parse($blackout->start)->startOfDay();
                $end = Carbon::parse($blackout->end)->endOfDay();
                // is the checkin date between the blackout dates
                if ($checkin >= $start && $checkin <= $end) return true;
            }
        }

        return false;
    }

    public function isCustomerAllowedToUse(?int $cid = null): bool {
        if (!str_contains($this->Properties->usage, 'customer')) return true;
        if (!$cid) return false;
        $allowed = $this->getSpecificCustomerAttribute();
        if (empty($allowed)) return true;
        $allowed = Arr::wrap($allowed);

        return in_array($cid, $allowed);
    }

    public function canBeUsedForRegion(int $region_id): bool {
        if (!str_contains($this->Properties->usage, 'region')) return true;
        $allowed = $this->Properties->usage_region ?? [];
        if (is_string($allowed)) $allowed = array_filter(array_map('intval', json_decode(stripslashes($allowed), true)));
        if (empty($allowed)) return true;
        $regions = Region::tree($allowed)->select('wp_gpxRegion.id')->pluck('id')->toArray();
        return in_array($region_id, $regions);
    }

    public function canBeUsedForResort(int $resort_id, int $region_id): bool {
        if (str_contains($this->Properties->usage, 'region')) {
            return $this->canBeUsedForRegion($region_id);
        }

        if (!str_contains($this->Properties->usage, 'resort')) return true;
        $allowed = $this->Properties->usage_resort ?? [];
        if (is_string($allowed)) $allowed = json_decode(stripslashes($allowed), true);
        if (empty($allowed)) return true;

        return in_array($resort_id, $allowed);
    }

    public function canBeUsedForTransactionType(string $type): bool {
        $allowed = $this->getAllowedTransactionTypes();
        $options = $this->getUpsellOptions();
        // there are no implemented discounts for the deposit late fee
        if ($type === 'DepositWeek') return false;
        if ($type === 'ExtendWeek') {
            if (!array_intersect(['upsell', 'any'], $allowed)) return false;

            return in_array('Extension Fees', $options);
        }
        // This is a rental or exchange
        if (in_array('any', $allowed)) return true;
        if (in_array($type, $allowed)) return true;
        if (!in_array('upsell', $allowed)) return false;

        if ($type === 'ExchangeWeek') {
            // exchanges can have flex fees, upgrade fees and guest fees
            return !!array_intersect(['CPO', 'Upgrade', 'Guest Fees'], $options);
        }

        // rentals can only have guest fees
        return in_array('Guest Fees', $options);
    }

    public function isBlockedByMinPrice(float $total, bool $is_exchange = false): bool {
        $min = $this->min_week_price ?? 0;
        if ($min <= 0) return false;

        if ($is_exchange) return true;

        return $total < $min;
    }

    public function isBlockedByUpsellCPO(DateTimeInterface|string $checkin, float $fee): bool {
        if (!$this->isUpsell(true)) return false;
        if (!$this->isCpoUpsell()) return false;
        if ($fee <= 0) return false;
        $checkin = $checkin instanceof DateTimeInterface ? Carbon::instance($checkin) : Carbon::parse($checkin);

        return $checkin->clone()->subDays(45)->isPast();
    }

    public function isAlreadyRedeemed(int $cid): bool {
        if (!$this->isSingleUse()) return false;
        $customers = $this->getSpecificCustomerAttribute();
        $redemptions = RedeemedCoupon::forUser($cid)->forCoupon($this->id)->get();
        if ($redemptions->isEmpty()) return false;
        $customersCount = array_count_values($customers);
        $hcCustomers = include get_template_directory() . '/data/hc-customers.php';
        $hcConverted = [];
        foreach ($hcCustomers as $hccVs) {
            foreach ($hccVs as $hccV) {
                if ($hccV === $cid) {
                    $hcConverted[] = $cid;
                    //was this owner already added?
                    if (isset($customersCount[$cid]) && $customersCount[$cid] > 0) {
                        //this is a duplicate record -- we need to reduce the original amount
                        $customersCount[$cid]--;
                    }
                }
            }
        }
        $hcCustomercount = count($hcConverted);
        $customersCount[$cid] += $hcCustomercount;

        return ($redemptions->count() >= $customersCount[$cid]);
    }

    public function isValidForCart(ShoppingCart $cart): bool {
        // does the cart already have a coupon that can't be stacked?
        if ($cart->coupons->filter(fn(Special $coupon) => !$coupon->canStack())->isNotEmpty()) return false;
        // is this coupon stackable?
        if (!$this->canStack()) {
            // does the cart have a coupon already?
            if ($cart->coupons->isNotEmpty()) return false;
            // does the cart have a promo discount
            // @TODO - this is not fully implemented
            if ($cart->promo && $cart->item()->getDiscount()) return false;
        }
        // check expiration
        if (!$this->hasStarted()) return false;
        if ($this->isExpired()) return false;
        // usage rules
        if (!$this->canBeUsedForTransactionType($cart->item()->getType())) return false;
        if ($this->isDateBlackedOut($cart->week->check_in_date)) return false;
        if ($this->isResortBlackedOut($cart->week->resort, $cart->week->check_in_date)) return false;
        if ($this->isResortTravelDateBlackedOut($cart->week->resort, $cart->week->check_in_date)) return false;
        if ($this->isBlockedByMinPrice($cart->getTotals()->total, $cart->item()->isExchange())) return false;
        if ($this->isBlockedByUpsellCPO($cart->week->check_in_date, $cart->item()->getFlexFee())) return false;
        if (!$this->isCustomerAllowedToUse($cart->cid)) return false;
        if (!$this->canBeUsedForRegion($cart->week->theresort->gpxRegionID)) return false;
        if (!$this->canBeUsedForResort($cart->week->resort,$cart->week->theresort->gpxRegionID )) return false;
        // exclusions
        if ($this->isResortExcluded($cart->week->resort)) return false;
        if ($this->isRegionExcluded($cart->week->theresort->gpxRegionID)) return false;
        if ($this->isExcludedByLeadTime($cart->week->check_in_date)) return false;
        if ($this->isExcludedByBookingDate()) return false;

        // check if already used
        if ($this->isAlreadyRedeemed($cart->cid)) return false;

        return true;
    }

    public function getLandingPageId(int $week_id, int $cid): ?string {
        $availability = $this->Properties->availability ?? null;
        if ($availability !== 'Landing Page') return null;
        $value = $week_id . '|' . $this->id;
        $meta = get_user_meta($cid, 'lppromoid');

        if ($meta && $meta === $value) {
            return $value;
        }

        if ($this->master) {
            $value = $week_id . '|' . $this->master;
            if($meta === $value) return $value;

            $promos = Special::select('id')->where('master', '=', $this->master)->get();
            foreach ($promos as $lpm) {
                if (isset($_COOKIE["lppromoid|{$week_id}|{$lpm->id}"])) {
                    return $week_id . '|' . $lpm->id;
                }
            }
        }

        if (PreHold::select('lpid')
                   ->where('weekId', '=', $week_id)
                   ->where('user', '=', $cid)
                   ->where('lpid', '=', $week_id . $this->id)
                   ->exists()) {
            return $week_id . '|' . $this->id;
        }

        return null;
    }

    public function landingPageWasNotVisited(?int $cid = null): bool {
        $availability = $this->Properties->availability ?? null;
        if ($availability !== 'Landing Page') return false;
        if (!$cid) return true;
        $meta = get_user_meta($cid, 'lppromoid', true);

        return $this->id != $meta;
    }

    public function getMinWeekPriceAttribute(): float {
        $properties = $this->Properties;
        if (isset($properties->minWeekPrice) && $properties->minWeekPrice > 0) return (float) $properties->minWeekPrice;

        return 0.00;
    }

    public function getSortOrderAttribute(): int {
        return match ($this->promoType) {
            "Set Amt" => 0,
            "Dollar Off" => 1,
            "Pct Off" => 2,
            default => 3,
        };
    }

    public function hasStarted(): bool {
        return $this->StartDate->isPast();
    }

    public function isExpired(): bool {
        return $this->EndDate->isPast();
    }

    public function isCurrent(): bool {
        return $this->hasStarted() && !$this->isExpired();
    }

    public function isSingleUse(): bool {
        return ($this->Properties?->singleUse ?? 'No') === 'Yes';
    }

    public function getSpecificCustomerAttribute(): array {
        $customers = $this->Properties?->specificCustomer ?? [];
        if (is_string($customers)) $customers = json_decode(stripslashes($customers), true);

        return Arr::wrap($customers);
    }

    public function getPromoType(): ?string {
        $discountTypes = ['Pct Off', 'Dollar Off', 'Set Amt', 'BOGO', 'BOGOH', 'Auto Create Coupon'];
        $type = $this->Properties?->promoType ?? $this->PromoType;

        return in_array($type, $discountTypes) ? $type : $this->PromoType;
    }

    public function isPercentOff(): bool {
        return $this->getPromoType() === 'Pct Off';
    }

    public function isDollarOff(): bool {
        return $this->getPromoType() === 'Dollar Off';
    }

    public function isBogo(): bool {
        return in_array($this->getPromoType(), ['BOGO', 'BOGOH']);
    }

    public function isSetAmount(): bool {
        return $this->getPromoType() === 'Set Amt';
    }

    public function isCoupon(): bool {
        return $this->Type === 'coupon';
    }

    public function isPromo(): bool {
        return $this->Type === 'promo';
    }

    public function scopeActive(Builder $query, bool $active = true): Builder {
        return $query->where('wp_specials.Active', '=', $active);
    }

    public function scopeCurrent(Builder $query, \DateTimeInterface $now = null): Builder {
        $now = $now ? Carbon::instance($now) : Carbon::now();

        return $query->where(fn($query) => $query
            ->where('wp_specials.StartDate', '<=', $now)
            ->where('wp_specials.EndDate', '>=', $now)
        );
    }

    public function scopeCode(Builder $query, string $slug) {
        return $query->where(fn($query) => $query
            ->where('wp_specials.Name', '=', $slug)
            ->orWhere('wp_specials.Slug', '=', $slug)
        );
    }

    public function scopeSlug(Builder $query, string $slug): Builder {
        return $query->where('wp_specials.Slug', '=', $slug);
    }

    public function scopeType(Builder $query, string $type): Builder {
        return $query->where('wp_specials.Type', '=', $type);
    }

    public function scopePromoType(Builder $query, string $type): Builder {
        return $query->where('wp_specials.PromoType', '=', $type);
    }

    public function scopeCoupon(Builder $query): Builder {
        return $query->type('coupon');
    }

    public function scopePromo(Builder $query): Builder {
        return $query->type('promo');
    }

    public function scopeLandingPage(Builder $query): Builder {
        return $query->whereRaw('Properties->>"$.availability" = ?', 'Landing Page');
    }

    public function scopeAdminSearch(Builder $query, PromoSearch $search): Builder {
        return $query
            ->when($search->id !== '', fn($query) => $query->where('id', 'LIKE', $search->id . '%'))
            ->when($search->name !== '', fn($query) => $query->where('Name', 'LIKE', '%' . $search->name . '%'))
            ->when($search->slug !== '', fn($query) => $query->where('Slug', 'LIKE', '%' . $search->slug . '%'))
            ->when($search->coupon !== '', fn($query) => $query->where(fn($query) => $query
                ->where('redeemed', '!=', 0)
                ->where('redeemed', 'LIKE', $search->coupon . '%')
            ))
            ->when($search->availability === 'landing', fn($query) => $query->whereRaw('Properties->>"$.availability" = ?', 'Landing Page'))
            ->when($search->availability === 'site', fn($query) => $query->whereRaw('Properties->>"$.availability" = ?', 'Site-wide'))
            ->when($search->type === 'coupon', fn($query) => $query->type('coupon'))
            ->when($search->type === 'promo', fn($query) => $query->type('promo'))
            ->when($search->active === 'yes', fn($query) => $query->active(true))
            ->when($search->active === 'no', fn($query) => $query->active(false))
            ->when($search->travel, fn($query) => $query->where(fn($query) => $query
                ->whereDate('TravelStartDate', '<=', $search->travel)
                ->whereDate('TravelEndDate', '>=', $search->travel)
            ))
            ->when($search->sort !== 'id', fn($query) => $query
                ->orderBy(match ($search->sort) {
                    'type' => 'Type',
                    'name' => 'Name',
                    'slug' => 'Slug',
                    'availability' => DB::raw('Properties->>"$.availability"'),
                    'travel_start' => 'TravelStartDate',
                    'travel_end' => 'TravelEndDate',
                    'active' => 'Active',
                    'coupon' => 'redeemed',
                    default => 'id',
                }, $search->dir)
            )
            ->orderBy('id', $search->dir);
    }
}
