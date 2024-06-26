<?php

namespace GPX\Repository;

use GPX\Model\Week;
use GPX\Model\Special;
use Illuminate\Support\Collection;

class SpecialsRepository {
    public static function instance(): SpecialsRepository {
        return gpx(SpecialsRepository::class);
    }

    public function get_promos_for_week(Week $week): \Illuminate\Database\Eloquent\Collection {
        $week->loadMissing('theresort');
        $checkin = $week->check_in_date->format('Y-m-d');

        return Special::select([
            'wp_specials.id',
            'wp_specials.Name',
            'wp_specials.Slug',
            'wp_specials.Properties',
            'wp_specials.Amount',
            'wp_specials.SpecUsage',
            'wp_specials.PromoType',
            'wp_specials.master',
        ])
                      ->leftJoin('wp_promo_meta', 'wp_promo_meta.specialsID', '=', 'wp_specials.id')
                      ->where(fn($query) => $query
                          ->orWhere('SpecUsage', '=', 'any')
                          ->orWhere('SpecUsage', 'LIKE', '%customer%')
                          ->orWhere('SpecUsage', 'LIKE', '%region%')
                          ->orWhere(fn($query) => $query
                              ->orWhere(fn($query) => $query
                                  ->where('wp_promo_meta.foreignID', '=', $week->resort)
                                  ->where('wp_promo_meta.refTable', '=', 'wp_resorts')
                              )
                              ->orWhere(fn($query) => $query
                                  ->where('wp_promo_meta.foreignID', '=', $week->theresort->gpxRegionID)
                                  ->where('wp_promo_meta.refTable', '=', 'wp_gpxRegion')
                              )
                          )
                      )
                      ->where(fn($query) => $query
                          ->whereDate('wp_specials.TravelStartDate', '<=', $checkin)
                          ->whereDate('wp_specials.TravelEndDate', '>=', $checkin)
                      )
                      ->current()
                      ->type('promo')
                      ->active()
                      ->get();
    }

    function get_specials_for_promo(string $code = null): Collection {
        if (empty($code)) {
            return new Collection();
        }
        $special = Special::active()
                          ->current()
                          ->slug($code)
                          ->first();

        return Special::active()
                      ->current()
                      ->where(fn($query) => $query
                          ->where('id', $special->id)
                          ->orWhere('master', $special->id)
                      )
                      ->get();

    }
}
