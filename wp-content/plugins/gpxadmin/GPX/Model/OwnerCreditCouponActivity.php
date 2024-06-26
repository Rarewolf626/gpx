<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerCreditCouponActivity extends Model {
    protected $table = 'wp_gpxOwnerCreditCoupon_activity';
    protected $primaryKey = 'id';
    const CREATED_AT = 'datetime';
    const UPDATED_AT = null;
    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'couponID' => 'integer',
        'amount' => 'integer',
        'datetime' => 'timestamp',
        'xref' => 'integer',
        'userID' => 'integer',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'userID');
    }

    public function coupon(): BelongsTo {
        return $this->belongsTo(OwnerCreditCoupon::class, 'couponID');
    }

    public function transaction(): BelongsTo {
        return $this->belongsTo(Transaction::class, 'xref');
    }

    public function scopeByUser(Builder $query, User|int $user): Builder {
        $user_id = $user instanceof User ? $user->id : $user;
        return $query->where('wp_gpxOwnerCreditCoupon_activity.userID', '=', $user_id);
    }

    public function scopeByTransaction(Builder $query, Transaction|int $transaction): Builder {
        $transaction_id = $transaction instanceof Transaction ? $transaction->id : $transaction;
        return $query->where('wp_gpxOwnerCreditCoupon_activity.xref', '=', $transaction_id);
    }

    public function scopeByCoupon(Builder $query, OwnerCreditCoupon|int $coupon): Builder {
        $coupon_id = $coupon instanceof OwnerCreditCoupon ? $coupon->id : $coupon;
        return $query->where('wp_gpxOwnerCreditCoupon_activity.couponID', '=', $coupon_id);
    }

    public function scopeByActivity(Builder $query, string $activity): Builder {
        return $query->where('wp_gpxOwnerCreditCoupon_activity.activity', '=', $activity);
    }
}
