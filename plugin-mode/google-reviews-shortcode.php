<?php
function fetch_google_reviews() {
    $api_key = 'PLACES_API_KEY'; // <-- INSERT YOUR GOOGLE PLACES API KEY HERE
    $place_id = 'PLACE_ID'; // <-- INSERT YOUR GOOGLE PLACE ID HERE
    $google_reviews_url = 'https://www.google.com/maps/place/?q=place_id:' . $place_id;
    $cache_file = get_stylesheet_directory() . '/google-reviews-cache.json';
    $cache_duration = 21600; // CACHE REFRESHES EVERY 6 HOURS, CHANGE AS NEEDED

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
    </style>

    <?php
    if (!empty($reviews)) {
        echo '<div class="google-reviews">';

        if ($avg_rating) {
        	echo '<a 		href="LINK_TO_YOUR_GOOGLE_BUSINESS_PROFILE"
        		target="_blank" rel="noopener noreferrer" 
        		class="average-rating-badge" 
        		style="text-decoration: none;">
        		<span class="big-number">' . $avg_rating . '</span> ★ on Google
    		</a>'; // ^^^ INSERT LINK TO GOOGLE BUSINESS PROFILE ABOVE
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

        echo '<a href="LINK_TO_YOUR_GOOGLE_BUSINESS_PROFILE" target="_blank" rel="noopener noreferrer" class="see-all-reviews">See all reviews</a>'; // <-- INSERT LINK TO GOOGLE BUSINESS PROFILE HERE

        echo '<br><p class="google-review-disclaimer">All reviews shown above are automatically pulled from Google using the official API.</p>';
        echo '</div>';

        // JS handler to show hidden reviews
        echo "<script>
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
add_shortcode('sonshine_google_reviews', 'fetch_google_reviews');
