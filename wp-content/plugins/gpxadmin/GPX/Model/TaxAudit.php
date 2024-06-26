<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;

class TaxAudit extends Model {
    protected $table = 'wp_gpxTaxAudit';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'transactionDate' => 'datetime',
        'arrivalDate' => 'date',
        'gpxTaxID' => 'integer',
        'emsID' => 'integer',
        'baseAmount' => 'float',
        'taxAmount' => 'float',
    ];
}
