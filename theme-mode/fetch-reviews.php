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

    if (!empty($reviews)) {
        echo '<div class="google-reviews">';

        if ($avg_rating) {
            echo '<a href="LINK_TO_GOOGLE_BUSINESS_PROFILE" target="_blank" rel="noopener noreferrer" class="average-rating-badge">'; // <-- INSERT GOOGLE BUSINESS PROFILE LINK HERE
            echo '<span class="big-number">' . $avg_rating . '</span> ★ on Google</a>';
        }

        $i = 0;
        foreach ($reviews as $review) {
            if ($i >= 5) break;
            $extra_class = ($i >= 3) ? ' hidden-review' : '';
            echo '<div class="review' . $extra_class . '">';
            echo '<strong>' . esc_html($review['author_name']) . '</strong><br>';

            $timestamp = intval($review['time']);
            $date_string = date('F j, Y', $timestamp);
            $relative_time = $review['relative_time_description'] ?? '';
            $location = $review['author_url'] ?? null;

            echo '<div class="review-meta">';
            echo '<em>' . esc_html($relative_time) . ' (' . esc_html($date_string) . ')</em>';
            if ($location) {
                echo ' &nbsp;|&nbsp; <a href="' . esc_url($location) . '" target="_blank" class="review-location">Google profile</a>';
            }
            echo '</div>';

            echo '<span class="stars">';
            for ($s = 0; $s < 5; $s++) {
                echo '<svg class="star-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431L24 9.753l-6 5.847L19.336 24 12 20.125 4.664 24 6 15.6 0 9.753l8.332-1.735z"/></svg>';
            }
            echo '</span><br>';

            echo '<p>' . esc_html($review['text']) . '</p>';
            echo '</div>';
            $i++;
        }

        if ($i > 3) {
            echo '<button class="show-more-reviews">See More</button>';
        }

        echo '<a href="GOOGLE_BUSINESS_PROFILE_URL" target="_blank" rel="noopener noreferrer" class="see-all-reviews">See all reviews</a>'; // <-- INSERT GOOGLE BUSINESS PROFILE HERE

        // Attribution badge (required per license)
        echo '<div class="google-review-attribution">';
        echo 'Powered by <a href="https://github.com/SonShineRoofing/sonshine-google-reviews-shortcode" target="_blank" rel="noopener noreferrer">SonShine Google Reviews</a>';
        echo '</div>';

        echo '<br><p class="google-review-disclaimer">All reviews shown above are automatically pulled from Google using the official API.</p>';
        echo '</div>';
    } else {
        echo '<p>No 5-star reviews found.</p>';
    }

    return ob_get_clean();
}

add_shortcode('sonshine_google_reviews', 'sonshine_fetch_google_reviews');
