<?php

namespace GPX\Model;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class UnitType extends Model
{
    protected $table = 'wp_unit_type';
    protected $primaryKey = 'record_id';
    protected $guarded = [];

    protected $casts = [
        'record_id' => 'integer',
        'user' => 'integer',
        'resort_id' => 'integer',
        'weekId' => 'integer',
        'create_date' => 'datetime',
        'last_modified_date' => 'datetime',
    ];

    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'last_modified_date';

    public function weeks(  ) {
        return $this->hasMany(Week::class, 'unit_type', 'record_id');
    }

    public static function getNumberOfBedrooms(string $bedrooms = null): string {
        return match(true){
            Str::contains($bedrooms, '3', true) => '3',
            Str::contains($bedrooms, '2', true) => '2',
            Str::contains($bedrooms, '1', true) => '1',
            Str::contains($bedrooms, 'st', true) => 'studio',
            Str::contains($bedrooms, ['hotel','htl'], true) => 'hotel',
            default => mb_strtolower($bedrooms ?? ''),
        };
    }

    public function scopeByResort(Builder $query, int $resort_id): Builder {
        return $query->where('resort_id', $resort_id);
    }
}
