<?php

namespace GPX\Model;

use DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use GPX\Model\ValueObject\Admin\Transaction\TransactionSearch;

/**
 * @property-read int $id
 * @property string $transactionType
 * @property string $cartID
 * @property ?string $sessionID
 * @property int $userID
 * @property string $resortID
 * @property ?int $weekId
 * @property ?Carbon $check_in_date
 * @property ?int $depositID
 * @property ?int $parent_id
 * @property ?int $paymentGatewayID
 * @property ?int $transactionRequestId
 * @property array $transactionData
 * @property ?string $sfid
 * @property ?array $sfData
 * @property ?array $data
 * @property ?float $returnTime
 * @property Carbon $datetime
 * @property string $authorization_number
 * @property string $merchant_response
 * @property string $billing_address
 * @property string $inCard_name
 * @property string $CVV
 * @property string $expiry_date
 * @property int $card_number
 * @property bool $cancelled
 * @property ?Carbon $cancelledDate
 * @property string $cancelledData
 * @property ?User $user
 * @property ?Partner $partner
 * @property ?Week $week
 * @property ?Transaction $previous
 */
class Transaction extends Model {
    protected $table = 'wp_gpxTransactions';
    protected $primaryKey = 'id';
    const CREATED_AT = 'datetime';
    const UPDATED_AT = null;
    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'userID' => 'integer',
        'weekId' => 'integer',
        'parent_id' => 'integer',
        'depositID' => 'integer',
        'transactionRequestId' => 'integer',
        'transactionData' => 'array',
        'paymentGatewayID' => 'integer',
        'sfid' => 'string',
        'sfData' => 'array',
        'data' => 'array',
        'card_number' => 'integer',
        'datetime' => 'datetime',
        'check_in_date' => 'date',
        'cancelledDate' => 'date',
        'cancelledData' => 'array',
        'cancelled' => 'boolean',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'userID', 'ID');
    }

    public function partner(): BelongsTo {
        return $this->belongsTo(Partner::class, 'userID', 'user_id');
    }

    public function resort(): BelongsTo {
        return $this->belongsTo(Resort::class, 'resortID', 'ResortID');
    }

    public function week(): BelongsTo {
        return $this->belongsTo(Week::class, 'weekId', 'record_id');
    }

    public function deposit(): BelongsTo {
        return $this->belongsTo(Credit::class, 'depositID', 'id');
    }

    public function previous(): BelongsTo {
        return $this->belongsTo(Transaction::class, 'parent_id', 'id');
    }

    public function related(): HasMany {
        return $this->hasMany(Transaction::class, 'parent_id', 'id');
    }

    public function getCheckinAttribute(): ?Carbon {
        $data = $this->data;
        if (empty($data['checkIn'])) {
            return null;
        }

        return Carbon::parse($data['checkIn']);
    }

    public function getCheckoutAttribute(): ?Carbon {
        $checkin = $this->getCheckinAttribute();
        if (!$checkin) {
            return null;
        }

    }

    public function isBooking(): bool {
        return $this->transactionType === 'booking';
    }

    public function isExchange(): bool {
        if ($this->transactionType !== 'booking') return false;

        return in_array(mb_strtolower(trim($this->data['WeekType'] ?? '')), [
            'exchange',
            'exchangeweek',
            'exchange week',
        ]);
    }

    public function isDeposit(): bool {
        return $this->transactionType === 'deposit';
    }

    public function isCreditTransfer(): bool {
        return $this->transactionType === 'credit_transfer';
    }

    public function hasFlexBooking(): bool {
        if ($this->transactionType !== 'booking') return false;

        return ($this->data['CPO'] ?? '') === 'Taken';
    }

    public function hasGuestFee(): bool {
        $data = $this->data;
        if (array_key_exists('HasGuestFee', $data)) {
            return (bool)$data['HasGuestFee'];
        }

        return ($data['actguestFee'] ?? $data['GuestFeeAmount'] ?? 0) > 0;
    }

    public function canBeRefunded(): bool {
        if ($this->transactionType !== 'booking') return false;
        if ($this->cancelled) return false;
        if (!$this->hasFlexBooking()) return false;

        return $this->check_in_date->clone()->endOfDay()->subDays(45)->isFuture();
    }

    public function scopeBooking(Builder $query): Builder {
        return $query->type('booking');
    }

    public function scopeType(Builder $query, array|string|Collection $type = []): Builder {
        $type = $type instanceof Collection ? $type : Arr::wrap($type);

        return $query->whereIn('transactionType', $type);
    }

    public function scopeCancelled(Builder $query, bool $cancelled = true): Builder {
        return $query->when($cancelled, fn($query) => $query->where('cancelled', '=', true))
                     ->when(!$cancelled, fn($query) => $query->where('cancelled', '=', false));
    }

    public function scopeByCart(Builder $query, string|array|Collection $cart_id): Builder {
        if (is_array($cart_id) || $cart_id instanceof Collection) {
            return $query->whereIn('cartID', $cart_id);
        }

        return $query->where('cartID', '=', $cart_id);
    }

    public function scopeForUser(Builder $query, int|array|Collection $cid): Builder {
        if (is_array($cid) || $cid instanceof Collection) {
            return $query->whereIn('userID', $cid);
        }

        return $query->where('userID', '=', $cid);
    }

    public function scopeForWeek(Builder $query, int|array|Collection $week_id): Builder {
        if (is_array($week_id) || $week_id instanceof Collection) {
            return $query->whereIn('weekId', $week_id);
        }

        return $query->where('weekId', '=', $week_id);
    }

    public function scopeUpcoming(Builder $query): Builder {
        return $query->whereRaw('check_in_date >= CURRENT_DATE()');
    }

    public function scopePast(Builder $query): Builder {
        return $query->whereRaw('check_in_date < CURRENT_DATE()');
    }

    public function scopeAdminSearch(Builder $query, TransactionSearch $search): Builder {
        return $query
            ->when($search->id !== '', fn($query) => $query->where('id', 'LIKE', $search->id . '%'))
            ->when($search->type, fn($query) => $query->type($search->type))
            ->when($search->user !== '', fn($query) => $query->where('userID', 'LIKE', '%' . $search->user . '%'))
            ->when($search->owner_id !== null, fn($query) => $query->where('userID', '=', $search->owner_id))
            ->when($search->parent_id !== null, fn($query) => $query->where('parent_id', '=', $search->parent_id))
            ->when($search->owner !== '', fn($query) => $query->whereRaw('LOWER(transactionData->>"$.OwnerName") LIKE ?', '%' . mb_strtolower($search->owner) . '%'))
            ->when($search->adults !== null, fn($query) => $query->whereRaw('transactionData->>"$.Adults" = ?', $search->adults))
            ->when($search->children !== null, fn($query) => $query->whereRaw('transactionData->>"$.Children" = ?', $search->children))
            ->when($search->upgrade !== null, fn($query) => $query->whereRaw('transactionData->>"$.UpgradeFee" LIKE ?', $search->upgrade . '%'))
            ->when($search->cpo === 'taken', fn($query) => $query->whereIn(DB::raw('LOWER(transactionData->>"$.CPO")'), [
                'taken',
                't',
            ]))
            ->when($search->cpo === 'nottaken', fn($query) => $query->whereIn(DB::raw('LOWER(transactionData->>"$.CPO")'), [
                'nottaken',
                'n',
            ]))
            ->when($search->cpo === 'na', fn($query) => $query->whereRaw('LOWER(transactionData->>"$.CPO") = ?', 'notapplicable'))
            ->when($search->cpo_fee !== null, fn($query) => $query->whereRaw('transactionData->>"$.CPOFee" LIKE ?', $search->cpo_fee . '%'))
            ->when($search->resort !== '', fn($query) => $query->whereRaw('LOWER(transactionData->>"$.ResortName") LIKE ?', '%' . mb_strtolower($search->resort) . '%'))
            ->when($search->room !== '', fn($query) => $query->whereRaw('LOWER(transactionData->>"$.Size") LIKE ?', '%' . mb_strtolower($search->room) . '%'))
            ->when($search->week_type === 'rental', fn($query) => $query->whereRaw("LOWER(transactionData->>\"$.WeekType\") = 'rental'"))
            ->when($search->week_type === 'exchange', fn($query) => $query->whereRaw("LOWER(transactionData->>\"$.WeekType\") = 'exchange'"))
            ->when($search->balance !== null, fn($query) => $query->whereRaw('transactionData->>"$.Balance" LIKE ?', $search->balance . '%'))
            ->when($search->resort_id !== '', fn($query) => $query->where('resortID', 'LIKE', '%' . $search->resort_id . '%'))
            ->when($search->week !== '', fn($query) => $query->where('weekID', 'LIKE', '%' . $search->week . '%'))
            ->when($search->sleeps !== null, fn($query) => $query->whereRaw('transactionData->>"$.sleeps" = ?', $search->sleeps))
            ->when($search->bedrooms !== '', fn($query) => $query->whereRaw('LOWER(transactionData->>"$.bedrooms") LIKE ?', '%' . mb_strtolower($search->bedrooms) . '%'))
            ->when($search->nights !== null, fn($query) => $query->whereRaw('transactionData->>"$.noNights" = ?', $search->nights))
            ->when($search->checkin, fn($query) => $query->where('check_in_date', '=', $search->checkin->format('Y-m-d')))
            ->when($search->paid !== null, fn($query) => $query->whereRaw('transactionData->>"$.Paid" LIKE ?', $search->paid . '%'))
            ->when($search->processed !== '', fn($query) => $query->whereRaw('LOWER(transactionData->>"$.processedBy") LIKE ?', '%' . mb_strtolower($search->processed) . '%'))
            ->when($search->promo !== '', fn($query) => $query->whereRaw('LOWER(transactionData->>"$.promoName") LIKE ?', '%' . mb_strtolower($search->promo) . '%'))
            ->when($search->discount !== null, fn($query) => $query->whereRaw('transactionData->>"$.discount" LIKE ?', $search->discount . '%'))
            ->when($search->coupon !== null, fn($query) => $query->whereRaw('transactionData->>"$.couponDiscount" LIKE ?', $search->coupon . '%'))
            ->when($search->occoupon !== '', fn($query) => $query->whereRaw('transactionData->>"$.ownerCreditCouponID" LIKE ?', '%' . $search->occoupon . '%'))
            ->when($search->ocdiscount !== null, fn($query) => $query->whereRaw('transactionData->>"$.ownerCreditCouponAmount" LIKE ?', $search->ocdiscount . '%'))
            ->when($search->date, fn($query) => $query->whereDate('datetime', '=', $search->date->format('Y-m-d')))
            ->when($search->cancelled === 'yes', fn($query) => $query->where('cancelled', '=', 1))
            ->when($search->cancelled === 'no', fn($query) => $query->where('cancelled', '=', 0))
            ->when($search->sort !== 'id', fn($query) => $query
                ->orderBy(match ($search->sort) {
                    'type' => 'transactionType',
                    'user' => 'userID',
                    'member' => DB::raw('transactionData->>"$.MemberName"'),
                    'owner' => DB::raw('transactionData->>"$.OwnerName"'),
                    'guest' => DB::raw('transactionData->>"$.GuestName"'),
                    'adults' => DB::raw('CAST(transactionData->>"$.Adults" as UNSIGNED INTEGER)'),
                    'children' => DB::raw('CAST(transactionData->>"$.Children" as UNSIGNED INTEGER)'),
                    'upgrade' => DB::raw('CAST(transactionData->>"$.UpgradeFee" as DECIMAL(10,2))'),
                    'cpo' => DB::raw('transactionData->>"$.CPO"'),
                    'cpo_fee' => DB::raw('CAST(transactionData->>"$.CPOFee" as DECIMAL(10,2))'),
                    'resort' => DB::raw('transactionData->>"$.ResortName"'),
                    'room' => DB::raw('transactionData->>"$.Size"'),
                    'week_type' => DB::raw('transactionData->>"$.WeekType"'),
                    'balance' => DB::raw('CAST(transactionData->>"$.Balance" as DECIMAL(10,2))'),
                    'resort_id' => 'resortID',
                    'week' => 'weekId',
                    'deposit' => 'depositID',
                    'sleeps' => DB::raw('CAST(transactionData->>"$.sleeps" as UNSIGNED INTEGER)'),
                    'bedrooms' => DB::raw('transactionData->>"$.bedrooms"'),
                    'nights' => DB::raw('CAST(transactionData->>"$.noNights" as UNSIGNED INTEGER)'),
                    'checkin' => 'check_in_date',
                    'paid' => DB::raw('CAST(transactionData->>"$.Paid" as DECIMAL(10,2))'),
                    'promo' => DB::raw('transactionData->>"$.promoName"'),
                    'discount' => DB::raw('CAST(transactionData->>"$.discount" as DECIMAL(10,2))'),
                    // this column is in the format $1,000.00 so we need to remove the $ and , and cast to decimal to sort numerically
                    'coupon' => DB::raw('CAST(REPLACE(REPLACE(transactionData->>"$.couponDiscount", "$", ""), ",", "") as DECIMAL(10,2))'),
                    'occoupon' => DB::raw('transactionData->>"$.ownerCreditCouponID"'),
                    'ocdiscount' => DB::raw('CAST(transactionData->>"$.ownerCreditCouponAmount" as DECIMAL(10,2))'),
                    'date' => 'datetime',
                    'cancelled' => 'cancelled',
                    default => 'id',
                }, $search->dir)
            )
            ->orderBy('id', $search->dir);
    }
}
