<?php

namespace GPX\Model;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $coupon_id
 * @property int $transaction_id
 * @property bool $used
 */
class AutoCoupon extends Model {
    protected $table = 'wp_gpxAutoCoupon';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'user_id' => 'integer',
        'transaction_id' => 'integer',
        'coupon_id' => 'integer',
        'used' => 'boolean',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo( User::class, 'user_id', 'ID' );
    }

    public function coupon(): BelongsTo {
        return $this->belongsTo( Special::class, 'coupon_id', 'id' );
    }

    public function transaction(): BelongsTo {
        return $this->belongsTo( Transaction::class, 'transaction_id', 'id' );
    }

    public function scopeForUser( Builder $query, int|User|\WP_User $cid ): Builder {
        $cid = match ( true ) {
            $cid instanceof User => $cid->ID,
            $cid instanceof \WP_User => $cid->ID,
            default => $cid,
        };

        return $query->where( 'user_id', '=', $cid );
    }

    public function scopeWhereHash( Builder $query, string|array $hash ): Builder {
        $hash = Arr::wrap( $hash );
        return $query->whereIn( 'coupon_hash', $hash );
    }

    public function scopeUsed( Builder $query, bool $used = true ): Builder {
        return $query->where( 'used', '=', $used );
    }

    public function scopeForCoupon(Builder $query, int|Special $coupon) {
        $coupon_id = $coupon instanceof Special ? $coupon->id : $coupon;
        return $query->where( 'coupon_id', '=', $coupon_id );
    }

    public function scopeForTransaction(Builder $query, int|Transaction $transaction) {
        $transaction_id = $transaction instanceof Transaction ? $transaction->id : $transaction;
        return $query->where( 'transaction_id', '=', $transaction_id );
    }
}
