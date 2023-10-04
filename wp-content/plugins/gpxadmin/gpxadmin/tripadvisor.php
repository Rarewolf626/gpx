<?php

use GPX\Api\TripAdvisor\TripAdvisor;
use GPX\Model\Resort;

function gpx_get_tripadvisor_locations()
{
    if (!gpx_is_administrator()) {
        wp_send_json(['success' => false, 'message' => 'Access denied.'], 403);
    }
    $coords = gpx_request('coords');
    if (empty($coords)) {
        wp_send_json(['success' => false, 'message' => 'No coordinates provided.'], 422);
    }
    $resort_id = gpx_request('resort_id');
    if (empty($resort_id)) {
        wp_send_json(['success' => false, 'message' => 'No resort provided.'], 422);
    }

    $tripadvisor = TripAdvisor::instance();
    try {
        $locations = $tripadvisor->location_mapper($coords);
    } catch (Exception $e) {
        wp_send_json(['success' => false, 'message' => 'Did not find any locations at the given coordinates.'], 200);
    }
    if (empty($locations->data)) {
        wp_send_json(['success' => false, 'message' => 'Did not find any locations at the given coordinates.'], 200);
    }

    wp_send_json(['success' => true, 'locations' => array_map(fn($location) => [
        'location_id' => $location->location_id,
        'name' => $location->name,
        'address' => $location->address_obj->address_string,
        'coords' => $coords,
        'resort' => $resort_id,
    ], $locations->data)]);
}

add_action('wp_ajax_gpx_get_tripadvisor_locations', 'gpx_get_tripadvisor_locations');

function gpx_set_tripadvisor_location()
{
    if (!gpx_is_administrator()) {
        wp_send_json(['success' => false, 'message' => 'Access denied.'], 403);
    }
    $resort_id = gpx_request('resort_id');
    if (empty($resort_id)) {
        wp_send_json(['success' => false, 'message' => 'No resort provided.'], 422);
    }
    $location_id = gpx_request('location_id');
    if (empty($location_id)) {
        wp_send_json(['success' => false, 'message' => 'No location provided.'], 422);
    }
    $resort = Resort::find($resort_id);
    if (!$resort) {
        wp_send_json(['success' => false, 'message' => 'Resort not found.'], 422);
    }
    $tripadvisor = TripAdvisor::instance();
    try {
        $location = $tripadvisor->location($location_id);
    } catch (Exception $e) {
        wp_send_json(['success' => false, 'message' => 'Not a valid Trip Advisor location.'], 422);
    }

    $resort->update(['taID' => $location->location_id]);

    wp_send_json(['success' => true, 'message' => 'TripAdvisor ID Updated!', 'location_id' => $resort->taID]);
}

add_action('wp_ajax_gpx_set_tripadvisor_location', 'gpx_set_tripadvisor_location');

add_shortcode('tripadvisor-widget', function ($atts) {
    $atts = shortcode_atts(array(
        'id' => null,
        'resort' => null,
    ), $atts);
    if (empty($atts['id']) || $atts['id'] == 1) return '';
    $ta = TripAdvisor::instance();
    try {
        $tripadvisor = $ta->location($atts['id']);
        if (isset($tripadvisor->num_reviews)) {
            $reviews = (int)$tripadvisor->num_reviews;
        } else {
            $reviews = array_sum((array)$tripadvisor->review_rating_count);
        }
        if (isset($tripadvisor->rating)) {
            $stars = (float)$tripadvisor->rating;
        } else {
            $totalstars = array_reduce(array_keys((array)$tripadvisor->review_rating_count), fn($total, $key) => $total + ($key * $tripadvisor->review_rating_count->$key), 0);
            $stars = $reviews > 0 ? round(number_format($totalstars / $reviews, 1, '.', '') * 2) / 2 : 0;
        }
        $taURL = $tripadvisor->web_url;

        return gpx_theme_template_part('resort-tripadvisor-widget', [
            'resort' => $atts['resort'],
            'taURL' => $taURL,
            'stars' => $stars,
            'reviews' => $reviews,
        ], false);
    } catch (Exception $e) {
        return '';
    }
});
