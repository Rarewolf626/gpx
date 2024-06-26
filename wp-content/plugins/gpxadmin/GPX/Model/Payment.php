<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
    protected $table = 'wp_payments';
    protected $primaryKey = 'id';

    protected $guarded = [];
    public $timestamps = false;
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $casts = [
        'id' => 'integer',
        'invoice_id' => 'integer',
        'transactionID' => 'integer',
        'userID' => 'integer',
        'i4go_responsecode' => 'integer',
        'i4go_postalcode' => 'integer',
        'i4go_expirationmonth' => 'integer',
        'i4go_expirationyear' => 'integer',
        'i4go_object' => 'array',
    ];

    public function user() {
        return $this->belongsTo( User::class, 'userID', 'ID' );
    }

    public function cart() {
        return $this->belongsTo( Cart::class, 'cartID', 'cartID' );
    }

    protected static function booted() {
        static::saved( function ( Payment $model ) {
            if ( $model->invoice_id === null ) {
                $model->invoice_id = $model->id;
                $model->save();
            }
        } );
    }
}
