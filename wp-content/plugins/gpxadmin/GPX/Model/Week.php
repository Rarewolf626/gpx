<?php

namespace GPX\Model;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $record_id
 * @property float $price
 * @property int $resort
 * @property Resort $theresort
 * @property Carbon $check_in_date
 * @property Carbon $check_out_date
 * @property Carbon $active_specific_date
 * @property array $update_details
 */
class Week extends Model {
    protected $table = 'wp_room';
    protected $primaryKey = 'record_id';

    protected $guarded = [];
    public const CREATED_AT = 'create_date';
    public const UPDATED_AT = 'last_modified_date';

    protected $casts = [
        'record_id' => 'integer',
        'create_date' => 'datetime',
        'active_specific_date' => 'datetime',
        'last_modified_date' => 'datetime',
        'check_in_date' => 'datetime',
        'check_out_date' => 'datetime',
        'resort' => 'integer',
        'unit_type' => 'integer',
        'source_num' => 'integer',
        'source_partner_id' => 'integer',
        'sourced_by_partner_on' => 'datetime',
        'active' => 'boolean',
        'availablity' => 'boolean',
        'available_to_partner_id' => 'integer',
        'type' => 'integer',
        'active_rental_push_date' => 'date',
        'price' => 'float',
        'points' => 'float',
        'given_to_partner_id' => 'integer',
        'import_id' => 'integer',
        'active_week_month' => 'integer',
        'create_by' => 'integer',
        'archived' => 'integer',
        'booked_status' => 'integer',
    ];

    public function unit(): BelongsTo {
        return $this->belongsTo(UnitType::class, 'unit_type', 'record_id');
    }

    public function theresort(): BelongsTo {
        return $this->belongsTo(Resort::class, 'resort', 'id');
    }

    public function holds(): HasMany {
        return $this->hasMany(PreHold::class, 'propertyID', 'record_id');
    }

    public function partner(): BelongsTo {
        return $this->belongsTo(Partner::class, 'source_partner_id', 'user_id');
    }

    public function available_partner(): BelongsTo {
        return $this->belongsTo(Partner::class, 'available_to_partner_id', 'user_id');
    }

    public function getRoomTypeAttribute() {
        switch ($this->type) {
            case 1:
                return 'Exchange';
            case 2:
                return 'Rental';
            case 3:
                return 'Exchange/Rental';
            default:
                return '--';
        }
    }

    public function getNoNightsAttribute() {
        return $this->check_in_date->diffInDays($this->check_out_date, true);
    }

    public function getUpdateDetailsAttribute($value) {
        if (empty($value)) {
            return null;
        }
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (empty($value)) {
            return null;
        }

        return array_map(function ($record) {
            if (isset($record['details'])) {
                if (is_string($record['details'])) {
                    if (!empty($record['details'])) {
                        $details = json_decode($record['details'], true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $details = json_decode(base64_decode($record['details']), true);
                        }
                        $record['details'] = $details;
                    }
                }

                return $record;
            }
        }, $value);
    }

    public function isBooked(): bool {
        return Transaction::forWeek($this->record_id)->cancelled(false)->exists();
    }

    public function isHeld(): bool {
        return PreHold::forWeek($this->record_id)->released(false)->exists();
    }

    public function getStatus(): string {
        if ($this->archived) {
            return 'Archived';
        }
        if ($this->active) {
            return 'Available';
        }
        if ($this->isBooked()) {
            return 'Booked';
        }
        if ($this->isHeld()) {
            return 'Held';
        }

        return 'Available';
    }

    public function getUpdateHistory(): Collection {
        $users = [];

        return collect($this->update_details)
            ->map(function ($record, $timestamp) use (&$users) {
                if (!isset($users[$record['update_by']])) {
                    $users[$record['update_by']] = UserMeta::load($record['update_by']);
                }

                return [
                    'date' => Carbon::createFromTimestamp($timestamp),
                    'user_id' => $record['update_by'],
                    'user' => $users[$record['update_by']]->getName(),
                    'details' => collect($record['details'])
                        ->filter(function ($up, $uk) {
                            if ($uk != 'room_archived' && (empty($up['old']) && empty($up['new']))) {
                                return false;
                            }

                            return true;
                        }),
                ];
            })
            ->values();
    }

    public function scopeActive(Builder $query, bool $active = true): Builder {
        return $query->where('active', '=', $active);
    }

    public function scopeArchived(Builder $query, bool $archived = true): Builder {
        return $query->where('archived', '=', $archived);
    }

    public function scopeIgnoreTestRecords(Builder $query): Builder {
        return $query->where('active_rental_push_date', '!=', '2030-01-01');
    }
}
