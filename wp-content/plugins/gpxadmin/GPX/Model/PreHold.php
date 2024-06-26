<?php

namespace GPX\Model;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $propertyID
 * @property int $weekId
 * @property int $user
 * @property int $lpid
 * @property string $weekType
 * @property ?array $data
 * @property Carbon $release_on
 * @property bool $released
 * @property User $theuser
 * @property Week $property
 * @property Week $week
 */
class PreHold extends Model {
    protected $table = 'wp_gpxPreHold';
    protected $guarded = [];
    public $timestamps = false;
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $casts = [
        'propertyID' => 'integer',
        'weekId' => 'integer',
        'user' => 'integer',
        'lpid' => 'integer',
        'data' => 'array',
        'released' => 'boolean',
        'release_on' => 'datetime',
    ];


    public function theuser() {
        return $this->belongsTo(User::class, 'user', 'ID');
    }

    public function property() {
        return $this->belongsTo(Week::class, 'propertyID', 'record_id');
    }

    public function week() {
        return $this->belongsTo(Week::class, 'weekId', 'record_id');
    }

    public function scopeReleased(Builder $query, bool $released = true): Builder {
        return $query->where('released', '=', $released);
    }

    public function scopeForUser(Builder $query, int $user_id): Builder {
        return $query->where('user', '=', $user_id);
    }

    public function scopeForWeek(Builder $query, int $week_id): Builder {
        return $query->where('weekId', '=', $week_id);
    }

    public function scopeOpen(Builder $query): Builder {
        return $query->where(fn($query) => $query
            ->released(false)
            ->whereRaw('release_on > NOW()')
        );
    }

    public function isExpired(): bool {
        if ($this->released) return true;

        return $this->release_on->isPast();
    }
}
