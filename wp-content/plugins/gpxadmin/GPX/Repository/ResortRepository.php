<?php

namespace GPX\Repository;

use GPX\Model\Enum\ResortPath;
use stdClass;
use GPX\Model\ResortMeta;
use Illuminate\Support\Arr;

class ResortRepository
{

    /**
     * @return RegionRepository
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function instance(): ResortRepository
    {
        return gpx(ResortRepository::class);
    }

    /**
     * @param int $id
     * @param object $location
     *
     * @return void
     */
    public function save_geodata(int $id, $location)
    {
        global $wpdb;

        if (is_object($location) and isset($location->lat) and isset($location->lng)) {
            $location_string = $location->lat . ',' . $location->lng;
        } else {
            return;
        }

        $sql = $wpdb->prepare("UPDATE wp_resorts
                                     SET `LatitudeLongitude` = %s, `latitude` = %f, `longitude` = %f, geocode_status = 1
                                     WHERE id  = %d ",
            $location_string,
            $location->lat,
            $location->lng,
            $id);
        $wpdb->query($sql);
    }


    public function save_geodata_error(int $id)
    {
        global $wpdb;

        $sql = $wpdb->prepare("UPDATE wp_resorts SET geocode_status = 0 WHERE id = %d ", $id);
        $wpdb->query($sql);

    }

    public function clear_geocode_status(int $id)
    {
        global $wpdb;

        $sql = $wpdb->prepare("UPDATE wp_resorts SET geocode_status = NULL WHERE id = %d ", $id);
        $wpdb->query($sql);
    }


    public function get_resort(int|string $id, string $field = 'id', string $path = null): ?stdClass
    {

        global $wpdb;
        $field = match ($field) {
            'id' => 'id',
            'name' => 'ResortName',
            'ResortName' => 'ResortName',
            'resort_id' => 'ResortID',
            'ResortID' => 'ResortID',
        };
        $sql = $wpdb->prepare("SELECT * FROM wp_resorts WHERE {$field} = %s", $id);
        $resort = $wpdb->get_row($sql, OBJECT);
        if (!$resort) {
            return null;
        }
        $resort->images = [];
        if ($resort->latitude && $resort->longitude) {
            $resort->maplink = sprintf("https://www.google.com/maps/place/%s,%s",
                $resort->latitude,
                $resort->longitude);
        } else {
            $resort->maplink = "https://maps.google.com/?q=" . rawurlencode($resort->Address1 . " " . $resort->Town . ", " . $resort->Region . " " . $resort->PostCode);
        }

        if (preg_match("|^https?://|", $resort->Website)) {
            $resort->url = $resort->Website;
            $resort->link = parse_url($resort->Website, PHP_URL_HOST);
        } elseif (!empty($resort->Website)) {
            $resort->url = 'https://' . $resort->Website;
            $resort->link = $resort->Website;
        } else {
            $resort->url = null;
            $resort->link = null;
        }
        //set the default images for the gallery
        for ($i = 1; $i < 4; $i++) {
            $ImagePath = 'ImagePath' . $i;
            if (!empty($resort->$ImagePath)) {
                $resort->images[] = [
                    'src' => str_replace("http://", "https://", $resort->$ImagePath),
                    'imageAlt' => strtolower($resort->ResortName),
                    'imageTitle' => $resort->ResortName,
                ];
            }
        }
        $meta = $this->get_resort_meta($resort->ResortID, [], $path);
        foreach ($meta as $key => $value) {
            $resort->$key = $value;
        }

        return $resort;
    }

    public function get_resort_meta(string $resort_id, array $fields = [], string $path = null): stdClass
    {
        $booking = $path === ResortPath::BOOKING;
        $profile = $path === ResortPath::PROFILE;
        global $wpdb;
        $sql = $wpdb->prepare("SELECT meta_key, meta_value FROM wp_resorts_meta WHERE ResortID=%s AND meta_key NOT IN
                                                                                ('AreaDescription', 'UnitDescription', 'AdditionalInfo',
                                                                                 'Description', 'Website', 'CheckInDays', 'CheckInEarliest',
                                                                                 'CheckInLatest', 'CheckOutEarliest', 'CheckOutLatest',
                                                                                 'Address1', 'Address2', 'Town', 'Region', 'Country', 'PostCode',
                                                                                 'Phone', 'Fax', 'Airport', 'Directions')", $resort_id);
        if (!empty($fields)) {
            $placeholders = gpx_db_placeholders($fields);
            $sql .= $wpdb->prepare(" AND meta_key IN ($placeholders)", $fields);
        }
        $results = $wpdb->get_results($sql, ARRAY_A);
        $meta = new stdClass();
        $now = time();
        $attributes = [
            'UnitFacilities',
            'ResortFacilities',
            'AreaFacilities',
            'resortConditions',
            'configuration',
            'CommonArea',
            'GuestRoom',
            'GuestBathroom',
            'UponRequest',
            'UnitConfig',
        ];
        foreach ($results as $row) {
            $key = $row['meta_key'];
            $value = json_decode($row['meta_value'], true);
            if (json_last_error()) {
                $meta->$key = $row['meta_value'];
                continue;
            }
            if ($key === 'images') {
                $value = array_map(function ($image) {
                    $img = [
                        'id' => $image['id'] ?? null,
                        'src' => $image['src'],
                        'imageAlt' => null,
                        'imageVideo' => null,
                        'imageTitle' => null,
                    ];
                    if (str_starts_with($image['src'], 'https://gpxvacations.com') && site_url() !== 'https://gpxvacations.com') {
                        $img['src'] = str_replace('https://gpxvacations.com', site_url(), $image['src']);
                    }
                    if ($image['type'] == 'uploaded') {
                        $img['imageAlt'] = get_post_meta($image['id'], '_wp_attachment_image_alt', true);
                        $img['imageVideo'] = get_post_meta($image['id'], 'gpx_image_video', true);
                        $img['imageTitle'] = get_the_title($image['id']);
                    }

                    return $img;
                }, $value ?? []);
                $meta->$key = $value;
                continue;
            }
            if (is_array($value)) {
                ksort($value);

                if ($key === 'AlertNote') {
                    $value = array_values(array_filter(Arr::map($value, function ($value, $date) use ($profile, $booking) {
                        $value = Arr::last(array_filter($value, function ($v) use ($profile, $booking, $date) {

                            if (is_array($v) && array_key_exists('desc', $v) && empty($v['desc'])) {
                                return false;
                            }
                            if (is_array($v) && array_key_exists('path', $v)) {
                                if ($booking && !$v['path']['booking']) {
                                    return false;
                                }
                                if ($profile && !$v['path']['profile']) {
                                    return false;
                                }
                            }
                            if (preg_match('/^(\d+)_(\d+)$/', $date, $dates)) {
                                $dates = [$dates[1], $dates[2]];
                            } else {
                                $dates = [$date];
                            }
                            if (isset($dates[0]) && $dates[0] > time()) {
                                // if the start date for the value is in the future, skip it
                                return false;
                            }
                            if (isset($dates[1]) && $dates[1] < time()) {
                                // if the end date for the value is in the past, skip it
                                return false;
                            }

                            return true;
                        }));
                        if (empty($value)) {
                            return null;
                        }

                        if (preg_match('/^(\d+)_(\d+)$/', $date, $dates)) {
                            $dates = [$dates[1], $dates[2]];
                        } else {
                            $dates = [$date];
                        }
                        return [
                            'desc' => stripslashes_from_strings_only(is_array($value) ? $value['desc'] : $value),
                            'date' => $dates,
                        ];
                    })));
                    $meta->$key = $value;
                    continue;
                }

                $value = array_filter($value, function ($v, $k) use ($now, $key) {
                    // has the start date for the value passed?
                    if (is_numeric($k) && $k > $now) {
                        return false;
                    }
                    if (preg_match('/^(\d+)_(\d+)$/', $k, $dates)) {
                        if ($dates[1] > $now) {
                            return false;
                        }
                        if ($dates[2] < $now) {
                            return false;
                        }

                        return true;
                    }

                    return true;
                }, ARRAY_FILTER_USE_BOTH);

                if (in_array($key, $attributes)) {
                    // it's an array
                    if (is_array(end($value))) {
                        $meta->$key = array_values(array_map('stripslashes_from_strings_only', end($value)));
                    }
                    // it's a string
                    if (is_string(end($value))) {
                        $meta->$key = stripslashes_from_strings_only(end($value));
                    }
                    continue;
                }

                $date = array_key_last($value);
                if (preg_match('/^(\d+)_(\d+)$/', $date, $dates)) {
                    $dates = [$dates[1], $dates[2]];
                } else {
                    $dates = [$date];
                }
                $value = $value[$date] ?? null;
                if (is_array($value)) {
                    if (isset($value['path'], $value['desc'])) {
                        array_unshift($value, [
                            'path' => $value['path'],
                            'desc' => stripslashes_from_strings_only($value['desc']),
                        ]);
                        unset($value['path'], $value['desc']);
                    }
                    if (Arr::isList($value)) {
                        $value = array_filter($value, function ($v) use ($profile, $booking, $now, $key) {
                            if (is_array($v) && array_key_exists('path', $v)) {
                                if ($profile && !$v['path']['profile']) {
                                    return false;
                                }
                                if ($booking && !$v['path']['booking']) {
                                    return false;
                                }
                            }

                            return true;
                        });
                        $value = end($value);
                        if (is_array($value) && array_key_exists('desc', $value)) {
                            $value = stripslashes_from_strings_only($value['desc']);
                        }
                    }
                }
            }
            $meta->$key = $value;
        }

        return $meta;
    }
}
