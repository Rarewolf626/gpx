<?php

namespace GPX\Repository;

use DB;
use GPX\Model\Region;
use DateTimeInterface;
use Illuminate\Support\Carbon;

class RegionRepository
{
    public static function instance(): RegionRepository
    {
        return gpx( RegionRepository::class );
    }

    public function tree(int $region_id = null)
    {
        if (empty($region_id)) return [];
        global $wpdb;
        $sql = $wpdb->prepare( "SELECT a.*
        FROM `wp_gpxRegion` a
        INNER JOIN wp_gpxRegion p ON (p.id = %d)
        WHERE (a.name != 'All') AND (a.`lft` >= p.`lft`) AND (a.`rght` <= p.`rght`)
        ORDER BY a.`lft` ASC",
                               $region_id );

        return $wpdb->get_results( $sql );
    }

    public function breadcrumbs(int $region_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare( "SELECT ancestor.*
            FROM `wp_gpxRegion` child, `wp_gpxRegion` ancestor
            WHERE child.lft >= ancestor.lft AND child.lft <= ancestor.rght
            AND child.id = %d
            ORDER BY ancestor.lft", $region_id);
        return $wpdb->get_results( $sql, OBJECT );
    }

    public function restricted(): array
    {
        global $wpdb;
        $sql = "SELECT a.id
        FROM `wp_gpxRegion` a
        INNER JOIN wp_gpxRegion p ON (p.name = 'Southern Coast (California)')
        WHERE a.`lft` BETWEEN p.`lft` AND p.`rght`
        ORDER BY a.`lft` ASC";
        $regions = $wpdb->get_col( $sql );

        return array_combine( $regions, $regions );
    }

    public function is_restricted( int $region_id, $checkin = null ): bool {
        static $restricted;
        if ( ! $restricted ) {
            $restricted = $this->restricted();
        }
        if ( ! in_array( $region_id, $restricted ) ) {
            return false;
        }

        return static::is_date_restricted( $checkin );
    }

    public static function is_date_restricted( $checkin = null ): bool {
        if ( ! $checkin ) {
            return false;
        }
        if ( $checkin instanceof DateTimeInterface ) {
            $date = Carbon::instance( $checkin );
        } elseif ( is_numeric( $checkin ) ) {
            $date = Carbon::createFromTimestamp( $checkin );
        } elseif ( is_string( $checkin ) ) {
            $date = Carbon::parse( $checkin );
            if ( ! $date->isValid() ) {
                return false;
            }
        } else {
            return false;
        }
        $start = $date->clone()->setMonth( 6 )->setDay( 1 )->format( 'Y-m-d' );
        $end   = $date->clone()->setMonth( 9 )->setDay( 1 )->format( 'Y-m-d' );
        $date  = $date->format( 'Y-m-d' );

        return $date >= $start && $date < $end;
    }

    /**
     * @param $regionname
     *
     * @return mixed
     */
    public function get_region_id($regionname = null)
    {
        global $wpdb;
        if (empty($regionname)) return null;

        $sql    = $wpdb->prepare( "SELECT id
                                     FROM `wp_gpxRegion`
                                     WHERE `name` = '%s'",
                                  $regionname );
        $region = $wpdb->get_results( $sql );

        return $region[0]->id;
    }

    /**
     * @param $regionid
     *
     * @return mixed
     */
    public function get_region_name($regionid = null)
    {
        global $wpdb;
        if (empty($regionid)) return null;

        $sql    = $wpdb->prepare( "SELECT name
                                     FROM `wp_gpxRegion`
                                     WHERE id = %d",
                                  $regionid );
        $region = $wpdb->get_results( $sql );

        return $region[0]->name;
    }

    public function findRegion(string $name = null): ?Region
    {
        if (empty($name)) return null;
        $category = DB::table('wp_gpxCategory')
            ->select('countryID')
            ->where('country', '=', $name)
            ->where('CountryID', '<', 1000)
            ->take(1)
            ->first();

        if ($category) {
            $region = Region::query()
                ->select(['wp_gpxRegion.*'])
                ->join('wp_daeRegion as b', 'wp_gpxRegion.RegionID', '=', 'b.id')
                ->where('b.CategoryID', '=', $category->countryID)
                ->active()
                ->take(1)
                ->first();
            if ($region) return $region;
        }

        return Region::active()
            ->byName($name)
            ->take(1)
            ->first();
    }

}
