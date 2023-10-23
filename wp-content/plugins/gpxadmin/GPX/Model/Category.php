<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'wp_gpxCategory';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
    protected $casts = [
        'CountryID' => 'integer',
        'newCountryID' => 'integer',
        'reassigned' => 'boolean',
    ];
}
