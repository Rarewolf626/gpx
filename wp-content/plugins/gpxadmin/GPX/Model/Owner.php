<?php


namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Owner extends Model
{
    protected $table = 'wp_GPR_Owner_ID__c';
    protected $primaryKey = 'id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $guarded = [];

    protected $casts = [
        'Name' => 'integer',
        'id' => 'integer',
        'user_id' => 'integer',
        'welcome_email_sent' => 'boolean',
        'created_date' => 'timestamp',
        'updated_date' => 'timestamp',
    ];

    public function user(  ): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    public function scopeBySalesforceId( Builder $query, string $name ): Builder {
        return $query->where('Name', '=', $name);
    }
}
