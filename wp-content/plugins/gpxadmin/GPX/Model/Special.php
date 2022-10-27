<?php

namespace GPX\Model;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    public function scopeActive( Builder $query, bool $active = true ): Builder {
        return $query->where('Active', '=', $active);
    }

    public function getTransactionTypeAttribute(  ): ?string {
        $properties = $this->Properties;
        if(empty($properties['transactionType'])) return null;
        $type = array_filter(Arr::wrap($properties['transactionType']));
        if(empty($type)) return null;
        if(count($type) === 1) return Arr::first($type);
        return implode(', ',$type);
    }
}
