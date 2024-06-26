<?php

namespace GPX\Model;

use DB;
use GPX\Model\Checkout\ShoppingCart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OwnerCreditCoupon extends Model {
    protected $table = 'wp_gpxOwnerCreditCoupon';
    protected $primaryKey = 'id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = null;
    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'singleuse' => 'boolean',
        'active' => 'boolean',
        'created_date' => 'timestamp',
        'expirationDate' => 'date',
        'redeemed' => 'float',
        'amount' => 'float',
    ];

    public function owners(): BelongsToMany {
        return $this->belongsToMany(User::class, 'wp_gpxOwnerCreditCoupon_owner', 'couponID', 'ownerID');
    }

    public function activity(): HasMany {
        return $this->hasMany(OwnerCreditCouponActivity::class, 'couponID');
    }

    public function isSingleUse(): bool {
        return $this->singleuse;
    }

    public function setRedeemedAttribute($value = null): void {
        if ($value === null) $value = 0.00;
        $this->attributes['redeemed'] = $value;
    }

    public function setAmountAttribute($value = null): void {
        if ($value === null) $value = 0.00;
        $this->attributes['amount'] = $value;
    }

    public function scopeActive(Builder $query, bool $active = true): Builder {
        return $query->where('wp_gpxOwnerCreditCoupon.active', '=', $active);
    }

    public function scopeSingleUse(Builder $query, bool $single_use = true): Builder {
        return $query->where('wp_gpxOwnerCreditCoupon.singleuse', '=', $single_use);
    }

    public function scopeByCode(Builder $query, string $code): Builder {
        return $query->where(fn(Builder $query) => $query
            ->where('wp_gpxOwnerCreditCoupon.name', '=', $code)
            ->orWhere('wp_gpxOwnerCreditCoupon.couponcode', '=', $code)
        );
    }

    public function scopeByOwner(Builder $query, int|User $owner): Builder {
        $cid = $owner instanceof User ? $owner->id : $owner;

        return $query->whereHas('owners', fn(Builder $query) => $query->where('wp_users.ID', '=', $cid));
    }

    public function scopeWithRedeemed(Builder $query): Builder {
        return $query->withSum(['activity as redeemed' => fn(Builder $query) => $query->where('activity', '=', 'transaction')], 'amount');
    }

    public function scopeWithAmount(Builder $query): Builder {
        return $query->withSum(['activity as amount' => fn(Builder $query) => $query->where('activity', '!=', 'transaction')], 'amount');
    }

    public function hasBalance(): bool {
        $balance = $this->calculateBalance();

        return $balance > 0;
    }

    public function calculateBalance(): float {
        if (!array_key_exists('redeemed', $this->attributes) || !array_key_exists('amount', $this->attributes)) {
            $this->loadMissing('activity');
            $this->redeemed = (float) $this->activity->where('activity', '==', 'transaction')->sum('amount');
            if ($this->isSingleUse() && $this->redeemed > 0) {
                return 0.00;
            }
            $this->amount = (float) $this->activity->where('activity', '!=', 'transaction')->sum('amount');
        }

        if ($this->isSingleUse() && $this->redeemed > 0) {
            return 0.00;
        }

        return $this->amount - $this->redeemed;
    }

    public static function generateUniqueSlug(string $slug): string {
        $generated = $slug;
        do {
            $exists = OwnerCreditCoupon::where('couponcode', '=', $generated)->exists();
            if ($exists) $generated = $slug . rand(1, 1000);
        } while ($exists);

        return $generated;
    }
}
