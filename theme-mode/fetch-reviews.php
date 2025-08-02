<?php
/**
 * Registers [sonshine_google_reviews] shortcode.
 * Fetches Google reviews and outputs styled HTML.
 * 
 * To use: Place this file in your theme folder and include it from functions.php
 */

function sonshine_fetch_google_reviews() {
    // 🔧 REPLACE with your actual API Key and Place ID
    $api_key   = 'YOUR_GOOGLE_API_KEY';
    $place_id  = 'YOUR_GOOGLE_PLACE_ID';
    $cache_file = get_stylesheet_directory() . '/google-reviews-cache.json';
    $cache_duration = 21600; // 6 hours

    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
        $cached = json_decode(file_get_contents($cache_file), true);
    } else {
        $response = wp_remote_get("https://maps.googleapis.com/maps/api/place/details/json?place_id=$place_id&fields=rating,reviews&key=$api_key");
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        $cached = [
            'reviews' => $data['result']['reviews'] ?? [],
            'avg_rating' => $data['result']['rating'] ?? null
        ];
        file_put_contents($cache_file, json_encode($cached));
    }

    $reviews = array_filter($cached['reviews'], function($r) {
        return isset($r['rating']) && intval($r['rating']) === 5;
    });

    $avg_rating = isset($cached['avg_rating']) ? round($cached['avg_rating'], 1) : null;

    ob_start();
    include get_stylesheet_directory() . '/theme-mode/layout-output.php';
    return ob_get_clean();
}

add_shortcode('sonshine_google_reviews', 'sonshine_fetch_google_reviews');
