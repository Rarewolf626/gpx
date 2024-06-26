<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $ID
 * @property string $TaxAuthority
 * @property string $City
 * @property string $State
 * @property string $Country
 * @property-read float $total_percent
 * @property-read float $total_flat
 * @property float $TaxPercent1
 * @property float $TaxPercent2
 * @property float $TaxPercent3
 * @property float $FlatTax1
 * @property float $FlatTax2
 * @property float $tFlatTax3
 */
class TaxRate extends Model {
    protected $table = 'wp_gpxTaxes';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'ID' => 'integer',
        'TaxPercent1' => 'float',
        'TaxPercent2' => 'float',
        'TaxPercent3' => 'float',
        'FlatTax1' => 'float',
        'FlatTax2' => 'float',
        'FlatTax3' => 'float',
    ];

    public function getTotalPercentAttribute(): float {
        return array_sum( [ $this->TaxPercent1, $this->TaxPercent2, $this->TaxPercent3 ] );
    }

    public function getTotalFlatAttribute(): float {
        return array_sum( [ $this->FlatTax1, $this->FlatTax2, $this->FlatTax3 ] );
    }

    public function amount(int|float $price): float {
        $amount = 0.00;
        $percent = $this->total_percent;
        $flat = $this->total_flat;
        if ($percent > 0) {
            $amount += $price * ($percent / 100);
        }
        if ($flat > 0) {
            $amount += $flat;
        }
        return $amount;
    }
}
