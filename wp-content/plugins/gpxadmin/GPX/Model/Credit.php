<?php

namespace GPX\Model;

use DB;
use DateTimeInterface;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Credit extends Model {
    protected $table = 'wp_credit';
    protected $primaryKey = 'id';
    protected $guarded = [];
    const CREATED_AT = 'created_date';
    const UPDATED_AT = null;

    protected $casts = [
        'created_date' => 'datetime',
        'credit_amount' => 'integer',
        'credit_expiration_date' => 'date',
        'interval_number' => 'integer',
        'deposit_year' => 'integer',
        'transaction_type' => 'integer',
        'week_type' => 'integer',
        'check_in_date' => 'date',
        'owner_id' => 'integer',
        'extension_date' => 'date',
        'modification_id' => 'integer',
        'credit_used' => 'integer',
        'modified_date' => 'date',
    ];

    public function deposit() {
        return $this->hasOne(DepositOnExchange::class, 'creditID', 'id');
    }

    public function interval() {
        return $this->belongsTo(Interval::class, 'interval_number', 'contractID');
    }

    public function getCreditsAttribute() {
        return (int) $this->credit_amount - (int) $this->credit_used;
    }

    public function scopeForUser(Builder $query, int $cid): Builder {
        return $query->where('owner_id', '=', $cid);
    }

    public function scopeExpired(Builder $query): Builder {
        return $query->where(fn($query) => $query
            ->whereNotNull('credit_expiration_date')
            ->whereRaw('credit_expiration_date < CURRENT_DATE()')
        );
    }

    public function scopeNotExpired(Builder $query, DateTimeInterface $date = null): Builder {
        return $query->where(fn($query) => $query
            ->whereNull('credit_expiration_date')
            ->orWhereDate('credit_expiration_date', '>=', $date ? $date->format('Y-m-d') : DB::raw('CURRENT_DATE()'))
        );
    }

    public function scopeUsed(Builder $query): Builder {
        return $query->whereRaw('IFNULL(credit_amount,0) - IFNULL(credit_used,0) <= 0');
    }

    public function scopeNotUsed(Builder $query): Builder {
        return $query->whereRaw('IFNULL(credit_amount,0) - IFNULL(credit_used,0) > 0');
    }

    public function scopeStatus(Builder $query, string $status): Builder {
        return $query->where('status', '=', $status);
    }

    public function scopeNotStatus(Builder $query, string $status): Builder {
        return $query->where('status', '!=', $status);
    }

    public function scopeApproved(Builder $query): Builder {
        return $query->status('Approved');
    }

    public function scopeNotApproved(Builder $query): Builder {
        return $query->notStatus('Approved');
    }

    public function scopeAction(Builder $query, string $action): Builder {
        return $query->where('credit_action', '=', $action);
    }

    public function scopeNotAction(Builder $query, string $action): Builder {
        return $query->where('credit_action', '!=', $action);
    }

    public function scopeTransfered(Builder $query): Builder {
        return $query->action('transferred');
    }

    public function scopeNotTransfered(Builder $query): Builder {
        return $query->notAction('transferred');
    }

    public function scopeDoe(Builder $query): Builder {
        return $query->where('status', '=', 'DOE');
    }

    public function scopeHasExpiration(Builder $query): Builder {
        return $query->whereNotNull('credit_expiration_date');
    }

    public function isExpired(DateTimeInterface|string $date = null): bool {
        if (null === $this->credit_expiration_date) return true;
        if (null === $date) $date = date_create();
        if (is_string($date)) $date = date_create($date);
        if (!$date) return false;

        return $date->format('Y-m-d') > $this->credit_expiration_date->format('Y-m-d');
    }

    public function getCreditBedrooms(): string {
        return UnitType::getNumberOfBedrooms($this->unit_type);
    }

    public static function calculateUpgradeFee(string $beds, string $creditbed, int $resort = null): int {
        $beds = UnitType::getNumberOfBedrooms($beds);
        $num_rooms_credit = UnitType::getNumberOfBedrooms($creditbed);

        // R0212 is the ResortID of Carlsbad Inn Beach Resort
        $is_cbi = $resort && Resort::where('ResortID', '=', 'R0212')->where('id', '=', $resort)->exists();
        if ($is_cbi && preg_match("/^1b.?6$/i", $creditbed)) {
            // This is only the case at Carlsbad Inn Beach Resort.
            // Owners who have a 1 Bedroom Sleeps 6 unit type can upgrade to a 2 bedroom with no upgrade fee.
            if ($beds == '2') {
                return 0;
            }
        }
        if ($num_rooms_credit == 'studio') {
            return match (true) {
                Str::contains($beds, ['st', 'htl'], true) => 0,
                Str::contains($beds, '1', true) => 85,
                default => 185,
            };
        }
        if ($num_rooms_credit == 'hotel') {
            return match (true) {
                Str::contains($beds, ['st', 'htl'], true) => 0,
                Str::contains($beds, '1', true) => 85,
                default => 185,
            };
        }
        if ($num_rooms_credit == '1') {
            return match (true) {
                Str::contains($beds, ['st', '1'], true) => 0,
                default => 185,
            };
        }
        if ($num_rooms_credit == '2') {
            return match (true) {
                Str::contains($beds, ['st', 'htl', '1', '2'], true) => 0,
                default => 185,
            };
        }

        return 0;
    }

    public function isDepositOnExchange(): bool {
        return $this->status === 'DOE';
    }

    public function isApproved(): bool {
        return $this->status === 'Approved';
    }

    public function isDenied(): bool {
        return $this->status === 'Denied';
    }

    public function hasStatus(string $status): bool {
        return $this->status === $status;
    }
}
