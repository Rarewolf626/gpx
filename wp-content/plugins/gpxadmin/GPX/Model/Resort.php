<?php

namespace GPX\Model;

use Illuminate\Support\Carbon;
use GPX\Model\Trait\HasResortFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use GPX\Model\ValueObject\Admin\Resort\ResortSearch;

/**
 * @property int $id
 * @property string $ResortID
 * @property ?string $gprID
 * @property ?string $sf_GPX_Resort__c
 * @property ?string $EndpointID
 * @property string $ResortName
 * @property string $search_name
 * @property ?string $WebLink
 * @property ?string $AlertNote
 * @property ?string $Address1
 * @property ?string $Address2
 * @property ?string $Town
 * @property ?string $Region
 * @property ?string $Country
 * @property ?string $PostCode
 * @property ?string $Phone
 * @property ?string $Fax
 * @property ?string $Email
 * @property ?array $UnitFacilities
 * @property ?array $ResortFacilities
 * @property ?array $AreaFacilities
 * @property ?string $ImagePath1
 * @property ?string $ImagePath2
 * @property ?string $ImagePath3
 * @property ?string $Website
 * @property ?string $AreaDescription
 * @property ?string $UnitDescription
 * @property ?string $Airport
 * @property ?string $Directions
 * @property ?string $CheckInDays
 * @property string $CheckInEarliest
 * @property string $CheckInLatest
 * @property string $CheckOutEarliest
 * @property string $CheckOutLatest
 * @property string $AdditionalInfo
 * @property string $Description
 * @property array $UnitConfig
 * @property string $LatitudeLongitude
 * @property ?float $latitude
 * @property ?float $longitude
 * @property string $VideoURL
 * @property string $DisabledNotes
 * @property string $HTMLAlertNotes
 * @property Carbon $lastUpdate
 * @property int $gpxRegionID
 * @property string $resortPromo
 * @property bool $featured
 * @property bool $gpr
 * @property bool $guestFeesEnabled
 * @property ?float $resort_fee
 * @property bool $store_d
 * @property int $HolidayPropMsg
 * @property bool $ai
 * @property int $taxMethod
 * @property int $taxID
 * @property int $taID
 * @property bool $active
 * @property ?int $geocode_status
 */
class Resort extends Model implements Addressable
{
    use HasResortFields;

    protected $table = 'wp_resorts';
    protected $primaryKey = 'id';
    protected $guarded = [];

    protected $casts = [
        'lastUpdate' => 'datetime',
        'gpxRegionID' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'featured' => 'boolean',
        'gpr' => 'boolean',
        'guestFeesEnabled' => 'boolean',
        'third_party_deposit_fee_enabled' => 'boolean',
        'resort_fee' => 'float',
        'store_d' => 'boolean',
        'ai' => 'boolean',
        'taxMethod' => 'integer',
        'taxID' => 'integer',
        'taID' => 'integer',
        'active' => 'boolean',
        'geocode_status' => 'integer',

    ];

    const CREATED_AT = null;
    const UPDATED_AT = 'lastUpdate';

    public function toAddress(  ): Address {
        return (new Address($this->only([
            'Address1', 'Address2', 'Town', 'Region', 'PostCode', 'Country', 'Phone', 'Fax', 'Email'
        ])));
    }


    public static function findByResortId(string $resort_id): ?Resort
    {
        return Resort::byResortId($resort_id)->first();
    }

    public function scopeByResortId(Builder $query, string $resort_id): Builder
    {
        return $query->where('ResortID', $resort_id);
    }

    public function scopeActive(Builder $query, bool $active = true): Builder {
        return $query->where('active', $active);
    }

    public function scopeFeatured(Builder $query, bool $featured = true): Builder {
        return $query->where('featured', $featured);
    }

    public function scopeAdminSearch(Builder $query, ResortSearch $search): Builder {
        return $query
            ->when($search->id !== '', fn($query) => $query->where('id', 'LIKE', $search->id . '%'))
            ->when($search->resort !== '', fn($query) => $query->where('ResortName', 'LIKE', '%' . $search->resort . '%'))
            ->when($search->city !== '', fn($query) => $query->where('Town', 'LIKE', '%' . $search->city . '%'))
            ->when($search->region !== '', fn($query) => $query->where('Region', 'LIKE', '%' . $search->region . '%'))
            ->when($search->country !== '', fn($query) => $query->where('Country', 'LIKE', '%' . $search->country . '%'))
            ->when($search->trip_advisor !== '', fn($query) => $query->where('taID', 'LIKE', $search->trip_advisor . '%'))
            ->when($search->ai === 'yes', fn($query) => $query->where('ai', '=', 1))
            ->when($search->ai === 'no', fn($query) => $query->where('ai', '=', 0))
            ->when($search->active === 'yes', fn($query) => $query->where('active', '=', 1))
            ->when($search->active === 'no', fn($query) => $query->where('active', '=', 0))
            ->when($search->sort !== 'id', fn($query) => $query
                ->orderBy(match ($search->sort) {
                    'resort' => 'ResortName',
                    'city' => 'Town',
                    'region' => 'Region',
                    'country' => 'Country',
                    'trip_advisor' => 'taID',
                    'ai' => 'ai',
                    'active' => 'active',
                    default => 'id',
                }, $search->dir)
            )
            ->orderBy('id', $search->dir)
            ;
    }
}
