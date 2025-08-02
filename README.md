# SonShine Roofing Google Reviews Website Widget
A lightweight, auto-updating cacheable Google Reviews widget shortcode for WordPress. Built by SonShine Roofing to display 5-star reviews in style — with options for quick setup or theme-level optimization.

Showcase your 5-star Google reviews in style with this customizable, cacheable shortcode.

Built for [SonShine Roofing](https://sonshineroofing.com), but available to all.

## ✨ Features

- Pulls real-time reviews via Google Places API every 6 hours
- Caches responses to reduce API quota use
- Clean layout with brand colors (#fb9216, #0045d7, #00e3fe)
- Responsive and accessible
- Two ways to use: Quick Setup or Optimized Theme Integration


## See It in Action

1. [For a live demo visit sonshineroofing.com](https://sonshineroofing.com)

---

## 🚀 Quick Setup (WPCode Snippets / Plugin Drop-in)

1. Copy the contents of [`plugin-mode/google-reviews-shortcode.php`](plugin-mode/google-reviews-shortcode.php)
2. Paste it into a custom plugin or [WPCode](https://wpcode.com/)
3. Insert your Google Places API Key and link to your Google Business Profile where needed (instructions below on how to get Places API Key)
4. Use the shortcode in your builder:  
   ```php
   [sonshine_google_reviews]

## 🛠️ Optimized Theme Integration

1. Copy all files from [`theme-mode/`](theme-mode/) into your WordPress' theme folder
2. Insert your Google Places API Key and link to your Google Business Profile in [`theme-mode/fetch-google-reviews.php`](fetch-google-reviews.php) (instructions below on how to get Places API key)
3. Use the shortcode in your builder as normal:
   ```php
   [sonshine_google_reviews]

## 🔑 How to Get Your Google Places API Key
1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create or select a project
3. Enable the "Places API"
4. Generate an API Key
5. Add it to your code (follow the comments from within the code. Everything is clearly labeled)


## 🙏 Credit
Maintained by [SonShine Roofing](https://sonshineroofing.com), Sarasota FL.
"Since 1987 we've got you covered"

## 📜 License
MIT – Do what you want, just don't take our stars. Open source is the way to the future. 
