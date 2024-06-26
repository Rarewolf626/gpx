<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedeemedCoupon extends Model {
    protected $table = 'wp_redeemedCoupons';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'id' => 'integer',
        'userID' => 'integer',
        'specialID' => 'integer',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo( User::class, 'userID', 'ID' );
    }

    public function coupon(): BelongsTo {
        return $this->belongsTo( Special::class, 'specialID', 'id' );
    }

    public function scopeForUser( Builder $query, int $userID ): Builder {
        return $query->where( 'userID', $userID );
    }

    public function scopeForCoupon( Builder $query, int $couponID ): Builder {
        return $query->where( 'specialID', $couponID );
    }
}
