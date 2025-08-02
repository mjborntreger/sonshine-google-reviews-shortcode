<?php
/**
 * Plugin Name: SonShine Google Reviews Shortcode
 * Description: Displays filtered 5-star Google reviews using the Places API. Caches results and supports optional attribution and count controls.
 * Version: 1.0
 * Author: SonShine Roofing
 * Author URI: https://sonshineroofing.com
 * License: SonShine Roofing License v1.0
 */

function sonshine_fetch_google_reviews($atts = []) {
    // ========== USER SETTINGS ==========
    $api_key     = 'PLACES_API_KEY'; // <-- INSERT YOUR GOOGLE PLACES API KEY
    $place_id    = 'PLACE_ID';       // <-- INSERT YOUR GOOGLE PLACE ID
    $profile_url = 'https://www.google.com/maps/place/SonShine+Roofing'; // <-- UPDATE TO YOUR PUBLIC GBP URL

    // ========== SHORTCODE ATTRIBUTES ==========
    $atts = shortcode_atts([
        'count' => 5
    ], $atts);
    $max_reviews = intval($atts['count']);

    // ========== CACHING ==========
    $cache_file = sanitize_file_name(get_stylesheet_directory() . '/google-reviews-cache.json');
    $cache_duration = 21600; // 6 hours

    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
        $cached = json_decode(file_get_contents($cache_file), true);
    } else {
        $response = wp_remote_get("https://maps.googleapis.com/maps/api/place/details/json?place_id=$place_id&fields=rating,reviews&key=$api_key");
        if (is_wp_error($response)) return '<p>Unable to retrieve Google reviews at this time.</p>';
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['result'])) return '<p>Google Reviews API returned no data.</p>';

        $cached = [
            'reviews' => $data['result']['reviews'] ?? [],
            'avg_rating' => $data['result']['rating'] ?? null
        ];
        file_put_contents($cache_file, json_encode($cached));
    }

    // ========== PROCESS ==========
    $reviews = array_filter($cached['reviews'], function($r) {
        return isset($r['rating']) && intval($r['rating']) === 5;
    });

    $avg_rating = isset($cached['avg_rating']) ? round($cached['avg_rating'], 1) : null;

    ob_start();
    ?>
    <style>
    .google-reviews {
      margin: 10px 0;
      font-family: Arial, sans-serif;
      font-size: 22px;
      position: relative;
      z-index: 5;
    }
    .google-reviews .average-rating-badge {
      background-color: #fb9216;
      color: #fff;
      font-weight: bold;
      padding: 10px 20px;
      border-radius: 50px;
      display: inline-block;
      font-size: 18px;
      margin-bottom: 20px;
    }
    .google-reviews .average-rating-badge .big-number {
      font-size: 2em;
      margin-right: 6px;
      vertical-align: middle;
    }
    .google-reviews .review {
      margin-bottom: 20px;
      transition: opacity 0.4s ease-in-out;
    }
    .google-reviews .stars {
      display: flex;
      gap: 4px;
      margin-bottom: 6px;
    }
    .google-reviews .star-icon {
      width: 18px;
      height: 18px;
      fill: #fb9216;
      transition: transform 0.3s ease;
    }
    .google-reviews .hidden-review {
      display: none;
    }
    .google-reviews .show-more-reviews,
    .google-reviews .see-all-reviews {
      background-color: #0045d7;
      color: white;
      border: none;
      padding: 10px 16px;
      margin-top: 10px;
      margin-right: 10px;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      display: inline-block;
      text-decoration: none;
    }
    .google-reviews .see-all-reviews {
      background-color: #00e3fe;
      color: #000;
    }
    .google-review-disclaimer {
      font-size: 0.75em;
      color: #666;
      font-style: italic;
      margin-top: 10px;
      line-height: 1.4;
    }
    .google-reviews .review-meta {
      font-size: 0.8em;
      color: #555;
      margin-bottom: 6px;
    }
    .google-reviews .review-location {
      color: #0045d7;
      text-decoration: underline;
      font-style: italic;
    }
    .google-review-attribution {
      font-size: 0.7em;
      text-align: right;
      margin-top: 10px;
      opacity: 0.6;
    }
    .google-review-attribution a {
      color: #555;
      text-decoration: none;
    }
    .google-review-attribution a:hover {
      text-decoration: underline;
    }
    </style>
    <?php

    if (!empty($reviews)) {
        echo '<div class="google-reviews">';

        if ($avg_rating) {
            echo '<a href="' . esc_url($profile_url) . '" class="average-rating-badge" target="_blank" rel="noopener noreferrer">';
            echo '<span class="big-number">' . esc_html($avg_rating) . '</span> ★ on Google</a>';
        }

        $i = 0;
        foreach ($reviews as $review) {
            if ($i >= $max_reviews) break;
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

        echo '<a href="' . esc_url($profile_url) . '" target="_blank" rel="noopener noreferrer" class="see-all-reviews">See all reviews</a>';

        if (apply_filters('sonshine_show_reviews_attribution', true)) {
            echo '<div class="google-review-attribution">';
            echo 'Powered by <a href="https://github.com/SonShineRoofing/sonshine-google-reviews-shortcode" target="_blank" rel="noopener noreferrer">SonShine Google Reviews</a>';
            echo '</div>';
        }

        echo '<p class="google-review-disclaimer">All reviews shown above are automatically pulled from Google using the official API.</p>';
        echo '</div>';

        echo "<script id='sonshine-show-more-handler'>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.querySelector('.show-more-reviews');
            if (btn) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.hidden-review').forEach(r => {
                        r.style.display = 'block';
                        r.style.opacity = 0;
                        setTimeout(() => r.style.opacity = 1, 50);
                    });
                    this.style.display = 'none';
                });
            }
        });
        </script>";
    } else {
        echo '<p>No 5-star reviews found.</p>';
    }

    return ob_get_clean();
}

add_shortcode('sonshine_google_reviews', 'sonshine_fetch_google_reviews');
