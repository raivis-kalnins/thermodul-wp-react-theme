# THERMODUL React Headless WordPress Theme

Version 3.10.4

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


## v3.8 migration + SEO fixes
- Restores/preserves legacy indexed pages instead of trashing and redirecting them by default.
- Adds the missing Water / ŪDENS model to the homepage and model data.
- Adds page-specific titles, meta descriptions, canonical URLs, Open Graph/Twitter tags and schema fallback.
- Integrates with Yoast filters when Yoast is active, avoiding duplicate theme metadata.
- Adds Appearance → THERMODUL SEO & Analytics for GA4, GTM and Search Console/Bing verification.
- Does not reuse the obsolete Universal Analytics UA property found in the old database backup.
- Improves fallback hreflang behavior and preserves current legacy page URLs.
- Fixes untranslated topbar/search/request labels and gallery/certification text in EN/RU/LT/ET.
- Adds a homepage About THERMODUL trust section and keeps the original legacy page available.
- Uses bundled local model images (including Water) instead of depending on the old live site for product artwork.
- Adds internal links from homepage model cards to preserved model pages when those pages exist.
- Preserves the live-site safety warning that the electric model must not be installed in a bathroom.
- Keeps certification copy evidence-based: EN442 / European heating-efficiency wording from the live content; CE/RoHS/IP claims require current manufacturer documentation before publishing.


## v3.9.1 live-database content sync
- Uses the exact model image files referenced by the current www.thermodul.eu database/product pages: modelloacqua, modelloelettrico, modellobivalente, modellobifacciale2, modellofasciaorizz and modellofasciavert.
- Model cards fall back to the live legacy image URL immediately and the importer sideloads those files into the new WordPress Media Library when possible.
- Homepage benefits copy is aligned more closely to the current legacy homepage: 80–85% radiant share, 15–20% convective share, ~276 ml water per metre, installation/use cases and finish options.
- Generated fallback model pages include the correct model image at the top.
- The dormant React fallback no longer exposes CE/RoHS/IP20 placeholder claims; it now mirrors the EN442 / EU heating-efficiency / ECO / 2006 certification wording used by the server-rendered homepage.


## v3.10.0 test package / hCaptcha bridge
- Reads hCaptcha configuration from WP BBuilder using its helper when available, with a safe fallback to the saved `wpbb_settings` option.
- Uses the current hCaptcha verification endpoint and verifies against the configured site key.
- Keeps hCaptcha on the theme AJAX contact form while preserving WP BBuilder as the single source for enable/site/secret settings.
- Adds a non-secret hCaptcha readiness status to Appearance -> THERMODUL Demo Import.
- Adds a one-time safe migration that restores missing legacy THERMODUL page URLs without overwriting populated pages and keeps legacy redirects disabled by default.
- Retains the v3.9.1 live-database model mapping, including the missing Water model and the six distinct legacy product images.


## v3.10.1 screenshot alignment polish
- Aligns all six homepage model cards and pins every “Skatīt vairāk” link to the same bottom baseline within each row.
- Replaces the weak middle THERMODUL insights image with a brighter real installed-system interior image already bundled with the theme.
- Makes the three insight cards equal height with aligned Read more links.
- Corrects privacy-checkbox/text alignment in the AJAX contact form.
- Removes the redundant “Protected by hCaptcha” note below the native hCaptcha widget while keeping full server-side hCaptcha validation.
- Removes the extra hCaptcha wrapper box so the widget aligns cleanly with the form fields.


## v3.10.2 alignment, languages and performance

- Ships the v3.10.1 alignment rules in the **actually enqueued** `assets/css/theme.min.css`, fixing the model-card CTA baseline and contact form/hCaptcha alignment.
- Creates and links proper Polylang static-front-page translations for LV/EN/RU/LT/ET on the first administrator visit after update, removing the static-front-page translation warning.
- Uses bundled AVIF/JPG model artwork locally instead of six old-domain requests.
- Replaces the low-quality “Kuru modeli izvēlēties?” card image with `demo/model-guide.avif` plus a JPG fallback.
- Removes the unused React/wp-element homepage bootstrap and lazy-loads hCaptcha only near the contact form.
- Adds image dimensions, AVIF hero preload, a local responsive grid, and front-page removal of unnecessary WP BBuilder Bootstrap/block assets.
- Adds **Appearance → THERMODUL Performance** with cache TTL, cache status, and a one-click THERMODUL cache clear button. Docket Cache/persistent object cache is used automatically when active.

## v3.10.4 regression fixes
- Restores the exact six model images from the old/live THERMODUL site by resolving the existing Media Library imports for `modelloacqua`, `modellobifacciale2`, `modellobivalente`, `modellofasciaorizz`, `modellofasciavert`, and `modelloelettrico` instead of the generic bundled lookalikes introduced in v3.10.2.
- Keeps AVIF delivery when the imported AVIF variants exist in `thermodul_demo_image_map`.
- Replaces the broken `Kuru modeli izvēlēties?` composite with a clean 1200x675 dedicated interior image, with AVIF for the article modal and AVIF/JPG picture output for the card.
- Gives article modals their own cover-image treatment while product/model modals keep `object-fit: contain`.
- Rebuilds the privacy-consent label with an explicit text span and centers the checkbox vertically with the sentence on desktop and mobile.
