# THERMODUL React Headless WordPress Theme

Version 2.1.0

## Install
1. Upload the `thermodul-react-theme` folder to `/wp-content/themes/` or install the ZIP in WordPress.
2. Activate **Thermodul React Headless**.
3. Go to **Appearance → THERMODUL Demo Import** and click **Import / Refresh THERMODUL demo content**.
4. Clear any cache/CDN cache.

## What's included
- New THERMODUL visual homepage based on the supplied design direction.
- Original THERMODUL logo preserved in the header/footer.
- Server-rendered homepage so the demo loads even if React is delayed/blocked.
- React script retained for headless/front-page enhancements.
- Bootstrap grid layout.
- Gutenberg block patterns and BBuilder-friendly shortcodes.
- Full gallery shortcode with high quality original image URLs from thermodul.eu/galerija/.
- Model pages, certification page, contact page and sitemap.

## Shortcodes
- `[thermodul_gallery]` shows all gallery categories and all images.
- `[thermodul_gallery group="Dzīvojamā istaba"]` shows one category.
- `[thermodul_gallery limit="4"]` limits images per category.
- `[thermodul_models]` shows all model cards.
- `[thermodul_contacts]` shows contact cards.
- `[thermodul_sitemap]` shows sitemap links.


## v2.2 updates
- AVIF conversion hook for uploaded JPG/PNG/WebP images and bundled AVIF logo asset.
- Ajax lazy loading for gallery batches and IntersectionObserver image loading.
- Ajax contact form shortcode/block: [thermodul_contact_form].
- In-page Ajax search shortcode/block: [thermodul_ajax_search].
- Language switcher with Polylang support and fallback LV/EN/RU/LT/ET URLs.
- Dynamic Gutenberg block fallbacks and patterns for editable admin pages.


## v2.3.0
- Added Lithuanian (LT) and Estonian (ET) language switcher support.
- Added fallback URLs `/lt/` and `/et/`, hreflang tags, Polylang string registration, and localized search/form UI labels.


## v2.4 fixes
- Removed duplicate front-page demo content rendering that caused repeated models/gallery/form sections.
- Added importer-side Media Library sideload for original thermodul.eu gallery/model images and AVIF variant generation when the server image library supports AVIF.
- Improved gallery grid alignment and responsive layout.
- Added richer imported content for all demo pages and model pages from the old thermodul.eu structure.
- Fixed language switch JS detection for LV/EN/RU/LT/ET.
