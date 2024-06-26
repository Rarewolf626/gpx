<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $table = 'wp_cart';
    protected $primaryKey = 'id';
    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'user' => 'integer',
        'propertyID' => 'integer',
        'weekId' => 'integer',
        'datetime' => 'timestamp',
    ];
    const CREATED_AT = 'datetime';
    const UPDATED_AT = null;

    public function owner(  ): BelongsTo {
        return $this->belongsTo(User::class, 'user', 'ID');
    }

    public function week(): BelongsTo {
        return $this->belongsTo(Week::class, 'weekId', 'record_id');
    }

    public function property(): BelongsTo {
        return $this->belongsTo(Week::class, 'propertyID', 'record_id');
    }

    public function isExpired(): bool {
        return $this->datetime->clone()->subDay()->isPast();
    }
}
