<?php
if (!defined('ABSPATH')) { exit; }

define('THERMODUL_THEME_VERSION', '3.10.4');

function thermodul_setup() {
    load_theme_textdomain('thermodul', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array('height'=>80,'width'=>260,'flex-height'=>true,'flex-width'=>true));
    add_theme_support('html5', array('search-form','comment-form','comment-list','gallery','caption','style','script'));
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_editor_style(array('style.css','assets/css/wpbb-compat.css'));
    register_nav_menus(array('primary'=>__('Galvenā izvēlne', 'thermodul'), 'footer'=>__('Ātrās saites', 'thermodul')));
}
add_action('after_setup_theme', 'thermodul_setup');

function thermodul_asset_ver($path) { $file = get_template_directory() . '/' . ltrim($path, '/'); return file_exists($file) ? filemtime($file) : THERMODUL_THEME_VERSION; }


/* v3.10.2: small theme data cache. Uses Docket Cache/object cache when available,
 * with transient fallback on hosts without a persistent object cache. */
function thermodul_performance_enabled() { return (bool) get_option('thermodul_performance_mode', 1); }
function thermodul_cache_ttl() { return max(300, min(86400, (int) get_option('thermodul_cache_ttl', 3600))); }
function thermodul_cache_version() { return max(1, (int) get_option('thermodul_cache_version', 1)); }
function thermodul_cache_key($key) {
    $lang = function_exists('thermodul_current_lang') ? thermodul_current_lang() : 'lv';
    return 'v' . thermodul_cache_version() . ':' . $lang . ':' . sanitize_key($key);
}
function thermodul_cached($key, $callback, $ttl = null) {
    if (!thermodul_performance_enabled()) { return call_user_func($callback); }
    $cache_key = thermodul_cache_key($key);
    $found = false;
    $value = wp_cache_get($cache_key, 'thermodul', false, $found);
    if ($found) { return $value; }
    $transient_key = 'tdc_' . md5($cache_key);
    if (!wp_using_ext_object_cache()) {
        $value = get_transient($transient_key);
        if ($value !== false) { wp_cache_set($cache_key, $value, 'thermodul', (int) ($ttl ?: thermodul_cache_ttl())); return $value; }
    }
    $value = call_user_func($callback);
    $ttl = (int) ($ttl ?: thermodul_cache_ttl());
    wp_cache_set($cache_key, $value, 'thermodul', $ttl);
    if (!wp_using_ext_object_cache()) { set_transient($transient_key, $value, $ttl); }
    return $value;
}
function thermodul_clear_theme_cache() {
    update_option('thermodul_cache_version', thermodul_cache_version() + 1, false);
}
function thermodul_clear_cache_on_save($post_id) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) { return; }
    thermodul_clear_theme_cache();
}
add_action('save_post', 'thermodul_clear_cache_on_save', 30);

function thermodul_scripts() {
    /* On normal content pages use an already-loaded WP BBuilder Bootstrap grid if
     * available. The static homepage uses the tiny grid bundled in this theme. */
    $has_grid = wp_style_is('wpbb-bootstrap-core', 'enqueued') || wp_style_is('wpbb-bootstrap-grid', 'enqueued') || wp_style_is('wpbb-bootstrap-full', 'enqueued') || wp_style_is('wp-bbuilder-bootstrap', 'enqueued') || wp_style_is('bootstrap', 'enqueued');
    if (!$has_grid && !is_front_page() && !get_query_var('thermodul_onepage')) {
        wp_enqueue_style('thermodul-bootstrap-grid', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap-grid.min.css', array(), '5.3.3');
    }
    $theme_css = file_exists(get_template_directory() . '/assets/css/theme.min.css') ? get_template_directory_uri() . '/assets/css/theme.min.css' : get_stylesheet_uri();
    $theme_css_ver = file_exists(get_template_directory() . '/assets/css/theme.min.css') ? thermodul_asset_ver('assets/css/theme.min.css') : thermodul_asset_ver('style.css');
    wp_enqueue_style('thermodul-style', $theme_css, array(), $theme_css_ver);
    wp_enqueue_style('thermodul-wpbb', get_template_directory_uri() . '/assets/css/wpbb-compat.css', array('thermodul-style'), thermodul_asset_ver('assets/css/wpbb-compat.css'));
    $theme_js = file_exists(get_template_directory() . '/assets/js/theme.min.js') ? 'assets/js/theme.min.js' : 'assets/js/theme.js';
    wp_enqueue_script('thermodul-theme', get_template_directory_uri() . '/' . $theme_js, array(), thermodul_asset_ver($theme_js), true);
    if (function_exists('wp_script_add_data')) { wp_script_add_data('thermodul-theme', 'strategy', 'defer'); }
    wp_localize_script('thermodul-theme', 'thermodulTheme', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'searchNonce' => wp_create_nonce('thermodul_search'),
        'formNonce' => wp_create_nonce('thermodul_contact'),
        'galleryNonce' => wp_create_nonce('thermodul_gallery'),
        'lang' => function_exists('thermodul_current_lang') ? thermodul_current_lang() : 'lv',
        'hcaptchaEnabled' => function_exists('thermodul_hcaptcha_ready') ? thermodul_hcaptcha_ready() : false,
        'hcaptchaSiteKey' => function_exists('thermodul_hcaptcha_site_key') ? thermodul_hcaptcha_site_key() : '',
        'hcaptchaLang' => function_exists('thermodul_current_lang') ? thermodul_current_lang() : 'lv',
        'labels' => array(
            'searching' => function_exists('thermodul_i18n') ? thermodul_i18n('Meklē...', 'Searching...', 'Идет поиск...', 'Ieškoma...', 'Otsin...') : 'Meklē...',
            'noResults' => function_exists('thermodul_i18n') ? thermodul_i18n('Nekas netika atrasts.', 'No results found.', 'Ничего не найдено.', 'Nieko nerasta.', 'Tulemusi ei leitud.') : 'Nekas netika atrasts.',
            'minChars' => function_exists('thermodul_i18n') ? thermodul_i18n('Ievadiet vismaz 2 simbolus.', 'Type at least 2 characters.', 'Введите минимум 2 символа.', 'Įveskite bent 2 simbolius.', 'Sisestage vähemalt 2 tähemärki.') : 'Ievadiet vismaz 2 simbolus.',
            'loading' => function_exists('thermodul_i18n') ? thermodul_i18n('Ielādē...', 'Loading...', 'Загрузка...', 'Įkeliama...', 'Laadin...') : 'Ielādē...',
            'formError' => function_exists('thermodul_i18n') ? thermodul_i18n('Neizdevās nosūtīt formu. Lūdzu, mēģiniet vēlreiz.', 'Unable to send the form. Please try again.', 'Не удалось отправить форму. Попробуйте еще раз.', 'Nepavyko išsiųsti formos. Bandykite dar kartą.', 'Vormi saatmine ebaõnnestus. Palun proovige uuesti.') : 'Neizdevās nosūtīt formu.',
            'formRequired' => function_exists('thermodul_i18n') ? thermodul_i18n('Lūdzu, aizpildiet obligātos laukus.', 'Please fill in required fields.', 'Пожалуйста, заполните обязательные поля.', 'Užpildykite privalomus laukus.', 'Palun täitke kohustuslikud väljad.') : 'Lūdzu, aizpildiet obligātos laukus.',
            'captchaRequired' => function_exists('thermodul_i18n') ? thermodul_i18n('Lūdzu, apstipriniet hCaptcha.', 'Please complete hCaptcha.', 'Пожалуйста, пройдите hCaptcha.', 'Užbaikite hCaptcha patikrą.', 'Palun läbige hCaptcha kontroll.') : 'Lūdzu, apstipriniet hCaptcha.',
        ),
        'localIndex' => function_exists('thermodul_local_search_index') ? thermodul_local_search_index() : array(),
    ));
    /* hCaptcha is lazy-loaded by theme.js only when the contact form approaches
     * the viewport. The old headless React bootstrap was also removed: the
     * homepage is server-rendered and data-static-rendered=true. */
}
add_action('wp_enqueue_scripts', 'thermodul_scripts', 99);

function thermodul_admin_assets() {
    wp_enqueue_style('thermodul-editor', get_stylesheet_uri(), array(), thermodul_asset_ver('style.css'));
}
add_action('enqueue_block_editor_assets', 'thermodul_admin_assets');

function thermodul_brand() { ?>
    <a class="td-brand" href="<?php echo esc_url(function_exists('thermodul_language_home_url') ? thermodul_language_home_url() : home_url('/')); ?>" rel="home" aria-label="<?php bloginfo('name'); ?>">
        <img class="td-brand-logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/thermodul-logo.svg'); ?>" alt="THERMODUL" width="260" height="78">
    </a>
<?php }

function thermodul_menu_fallback() { ?>
<ul>
    <li><a href="<?php echo esc_url(thermodul_anchor_url('sakums')); ?>">Sākums</a></li>
    <li><a href="<?php echo esc_url(thermodul_anchor_url('kapec')); ?>">Kāpēc izvēlēties?</a></li>
    <li><a href="<?php echo esc_url(thermodul_anchor_url('ka-tas-darbojas')); ?>">Kā tas darbojas</a></li>
    <li><a href="<?php echo esc_url(thermodul_anchor_url('modeli')); ?>">Modeļi</a></li>
    <li><a href="<?php echo esc_url(thermodul_anchor_url('galerija')); ?>">Galerija</a></li>
    <li><a href="<?php echo esc_url(thermodul_anchor_url('sertifikacija')); ?>">Sertifikācija</a></li>
    <li><a href="<?php echo esc_url(thermodul_anchor_url('pieprasijums')); ?>">Kontakti</a></li>
</ul>
<?php }

function thermodul_nav($location = 'primary') {
    $items = array(
        thermodul_i18n('Sākums','Home','Главная','Pradžia','Avaleht') => thermodul_anchor_url('sakums'),
        thermodul_i18n('Kāpēc izvēlēties?','Why choose it?','Почему выбрать?','Kodėl rinktis?','Miks valida?') => thermodul_anchor_url('kapec'),
        thermodul_i18n('Kā tas darbojas','How it works','Как это работает','Kaip tai veikia','Kuidas see töötab') => thermodul_anchor_url('ka-tas-darbojas'),
        thermodul_i18n('Modeļi','Models','Модели','Modeliai','Mudelid') => thermodul_anchor_url('modeli'),
        thermodul_i18n('Galerija','Gallery','Галерея','Galerija','Galerii') => thermodul_anchor_url('galerija'),
        thermodul_i18n('Sertifikācija','Certification','Сертификация','Sertifikavimas','Sertifitseerimine') => thermodul_anchor_url('sertifikacija'),
        thermodul_i18n('Kontakti','Contacts','Контакты','Kontaktai','Kontakt') => thermodul_anchor_url('pieprasijums'),
    );
    echo '<ul class="td-onepage-nav">';
    foreach ($items as $label => $url) { echo '<li><a href="' . esc_url($url) . '">' . esc_html($label) . '</a></li>'; }
    echo '</ul>';
}

function thermodul_remote_img($path) {
    $path = ltrim($path, '/');
    $map = get_option('thermodul_demo_image_map', array());
    if (is_array($map) && !empty($map[$path]['url'])) { return $map[$path]['url']; }
    return apply_filters('thermodul_remote_img_base', 'https://www.thermodul.eu/wp-content/gallery/') . $path;
}
function thermodul_asset_img($path) {
    return get_template_directory_uri() . '/assets/images/' . ltrim($path, '/');
}
function thermodul_uploads_img($path) {
    $path = ltrim($path, '/');
    $remote = 'https://www.thermodul.eu/wp-content/uploads/' . $path;
    $map = get_option('thermodul_demo_image_map', array());
    $key = md5($remote);
    if (is_array($map) && !empty($map[$key]['url'])) { return $map[$key]['url']; }
    return $remote;
}

function thermodul_anchor_url($anchor = '') {
    $base = function_exists('thermodul_language_home_url') ? thermodul_language_home_url() : home_url('/');
    $base = trailingslashit($base);
    return $anchor ? $base . '#' . ltrim($anchor, '#') : $base;
}
function thermodul_anchor_map() {
    return array(
        'galerija' => 'galerija',
        'sertifikacija' => 'sertifikacija',
        'kontakti' => 'pieprasijums',
        'lapas-karte' => 'lapas-karte',
        'par-uznemumu' => 'kapec',
        'udens-modelis' => 'modeli',
        'elektriskais-modelis' => 'modeli',
        'dualais-modelis' => 'modeli',
        'abpusejais-modelis' => 'modeli',
        'dubultais-horizontalais-modelis' => 'modeli',
        'dubultais-vertikalais-modelis' => 'modeli',
    );
}
function thermodul_language_rewrites() {
    if (function_exists('pll_languages_list')) { return; }
    add_rewrite_rule('^(en|ru|lt|et)/?$', 'index.php?thermodul_lang=$matches[1]&thermodul_onepage=1', 'top');
}
add_action('init', 'thermodul_language_rewrites');
function thermodul_query_vars($vars) { $vars[] = 'thermodul_lang'; $vars[] = 'thermodul_onepage'; return $vars; }
add_filter('query_vars', 'thermodul_query_vars');
function thermodul_onepage_template($template) {
    if (get_query_var('thermodul_onepage')) { return get_template_directory() . '/front-page.php'; }
    return $template;
}
add_filter('template_include', 'thermodul_onepage_template', 99);
function thermodul_redirect_old_pages_to_onepage() {
    if (!get_option('thermodul_redirect_legacy_pages', 0)) { return; }
    if (is_admin() || wp_doing_ajax()) { return; }
    $uri = isset($_SERVER['REQUEST_URI']) ? trim(parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') : '';
    $parts = $uri === '' ? array() : explode('/', $uri);
    $lang = 'lv';
    if (!empty($parts[0]) && preg_match('/^(en|ru|lt|et)$/', $parts[0])) { $lang = $parts[0]; array_shift($parts); }
    $slug = isset($parts[0]) ? trim($parts[0]) : '';
    $map = thermodul_anchor_map();
    if ($slug && isset($map[$slug])) {
        $base = $lang === 'lv' ? home_url('/') : home_url('/' . $lang . '/');
        wp_safe_redirect(trailingslashit($base) . '#' . $map[$slug], 301);
        exit;
    }
}
add_action('template_redirect', 'thermodul_redirect_old_pages_to_onepage', 1);
function thermodul_gallery_raw_paths() {
    $make = function($folder, $prefix, $count) { $arr = array(); for ($i=1; $i <= $count; $i++) { $arr[] = $folder . '/' . $prefix . $i . '.jpg'; } return $arr; };
    return array(
        thermodul_i18n('Dzīvojamā istaba','Living rooms','Жилые комнаты','Gyvenamieji kambariai','Elutoad') => $make('dzivojama-istaba','dz',10),
        thermodul_i18n('Kafejnīcas un restorāni','Cafes and restaurants','Кафе и рестораны','Kavinės ir restoranai','Kohvikud ja restoranid') => $make('kafejnicas-restorani','kr',8),
        thermodul_i18n('Muzeji','Museums','Музеи','Muziejai','Muuseumid') => $make('muzejs','mu',8),
        thermodul_i18n('Ofisi un veselības iestādes','Offices and healthcare','Офисы и медучреждения','Biurai ir sveikatos įstaigos','Kontorid ja tervishoid') => $make('ofisi-veselibas-iestades','of',9),
        thermodul_i18n('Palīgtelpas','Utility rooms','Подсобные помещения','Pagalbinės patalpos','Abiruumid') => $make('paligtelpas','p',9),
        thermodul_i18n('Reliģiskās telpas','Religious buildings','Религиозные помещения','Religinės patalpos','Pühakojad') => $make('religiskas-iestades','rel',6),
        thermodul_i18n('Skolas & bērnu dārzi','Schools & kindergartens','Школы и детские сады','Mokyklos ir darželiai','Koolid ja lasteaiad') => $make('skolas-bernudarzi','sk',8),
        thermodul_i18n('Veikali','Shops','Магазины','Parduotuvės','Kauplused') => $make('veikali','v',6),
    );
}
function thermodul_upload_img($path) { return content_url('uploads/thermodul-demo/' . ltrim($path, '/')); }
function thermodul_image_dimensions($src) {
    static $memo = array();
    if (!$src) { return array(); }
    if (isset($memo[$src])) { return $memo[$src]; }
    $file = '';
    $theme_url = get_template_directory_uri();
    if (strpos($src, $theme_url) === 0) {
        $file = get_template_directory() . '/' . ltrim(str_replace($theme_url, '', $src), '/');
    } else {
        $uploads = wp_get_upload_dir();
        if (!empty($uploads['baseurl']) && strpos($src, $uploads['baseurl']) === 0) {
            $file = trailingslashit($uploads['basedir']) . ltrim(str_replace($uploads['baseurl'], '', $src), '/');
        }
    }
    if ($file && file_exists($file)) {
        $size = @getimagesize($file);
        if ($size && !empty($size[0]) && !empty($size[1])) { return $memo[$src] = array((int)$size[0], (int)$size[1]); }
    }
    return $memo[$src] = array();
}
function thermodul_picture($src, $alt = '', $class = '', $loading = 'lazy', $extra = '') {
    $avif = function_exists('thermodul_avif_url_for') ? thermodul_avif_url_for($src) : '';
    $cls = $class ? ' class="' . esc_attr($class) . '"' : '';
    $dims = thermodul_image_dimensions($src);
    $dim_attrs = !empty($dims) ? ' width="'.esc_attr($dims[0]).'" height="'.esc_attr($dims[1]).'"' : '';
    $html = '<picture>';
    if ($avif && $avif !== $src) { $html .= '<source srcset="' . esc_url($avif) . '" type="image/avif">'; }
    $html .= '<img' . $cls . ' src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '" loading="' . esc_attr($loading) . '" decoding="async"' . $dim_attrs . ' ' . $extra . '>';
    $html .= '</picture>';
    return $html;
}

function thermodul_gallery_groups() {
    $groups = array();
    foreach (thermodul_gallery_raw_paths() as $label => $paths) {
        $groups[$label] = array_map(function($path) { return thermodul_remote_img($path); }, $paths);
    }
    return $groups;
}

function thermodul_gallery_flat_items($group_filter = '') {
    $items = array();
    foreach (thermodul_gallery_groups() as $group => $images) {
        if ($group_filter && strtolower($group_filter) !== strtolower($group)) { continue; }
        foreach ($images as $i => $src) {
            $items[] = array(
                'group' => $group,
                'src'   => $src,
                'index' => $i + 1,
            );
        }
    }
    return $items;
}
function thermodul_gallery_card($item, $meta = '') {
    $meta = $meta ? $meta : thermodul_i18n('Skatīt attēlu','View image','Смотреть изображение','Žiūrėti nuotrauką','Vaata pilti');
    return '<a class="td-gallery-item td-lightbox" href="'.esc_url($item['src']).'" data-td-lightbox>'.thermodul_picture($item['src'], $item['group'].' '.$item['index'], '', 'lazy').'<span class="td-gallery-caption"><strong>'.esc_html($item['group']).'</strong><small>'.esc_html($meta).'</small></span></a>';
}

function thermodul_model_texts($key) {
    $texts = array(
        'udens-modelis' => array(
            'meta' => thermodul_i18n('Augstums: 13,7 cm · Dziļums: 2,9 cm','Height: 13.7 cm · Depth: 2.9 cm','Высота: 13,7 см · Глубина: 2,9 см','Aukštis: 13,7 cm · Gylis: 2,9 cm','Kõrgus: 13,7 cm · Sügavus: 2,9 cm'),
            'text' => thermodul_i18n('Ūdens modelis darbojas ar dažādiem siltuma ģeneratoriem, tostarp katliem un siltumsūkņiem. Tas ir piemērots jaunbūvēm un renovācijām, viegli savietojams ar esošiem radiatoriem vai citu ūdens apkures sistēmu un ļauj regulēt telpas atsevišķi.','The water model works with a wide range of heat generators, including boilers and heat pumps. It suits new builds and renovations, can be combined with existing radiators or other hydronic heating, and allows room-by-room control.','Водяная модель работает с различными источниками тепла, включая котлы и тепловые насосы. Подходит для новостроек и реконструкции, совместима с существующими радиаторами и другими водяными системами отопления и позволяет регулировать помещения отдельно.','Vandens modelis veikia su įvairiais šilumos šaltiniais, įskaitant katilus ir šilumos siurblius. Tinka naujai statybai ir renovacijai, gali būti derinamas su esamais radiatoriais ar kita vandens šildymo sistema ir leidžia reguliuoti patalpas atskirai.','Veemudel töötab erinevate soojusallikatega, sh katelde ja soojuspumpadega. See sobib uusehitistesse ja renoveerimiseks, ühildub olemasolevate radiaatorite või muu vesiküttega ning võimaldab ruumipõhist reguleerimist.')
        ),
        'abpusejais-modelis' => array(
            'meta' => thermodul_i18n('Augstums: 13,7 cm · Platums: 6,0 cm · Jauda x2','Height: 13.7 cm · Width: 6.0 cm · Double output','Высота: 13,7 см · Ширина: 6,0 см · Мощность x2','Aukštis: 13,7 cm · Plotis: 6,0 cm · Dviguba galia','Kõrgus: 13,7 cm · Laius: 6,0 cm · Topeltvõimsus'),
            'text' => thermodul_i18n('Risinājums logiem līdz grīdai un vietām, kur THERMODUL nevar stiprināt tieši pie sienas. Izskatās kā nepārtraukta grīdlīste no abām pusēm.','A solution for floor-to-ceiling windows and places where THERMODUL cannot be fixed directly to the wall. It creates a continuous baseboard look on both sides.','Решение для окон до пола и мест, где THERMODUL нельзя закрепить прямо к стене. С обеих сторон выглядит как непрерывный плинтус.','Sprendimas langams iki grindų ir vietoms, kur THERMODUL negalima tvirtinti tiesiai prie sienos. Iš abiejų pusių atrodo kaip vientisa grindjuostė.','Lahendus põrandani akendele ja kohtadesse, kus THERMODULit ei saa otse seinale kinnitada. Mõlemalt poolt jääb katkematu põrandaliistu mulje.')
        ),
        'dualais-modelis' => array(
            'meta' => thermodul_i18n('Ūdens + elektriskais režīms · Augstums 13,7 cm · Dziļums 2,9 cm','Water + electric mode · Height 13.7 cm · Depth 2.9 cm','Водяной + электрический режим · Высота 13,7 см · Глубина 2,9 см','Vandens + elektrinis režimas · Aukštis 13,7 cm · Gylis 2,9 cm','Vee- ja elektrirežiim · Kõrgus 13,7 cm · Sügavus 2,9 cm'),
            'text' => thermodul_i18n('Duālais modelis apvieno ūdens un elektrisko modeli. Tas ir praktisks starpsezonā, centralizētās apkures ēkās un kā papildu siltuma garantija.','The dual model combines water and electric operation. It is practical between seasons, in buildings with centralized heating, and as an additional heat backup.','Дуальная модель объединяет водяной и электрический режимы. Практична в межсезонье, в зданиях с центральным отоплением и как резерв тепла.','Dualinis modelis sujungia vandens ir elektrinį veikimą. Praktiškas tarpsezoniu, centralizuoto šildymo pastatuose ir kaip papildoma šilumos atsarga.','Duaalmudel ühendab vee- ja elektrilise töörežiimi. See on praktiline üleminekuperioodil, keskküttega hoonetes ja lisasoojusena.')
        ),
        'dubultais-horizontalais-modelis' => array(
            'meta' => thermodul_i18n('Augstums: 24 cm · Dziļums: 2,9 cm','Height: 24 cm · Depth: 2.9 cm','Высота: 24 см · Глубина: 2,9 см','Aukštis: 24 cm · Gylis: 2,9 cm','Kõrgus: 24 cm · Sügavus: 2,9 cm'),
            'text' => thermodul_i18n('Dubultais horizontālais risinājums piemērots lielām telpām, piemēram, skolām, restorāniem, sporta zālēm un baznīcām. To var uzstādīt dažādos augstumos.','The double horizontal solution suits large rooms such as schools, restaurants, gyms, and churches. It can be installed at different heights.','Двойное горизонтальное решение подходит для больших помещений: школ, ресторанов, спортзалов и церквей. Возможен монтаж на разной высоте.','Dvigubas horizontalus sprendimas tinka didelėms patalpoms: mokykloms, restoranams, sporto salėms ir bažnyčioms. Galima montuoti skirtingame aukštyje.','Topelt horisontaalne lahendus sobib suurtesse ruumidesse, näiteks koolidesse, restoranidesse, spordisaalidesse ja kirikutesse. Seda saab paigaldada eri kõrgustele.')
        ),
        'dubultais-vertikalais-modelis' => array(
            'meta' => thermodul_i18n('Vertikāls dubultais risinājums lielākai jaudai','Vertical double solution for higher output','Вертикальное двойное решение для большей мощности','Vertikalus dvigubas sprendimas didesnei galiai','Vertikaalne topeltlahendus suurema võimsuse jaoks'),
            'text' => thermodul_i18n('Dubultais vertikālais modelis tiek izmantots, ja siltuma jauda pa perimetru nav pietiekama. Īpaši piemērots ziemas dārziem un stiklotām lodžijām.','The double vertical model is used when perimeter heat output is not enough. It is especially suitable for winter gardens and glazed loggias.','Двойная вертикальная модель используется, когда тепловой мощности по периметру недостаточно. Особенно подходит для зимних садов и застеклённых лоджий.','Dvigubas vertikalus modelis naudojamas, kai šilumos galios perimetru nepakanka. Ypač tinka žiemos sodams ir įstiklintoms lodžijoms.','Topelt vertikaalmudelit kasutatakse siis, kui perimeetri soojusvõimsusest ei piisa. Eriti sobib talveaedadesse ja klaasitud lodzhadesse.')
        ),
        'elektriskais-modelis' => array(
            'meta' => thermodul_i18n('Augstums: 13,7 cm · Dziļums: 2,9 cm','Height: 13.7 cm · Depth: 2.9 cm','Высота: 13,7 см · Глубина: 2,9 см','Aukštis: 13,7 cm · Gylis: 2,9 cm','Kõrgus: 13,7 cm · Sügavus: 2,9 cm'),
            'text' => thermodul_i18n('Elektriskais modelis ir ātri uzstādāms risinājums telpām bez apkures katla vai centrālās apkures pieslēguma, tostarp atpūtas mājām un atsevišķām zonām.','The electric model is a quick-to-install solution for rooms without a boiler or central heating connection, including holiday homes and separate zones.','Электрическая модель быстро устанавливается в помещениях без котла или подключения к центральному отоплению, включая дачные дома и отдельные зоны.','Elektrinis modelis greitai montuojamas patalpose be katilo ar centrinio šildymo prijungimo, taip pat poilsio namuose ir atskirose zonose.','Elektrimudel on kiiresti paigaldatav lahendus ruumidesse, kus puudub katel või keskkütteühendus, sh suvilatesse ja eraldi tsoonidesse.')
        ),
    );
    return isset($texts[$key]) ? $texts[$key] : array('meta'=>'','text'=>'');
}

function thermodul_model_image_sources() {
    // Exact product artwork used by the current www.thermodul.eu product pages/database.
    return array(
        'udens-modelis' => '2014/01/modelloacqua.jpg',
        'abpusejais-modelis' => '2014/01/modellobifacciale2.jpg',
        'dualais-modelis' => '2014/01/modellobivalente.jpg',
        'dubultais-horizontalais-modelis' => '2014/01/modellofasciaorizz.jpg',
        'dubultais-vertikalais-modelis' => '2014/01/modellofasciavert.jpg',
        'elektriskais-modelis' => '2014/01/modelloelettrico.jpg',
    );
}

function thermodul_models() {
    /* v3.10.4: always use the exact six legacy model artworks. The importer stores
     * these old-live files in the current Media Library (with AVIF variants), and
     * thermodul_uploads_img() resolves to those local copies when available. */
    $sources = thermodul_model_image_sources();
    $items = array(
        array('title'=>thermodul_i18n('ŪDENS MODELIS','WATER MODEL','ВОДЯНАЯ МОДЕЛЬ','VANDENS MODELIS','VEEMUDEL'),'slug'=>'udens-modelis','img'=>thermodul_uploads_img($sources['udens-modelis'])),
        array('title'=>thermodul_i18n('ABPUSĒJAIS MODELIS','DOUBLE-SIDED MODEL','ДВУСТОРОННЯЯ МОДЕЛЬ','DVIPUSIS MODELIS','KAHEPOOLNE MUDEL'),'slug'=>'abpusejais-modelis','img'=>thermodul_uploads_img($sources['abpusejais-modelis'])),
        array('title'=>thermodul_i18n('DUĀLAIS MODELIS','DUAL MODEL','ДУАЛЬНАЯ МОДЕЛЬ','DUALINIS MODELIS','DUAALMUDEL'),'slug'=>'dualais-modelis','img'=>thermodul_uploads_img($sources['dualais-modelis'])),
        array('title'=>thermodul_i18n('DUBULTAIS HORIZONTĀLAIS MODELIS','DOUBLE HORIZONTAL MODEL','ДВОЙНАЯ ГОРИЗОНТАЛЬНАЯ МОДЕЛЬ','DVIGUBAS HORIZONTALUS MODELIS','TOPELT HORISONTAALNE MUDEL'),'slug'=>'dubultais-horizontalais-modelis','img'=>thermodul_uploads_img($sources['dubultais-horizontalais-modelis'])),
        array('title'=>thermodul_i18n('DUBULTAIS VERTIKĀLAIS MODELIS','DOUBLE VERTICAL MODEL','ДВОЙНАЯ ВЕРТИКАЛЬНАЯ МОДЕЛЬ','DVIGUBAS VERTIKALUS MODELIS','TOPELT VERTIKAALNE MUDEL'),'slug'=>'dubultais-vertikalais-modelis','img'=>thermodul_uploads_img($sources['dubultais-vertikalais-modelis'])),
        array('title'=>thermodul_i18n('ELEKTRISKAIS MODELIS','ELECTRIC MODEL','ЭЛЕКТРИЧЕСКАЯ МОДЕЛЬ','ELEKTRINIS MODELIS','ELEKTRIMUDEL'),'slug'=>'elektriskais-modelis','img'=>thermodul_uploads_img($sources['elektriskais-modelis'])),
    );
    foreach ($items as &$item) {
        $copy = thermodul_model_texts($item['slug']);
        $item['meta'] = $copy['meta'];
        $item['text'] = $copy['text'];
        $item['modal_img'] = thermodul_avif_url_for($item['img']) ?: $item['img'];
    }
    unset($item);
    return $items;
}



function thermodul_featured_gallery_items() {
    $items = thermodul_gallery_flat_items();
    return array_slice($items, 0, 5);
}


function thermodul_home_text() {
    return array(
        'hero_eyebrow' => thermodul_i18n('Siltās grīdlīstes apsildes sistēma','Warm baseboard heating system','Система тёплого плинтуса','Šiltų grindjuosčių šildymo sistema','Sooja põrandaliistu küttesüsteem'),
        'hero_title_a' => thermodul_i18n('Siltās grīdlīstes','Warm baseboard heating','Тёплый плинтус','Šiltos grindjuostės','Soe põrandaliist'),
        'hero_title_b' => 'THERMODUL',
        'hero_kicker' => thermodul_i18n('Komforts. Dizains. Efektivitāte.','Comfort. Design. Efficiency.','Комфорт. Дизайн. Эффективность.','Komfortas. Dizainas. Efektyvumas.','Mugavus. Disain. Tõhusus.'),
        'hero_lead' => thermodul_i18n(
            'Mūsdienīga grīdlīstes apsildes sistēma, kas nodrošina vienmērīgu siltumu, veselīgu mikroklimatu un tīru interjera līniju bez tradicionāliem radiatoriem.',
            'A modern baseboard heating system that delivers even warmth, a healthy indoor climate, and a clean interior line without traditional radiators.',
            'Современная система отопления тёплым плинтусом обеспечивает равномерное тепло, здоровый микроклимат и чистый интерьер без обычных радиаторов.',
            'Moderni grindjuosčių šildymo sistema užtikrina tolygią šilumą, sveiką mikroklimatą ir švarų interjero vaizdą be tradicinių radiatorių.',
            'Kaasaegne põrandaliistu küttesüsteem tagab ühtlase soojuse, tervisliku sisekliima ja puhta interjöörijoone ilma tavaradiaatoriteta.'
        ),
        'request' => thermodul_i18n('Pieprasīt informāciju','Request information','Запросить информацию','Prašyti informacijos','Küsi infot'),
        'gallery' => thermodul_i18n('Skatīt galeriju','View gallery','Смотреть галерею','Žiūrėti galeriją','Vaata galeriid'),
        'why' => thermodul_i18n('Kāpēc izvēlēties?','Why choose it?','Почему выбрать?','Kodėl rinktis?','Miks valida?'),
        'how' => thermodul_i18n('Kā tas darbojas','How it works','Как это работает','Kaip tai veikia','Kuidas see töötab'),
        'models' => thermodul_i18n('Modeļi','Models','Модели','Modeliai','Mudelid'),
        'gallery_title' => thermodul_i18n('Galerija','Gallery','Галерея','Galerija','Galerii'),
        'cert' => thermodul_i18n('Sertifikācija','Certification','Сертификация','Sertifikavimas','Sertifitseerimine'),
        'posts' => thermodul_i18n('THERMODUL padomi','THERMODUL insights','Советы THERMODUL','THERMODUL patarimai','THERMODUL nõuanded'),
        'contact' => thermodul_i18n('Pieprasīt informāciju','Request information','Запросить информацию','Prašyti informacijos','Küsi infot'),
        'load_more' => thermodul_i18n('Ielādēt vēl attēlus','Load more images','Загрузить ещё изображения','Įkelti daugiau nuotraukų','Laadi rohkem pilte'),
        'close' => thermodul_i18n('Aizvērt','Close','Закрыть','Uždaryti','Sulge'),
    );
}
function thermodul_feature_translations() {
    return array(
        array('✓', thermodul_i18n('Vienmērīgs starojuma siltums','Even radiant warmth','Равномерное лучистое тепло','Tolygus spindulinis šildymas','Ühtlane kiirgussoojus'), thermodul_i18n('THERMODUL darbojas galvenokārt (80–85%) ar siltuma starojumu. Vienmērīga siltuma sadale ļauj justies komfortabli arī pie līdz 2–3 °C zemākas telpas temperatūras nekā ar parastiem radiatoriem.','THERMODUL works mainly (80–85%) through radiant heat. Even heat distribution can provide comfort at a room temperature up to 2–3 °C lower than with conventional radiators.','THERMODUL работает главным образом (80–85%) за счёт лучистого тепла. Равномерное распределение тепла позволяет ощущать комфорт при температуре помещения до 2–3 °C ниже, чем с обычными радиаторами.','THERMODUL daugiausia (80–85 %) veikia spinduline šiluma. Tolygus šilumos pasiskirstymas leidžia jaustis komfortiškai esant iki 2–3 °C žemesnei patalpos temperatūrai nei su įprastais radiatoriais.','THERMODUL töötab peamiselt (80–85%) kiirgussoojusega. Ühtlane soojusjaotus võimaldab mugavust ka kuni 2–3 °C madalama ruumitemperatuuri juures kui tavaradiaatoritega.')),
        array('✓', thermodul_i18n('Mierīgs mikroklimats','Gentle indoor climate','Мягкий микроклимат','Švelnus mikroklimatas','Rahulik sisekliima'), thermodul_i18n('Lēnā konvektīvā daļa (15–20%) ir paredzēta, lai mazinātu strauju gaisa, putekļu un sīko daļiņu kustību telpā, vienlaikus sildot sienu perimetru.','The slower convective share (15–20%) is designed to reduce strong movement of air, dust and fine particles while warming the wall perimeter.','Медленная конвективная составляющая (15–20%) предназначена для уменьшения интенсивного движения воздуха, пыли и мелких частиц при прогреве периметра стен.','Lėtesnė konvekcinė dalis (15–20 %) skirta sumažinti intensyvų oro, dulkių ir smulkių dalelių judėjimą, kartu šildant sienų perimetrą.','Aeglasem konvektsiooniosa (15–20%) on mõeldud õhu, tolmu ja peenosakeste tugeva liikumise vähendamiseks, soojendades samal ajal seinte perimeetrit.')),
        array('✓', thermodul_i18n('Mazs ūdens daudzums','Low water volume','Малый объём воды','Mažas vandens kiekis','Väike veemaht'), thermodul_i18n('Pašreizējā THERMODUL informācijā norādīts, ka vienā sistēmas metrā ir aptuveni 276 ml ūdens; siltums tiek padots cilvēku uzturēšanās zonā, nevis koncentrēts pie griestiem.','Current THERMODUL information states that one metre of the system contains about 276 ml of water; heat is delivered in the occupied zone rather than being concentrated near the ceiling.','В текущей информации THERMODUL указано, что в одном метре системы находится около 276 мл воды; тепло подаётся в зоне пребывания людей, а не концентрируется под потолком.','Dabartinėje THERMODUL informacijoje nurodoma, kad viename sistemos metre yra apie 276 ml vandens; šiluma tiekiama žmonių buvimo zonoje, o ne koncentruojama prie lubų.','THERMODULi praeguse info järgi sisaldab üks meeter süsteemi umbes 276 ml vett; soojus antakse inimeste viibimistsooni, mitte ei koondu lae alla.')),
        array('✓', thermodul_i18n('Vienkārši un funkcionāli','Simple and functional','Просто и функционально','Paprasta ir funkcionalu','Lihtne ja funktsionaalne'), thermodul_i18n('Sistēma stiprināma pie sienas, neprasa īpašu konstrukciju, ir viegli pārbaudāma un tīrāma. Tas atvieglo uzstādīšanu un turpmāku apkopi.','The system mounts to the wall, does not require a special structure, and is easy to inspect and clean, simplifying installation and ongoing maintenance.','Система крепится к стене, не требует специальной конструкции, легко проверяется и очищается, что упрощает монтаж и дальнейшее обслуживание.','Sistema tvirtinama prie sienos, nereikalauja specialios konstrukcijos, ją lengva patikrinti ir valyti, todėl paprastesnis montavimas ir priežiūra.','Süsteem kinnitatakse seinale, ei vaja erikonstruktsiooni ning seda on lihtne kontrollida ja puhastada, mis lihtsustab paigaldust ja hooldust.')),
        array('✓', thermodul_i18n('Plašs pielietojums','Wide range of applications','Широкое применение','Platus pritaikymas','Lai kasutusala'), thermodul_i18n('Piemērots jaunbūvēm, renovācijām, radiatoru nomaiņai un kombinēšanai ar citām apkures sistēmām.','Suitable for new builds, renovations, radiator replacement, and combination with other heating systems.','Подходит для новостроек, реконструкции, замены радиаторов и совместной работы с другими системами отопления.','Tinka naujai statybai, renovacijai, radiatorių keitimui ir derinimui su kitomis šildymo sistemomis.','Sobib uusehitustesse, renoveerimiseks, radiaatorite asendamiseks ja kombineerimiseks teiste küttesüsteemidega.')),
        array('✓', thermodul_i18n('Dizains un apdares','Design and finishes','Дизайн и отделка','Dizainas ir apdaila','Disain ja viimistlus'), thermodul_i18n('Standarta apdares ir baltā, tumši brūnā un anodēta alumīnija krāsa; pēc pasūtījuma vecajā THERMODUL piedāvājumā norādītas 1709 RAL krāsas, 10 koka un 2 marmora imitācijas.','Standard finishes are white, dark brown and anodized aluminium; the current legacy THERMODUL offer lists 1,709 RAL colors plus 10 wood and 2 marble-effect finishes to order.','Стандартные варианты — белый, тёмно-коричневый и анодированный алюминий; в действующем прежнем предложении THERMODUL указаны 1709 цветов RAL, 10 вариантов под дерево и 2 под мрамор на заказ.','Standartinės apdailos – balta, tamsiai ruda ir anoduoto aliuminio; dabartiniame sename THERMODUL pasiūlyme nurodytos 1709 RAL spalvos, 10 medžio ir 2 marmuro imitacijos pagal užsakymą.','Standardviimistlused on valge, tumepruun ja anodeeritud alumiinium; praeguses vanas THERMODULi pakkumises on tellimisel 1709 RAL-tooni, 10 puidu- ja 2 marmoriimitatsiooni.')),
    );
}
function thermodul_posts_data() {
    return array(
        array(
            'title' => thermodul_i18n('Kā THERMODUL uzlabo mikroklimatu','How THERMODUL improves indoor climate','Как THERMODUL улучшает микроклимат','Kaip THERMODUL gerina mikroklimatą','Kuidas THERMODUL parandab sisekliimat'),
            'excerpt' => thermodul_i18n('Siltuma starojums samazina strauju gaisa kustību, tāpēc telpā cirkulē mazāk putekļu.','Radiant heat reduces strong air movement, so less dust circulates in the room.','Лучистое тепло снижает интенсивное движение воздуха, поэтому в помещении меньше пыли.','Spindulinė šiluma mažina oro judėjimą, todėl patalpoje cirkuliuoja mažiau dulkių.','Kiirgussoojus vähendab tugevat õhuliikumist, seega ringleb ruumis vähem tolmu.'),
            'body' => thermodul_i18n('THERMODUL galvenokārt silda ar starojumu. Sienas un telpas perimetrs uzsilst vienmērīgi, gaiss netiek strauji maisīts, bet komforts ir jūtams arī pie zemākas temperatūras. Tas ir īpaši noderīgi mājās ar bērniem, alerģiskiem cilvēkiem un telpās, kur nepieciešams stabils mikroklimats.','THERMODUL mainly heats through radiant warmth. Walls and the room perimeter warm evenly, air is not stirred aggressively, and comfort is felt even at a lower room temperature. This is especially useful in homes with children, allergy-sensitive people, and spaces that need a stable indoor climate.','THERMODUL в основном работает за счёт лучистого тепла. Стены и периметр помещения прогреваются равномерно, воздух не перемешивается резко, а комфорт ощущается даже при более низкой температуре. Это особенно полезно для домов с детьми, людей с аллергией и помещений со стабильным микроклиматом.','THERMODUL daugiausia šildo spinduline šiluma. Sienos ir patalpos perimetras sušyla tolygiai, oras nėra intensyviai maišomas, o komfortas jaučiamas ir esant žemesnei temperatūrai. Tai ypač naudinga namams su vaikais, alergiškiems žmonėms ir patalpoms, kur reikalingas stabilus mikroklimatas.','THERMODUL kütab peamiselt kiirgussoojusega. Seinad ja ruumi perimeeter soojenevad ühtlaselt, õhku ei segata intensiivselt ning mugavus on tuntav ka madalama temperatuuriga. See sobib eriti hästi lastega kodudesse, allergikutele ja ruumidesse, kus on vaja stabiilset sisekliimat.'),
            'image' => thermodul_remote_img('dzivojama-istaba/dz1.jpg')
        ),
        array(
            'title' => thermodul_i18n('Kuru modeli izvēlēties?','Which model should you choose?','Какую модель выбрать?','Kurį modelį rinktis?','Millist mudelit valida?'),
            'excerpt' => thermodul_i18n('Ūdens, elektriskais un duālais modelis ļauj pielāgot sistēmu katlam, siltumsūknim vai atsevišķām telpām.','Water, electric, and dual models adapt the system to boilers, heat pumps, or separate rooms.','Водяная, электрическая и дуальная модели позволяют адаптировать систему к котлу, тепловому насосу или отдельным помещениям.','Vandens, elektrinis ir dualinis modeliai pritaikomi katilui, šilumos siurbliui ar atskiroms patalpoms.','Vee-, elektri- ja duaalmudelid sobivad katlale, soojuspumbale või eraldi ruumidele.'),
            'body' => thermodul_i18n('Ja ēkā jau ir ūdens apkure, ūdens modelis parasti ir galvenais risinājums. Elektriskais modelis ir ērts telpām bez centrālās apkures pieslēguma, savukārt duālais modelis apvieno abus režīmus un ir praktisks starpsezonā.','If a building already has hydronic heating, the water model is usually the main solution. The electric model is convenient for rooms without central heating, while the dual model combines both modes and is practical between seasons.','Если в здании уже есть водяное отопление, водяная модель обычно является основным решением. Электрическая модель удобна для помещений без центрального отопления, а дуальная сочетает оба режима и практична в межсезонье.','Jei pastate jau yra vandens šildymas, vandens modelis dažniausiai yra pagrindinis sprendimas. Elektrinis modelis patogus patalpoms be centrinio šildymo, o dualinis sujungia abu režimus ir praktiškas tarpsezoniu.','Kui hoones on juba vesiküte, on veemudel tavaliselt põhivalik. Elektrimudel sobib ruumidesse ilma keskkütteta ning duaalmudel ühendab mõlemad režiimid ja on praktiline üleminekuperioodil.'),
            'image' => thermodul_asset_img('demo/model-selection-guide-v2.jpg'),
            'modal_image' => thermodul_asset_img('demo/model-selection-guide-v2.avif')
        ),
        array(
            'title' => thermodul_i18n('Kāpēc grīdlīstes apsilde ir estētiska','Why baseboard heating is aesthetic','Почему плинтусное отопление эстетично','Kodėl grindjuosčių šildymas estetiškas','Miks põrandaliistu küte on esteetiline'),
            'excerpt' => thermodul_i18n('Sistēma aizstāj radiatorus un saglabā brīvas sienas mēbelēm, logiem un tīram dizainam.','The system replaces radiators and keeps walls free for furniture, windows, and clean design.','Система заменяет радиаторы и освобождает стены для мебели, окон и чистого дизайна.','Sistema pakeičia radiatorius ir palieka sienas laisvas baldams, langams ir švariam dizainui.','Süsteem asendab radiaatorid ja jätab seinad vabaks mööblile, akendele ja puhtale disainile.'),
            'body' => thermodul_i18n('THERMODUL sildelements atrodas grīdlīstes līnijā, tāpēc tas neizjauc interjeru. To var pielāgot krāsām, apdarei un telpas arhitektūrai, saglabājot ērtu piekļuvi pārbaudei un apkopei.','The THERMODUL heating element sits in the baseboard line, so it does not disturb the interior. It can be adapted to colors, finishes, and room architecture while remaining easy to inspect and maintain.','Нагревательный элемент THERMODUL расположен в линии плинтуса, поэтому не нарушает интерьер. Его можно адаптировать к цветам, отделке и архитектуре помещения, сохраняя удобный доступ для проверки и обслуживания.','THERMODUL šildymo elementas yra grindjuostės linijoje, todėl netrikdo interjero. Jį galima pritaikyti spalvoms, apdailai ir patalpos architektūrai, išlaikant patogią prieigą patikrai ir priežiūrai.','THERMODUL kütteelement paikneb põrandaliistu joonel ega häiri interjööri. Seda saab sobitada värvide, viimistluse ja ruumi arhitektuuriga, säilitades lihtsa ligipääsu kontrolliks ja hoolduseks.'),
            'image' => thermodul_remote_img('dzivojama-istaba/dz4.jpg')
        ),
    );
}

function thermodul_home_data() {
    return thermodul_cached('home_data', function() {
        $gallery = thermodul_gallery_groups();
        $models = array_map(function($m) { $m['url'] = home_url('/' . $m['slug'] . '/'); return $m; }, thermodul_models());
        return array(
            'heroImage' => thermodul_asset_img('demo/hero-interior.jpg'),
            'diagramImage' => thermodul_asset_img('demo/how-diagram.jpg'),
            'contactImage' => thermodul_asset_img('demo/contact-interior.jpg'),
            'models' => $models,
            'gallery' => thermodul_featured_gallery_items(),
            'galleryGroups' => $gallery,
            'contactUrl' => home_url('/kontakti/'),
            'galleryUrl' => home_url('/galerija/'),
        );
    });
}

function thermodul_model_by_slug($slug) { foreach (thermodul_models() as $model) { if ($model['slug'] === $slug) { return $model; } } return null; }

function thermodul_gallery_shortcode($atts = array()) {
    $atts = shortcode_atts(array(
        'group' => '',
        'limit' => 0,
        'grouped' => 'yes',
        'ajax' => 'no',
        'compact' => 'no',
        'step' => 12,
        'button_label' => 'Ielādēt vēl attēlus',
    ), $atts, 'thermodul_gallery');

    $groups = thermodul_gallery_groups();
    $limit = intval($atts['limit']);
    $step  = max(1, intval($atts['step']));
    $compact = $atts['compact'] === 'yes';
    $group_filter = sanitize_text_field($atts['group']);
    $grid_class = 'td-gallery-grid td-gallery-grid-large' . ($compact ? ' td-gallery-grid-compact' : '');

    if ($atts['ajax'] === 'yes') {
        $flat = thermodul_gallery_flat_items($group_filter);
        $initial = $limit > 0 ? $limit : ($compact ? 8 : 12);
        $total = count($flat);
        $showcase_class = 'td-gallery-showcase' . ($compact ? ' td-gallery-showcase--compact' : '');
        $html = '<div class="'.esc_attr($showcase_class).'" data-total="'.esc_attr($total).'" data-step="'.esc_attr($step).'" data-button-label="'.esc_attr($atts['button_label']).'"'.($group_filter ? ' data-group="'.esc_attr($group_filter).'"' : '').'>';
        $html .= '<div class="'.esc_attr($grid_class).'" data-ajax-gallery="1">';
        foreach (array_slice($flat, 0, $initial) as $item) {
            $html .= thermodul_gallery_card($item, $compact ? thermodul_i18n('Skatīt','View','Смотреть','Žiūrėti','Vaata') : thermodul_i18n('Skatīt attēlu','View image','Смотреть изображение','Žiūrėti nuotrauką','Vaata pilti'));
        }
        $html .= '</div>';
        return $html . '</div>';
    }

    $html = '<div class="td-gallery-showcase'.($compact ? ' td-gallery-showcase--compact' : '').'">';
    foreach ($groups as $group => $images) {
        if ($group_filter && strtolower($group_filter) !== strtolower($group)) { continue; }
        if ($limit > 0) { $images = array_slice($images, 0, $limit); }
        if ($atts['grouped'] !== 'no') {
            $html .= '<section class="td-gallery-section"><div class="td-gallery-section-head"><h2>'.esc_html($group).'</h2><span>'.count($images).' attēli</span></div>';
        }
        $html .= '<div class="'.esc_attr($grid_class).'">';
        foreach ($images as $i => $src) {
            $html .= thermodul_gallery_card(array('group'=>$group,'src'=>$src,'index'=>$i+1), $compact ? thermodul_i18n('Skatīt','View','Смотреть','Žiūrėti','Vaata') : thermodul_i18n('Skatīt attēlu','View image','Смотреть изображение','Žiūrėti nuotrauką','Vaata pilti'));
        }
        $html .= '</div>';
        if ($atts['grouped'] !== 'no') { $html .= '</section>'; }
    }
    return $html . '</div>';
}

add_shortcode('thermodul_gallery', 'thermodul_gallery_shortcode');

function thermodul_legacy_page_url($slug, $fallback_anchor = '') {
    $page = get_page_by_path($slug, OBJECT, 'page');
    if ($page && $page->post_status === 'publish') {
        $lang = thermodul_current_lang();
        if ($lang === 'lv') { return get_permalink($page->ID); }
        if (function_exists('pll_get_post')) {
            $translated_id = pll_get_post($page->ID, $lang);
            if ($translated_id) { return get_permalink($translated_id); }
        }
    }
    return thermodul_anchor_url($fallback_anchor);
}

function thermodul_models_shortcode() {
    ob_start(); echo '<div class="row g-4 td-models-grid">';
    foreach (thermodul_models() as $m) { ?>
        <div class="col-md-6 col-xl-4"><article class="td-card td-model-card">
            <button class="td-model-image-link td-open-card" type="button" data-modal-title="<?php echo esc_attr($m['title']); ?>" data-modal-body="<?php echo esc_attr($m['text'] . ' ' . $m['meta']); ?>" data-modal-image="<?php echo esc_url(!empty($m['modal_img']) ? $m['modal_img'] : $m['img']); ?>"><?php echo thermodul_picture($m['img'], $m['title'], 'td-model-img', 'lazy'); ?></button>
            <div class="td-card-body"><h3><?php echo esc_html($m['title']); ?></h3><p><strong><?php echo esc_html($m['meta']); ?></strong></p><p><?php echo esc_html($m['text']); ?></p><a class="td-link" href="<?php echo esc_url(thermodul_legacy_page_url($m['slug'], 'modeli')); ?>"><?php echo esc_html(thermodul_i18n('Skatīt vairāk','View more','Подробнее','Žiūrėti daugiau','Vaata rohkem')); ?> →</a></div></article></div>
    <?php } echo '</div>'; return ob_get_clean();
}
add_shortcode('thermodul_models', 'thermodul_models_shortcode');


function thermodul_home_blocks() {
    return '<!-- wp:html --><div class="td-admin-edit-note"><strong>THERMODUL demo:</strong> the front page is rendered by editable theme sections and shortcodes. Use THERMODUL block patterns/shortcodes to add custom content below if needed.</div><!-- /wp:html -->';
}

function thermodul_page_content($slug) {
    $model = thermodul_model_by_slug($slug);
    if ($model) {
        $safety = '';
        if ($slug === 'elektriskais-modelis') {
            $safety = '<!-- wp:paragraph --><p><strong>Drošība:</strong> saskaņā ar pašreizējās THERMODUL lapas norādi elektrisko modeli nedrīkst uzstādīt vannas istabā. Elektroinstalācija jāveic atbilstoši piemērojamām drošības prasībām.</p><!-- /wp:paragraph -->';
        }
        return '<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="'.esc_url($model['img']).'" alt="'.esc_attr($model['title']).'" /></figure><!-- /wp:image -->'
        .'<!-- wp:paragraph --><p>'.esc_html($model['text']).'</p><!-- /wp:paragraph -->'
        .'<!-- wp:list --><ul><li>Viegli savietojams ar interjeru un esošu apkures sistēmu.</li><li>Vienmērīga siltuma sadale gar sienām un telpas perimetru.</li><li>Diskrēts korpuss ar plašām krāsu un apdares iespējām.</li></ul><!-- /wp:list -->'
        .'<!-- wp:paragraph --><p><strong>'.esc_html($model['meta']).'</strong></p><!-- /wp:paragraph -->'
        .$safety
        .'<!-- wp:shortcode -->[thermodul_contact_form]<!-- /wp:shortcode -->';
    }
    $map = array(
        'par-uznemumu' => '<!-- wp:heading --><h2>Par uzņēmumu</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Jau vairāk nekā 16 gadus Baltijas tirgū SIA “AQUADOCK BALTIJA” piedāvā grīdlīstes apsildes sistēmu THERMODUL. Sistēma atbilst Eiropas Savienības normām EN442 un ļauj radiatoru vietā izmantot estētisku, drošu un energoefektīvu perimetra apsildi.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>THERMODUL ir piemērots jaunbūvēm, renovācijām, sabiedriskām telpām un privātmājām. Nelielais izmērs netraucē mēbelēm, saglabā tīras interjera līnijas un palīdz uzturēt veselīgu mikroklimatu.</p><!-- /wp:paragraph --><!-- wp:shortcode -->[thermodul_models]<!-- /wp:shortcode -->',
        'sertifikacija' => '<!-- wp:heading --><h2>Mūsu idejām ir konkrēta forma un sertifikācija</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Pašreizējā THERMODUL vietne norāda, ka sistēma ir sertificēta saskaņā ar Eiropas apkures efektivitātes normām un atbilst EN442 prasībām. Vietnē ir publicēts EN442 materiāls un sertifikācijas mērījumu attēls.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Pašreizējā sertifikācijas lapā ir minēti arī ekoloģiskās būvniecības principi un “PLAN EXPO FAIR 2006” Dublinā saņemta balva. Pirms papildu CE, RoHS vai IP aizsardzības klases apgalvojumu publicēšanas tie jāpārbauda pret aktuālajiem ražotāja dokumentiem.</p><!-- /wp:paragraph --><!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><div class="td-card td-cert-card"><strong>EN442</strong><span>Apkures sistēmu prasības</span></div></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><div class="td-card td-cert-card"><strong>EU</strong><span>Eiropas apkures efektivitātes normas</span></div></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><div class="td-card td-cert-card"><strong>ECO</strong><span>Ekoloģiskās būvniecības principi</span></div></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><div class="td-card td-cert-card"><strong>2006</strong><span>Plan Expo Fair, Dublina</span></div></div><!-- /wp:column --></div><!-- /wp:columns -->',
        'galerija' => '<!-- wp:heading --><h2>THERMODUL galerija</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Uzstādījumu piemēri dzīvojamām istabām, kafejnīcām un restorāniem, muzejiem, ofisiem un veselības iestādēm, palīgtelpām, reliģiskām telpām, skolām, bērnudārziem un veikaliem. Demo importētājs mēģina ielādēt šos attēlus WordPress Media Library un izveidot AVIF versijas.</p><!-- /wp:paragraph --><!-- wp:shortcode -->[thermodul_gallery grouped="yes" ajax="yes"]<!-- /wp:shortcode -->',
        'kontakti' => '<!-- wp:group {"className":"td-contact-page-grid","layout":{"type":"constrained"}} --><div class="wp-block-group td-contact-page-grid"><!-- wp:heading --><h2>Pieprasīt informāciju</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Aizpildiet formu, un THERMODUL speciālists sazināsies ar jums par piemērotāko risinājumu jūsu projektam.</p><!-- /wp:paragraph --><!-- wp:shortcode -->[thermodul_contact_form recipient="info@thermodul.eu"]<!-- /wp:shortcode --><!-- wp:heading --><h2>Kontakti</h2><!-- /wp:heading --><!-- wp:shortcode -->[thermodul_contacts]<!-- /wp:shortcode --></div><!-- /wp:group -->',
        'lapas-karte' => '<!-- wp:heading --><h2>Lapas karte</h2><!-- /wp:heading --><!-- wp:paragraph --><p>THERMODUL informācija vienā lapā: sākums, priekšrocības, darbības princips, modeļi, galerija, sertifikācija un kontakti.</p><!-- /wp:paragraph --><!-- wp:list --><ul><li>Sākums</li><li>Kāpēc izvēlēties?</li><li>Kā tas darbojas</li><li>Modeļi: Ūdens, Elektriskais, Duālais, Abpusējais, Dubultais horizontālais, Dubultais vertikālais</li><li>Galerija</li><li>Sertifikācija</li><li>Kontakti un pieprasījuma forma</li></ul><!-- /wp:list -->' ,
    );
    return isset($map[$slug]) ? $map[$slug] : '';
}

function thermodul_contacts_shortcode() {
    $contacts = array(
        array('AQUADOCK BALTIJA SIA','Dārza iela 17, Ikšķile, LV-5052, Latvija','+371 29256299','Normunds Rasļenoks'),
        array('SB SIA','Maskavas iela 444b, LV-1063, Latvija','+371 26461214','Māris Rubīns'),
        array('SB SIA','Kārļa iela 7, Ventspils, LV-3601, Latvija','+371 29666906','Aija Zandberga-Kornijanova'),
        array('SILTUMTEHNIKA SIA','Atbrīvošanas aleja 163, Rēzekne, LV-4604, Latvija','+371 29149923','Uldis Kazāks'),
    );
    $html = '<div class="row g-4">'; foreach ($contacts as $c) { $html .= '<div class="col-md-6"><div class="td-contact-card"><h3>'.esc_html($c[0]).'</h3><p>'.esc_html($c[1]).'</p><p><strong>T</strong> <a href="tel:'.esc_attr(str_replace(' ','',$c[2])).'">'.esc_html($c[2]).'</a></p><p>'.esc_html($c[3]).'</p></div></div>'; } return $html.'</div>';
}
add_shortcode('thermodul_contacts', 'thermodul_contacts_shortcode');

function thermodul_sitemap_shortcode() {
    $items = array(
        'Siltās grīdlīstes THERMODUL'=>thermodul_anchor_url('sakums'),
        'Kāpēc izvēlēties?'=>thermodul_anchor_url('kapec'),
        'Kā tas darbojas'=>thermodul_anchor_url('ka-tas-darbojas'),
        'Modeļi'=>thermodul_anchor_url('modeli'),
        'Galerija'=>thermodul_anchor_url('galerija'),
        'Sertifikācija'=>thermodul_anchor_url('sertifikacija'),
        'Kontakti'=>thermodul_anchor_url('pieprasijums')
    );
    foreach (thermodul_models() as $m) { $items[$m['title']] = home_url('/'.$m['slug'].'/'); }
    $out = '<div class="td-card"><div class="td-card-body"><ul>';
    foreach ($items as $label=>$url) { $out .= '<li><a href="'.esc_url($url).'">'.esc_html($label).'</a></li>'; }
    return $out.'</ul></div></div>';
}
add_shortcode('thermodul_sitemap', 'thermodul_sitemap_shortcode');

function thermodul_register_block_patterns() {
    if (!function_exists('register_block_pattern')) { return; }
    register_block_pattern('thermodul/cta-red', array('title'=>'THERMODUL CTA', 'categories'=>array('call-to-action'), 'content'=>'<!-- wp:group {"className":"td-cta","layout":{"type":"constrained"}} --><div class="wp-block-group td-cta"><!-- wp:heading --><h2>Vai vēlaties uzzināt vairāk par THERMODUL?</h2><!-- /wp:heading --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link" href="/#pieprasijums">Pieprasīt informāciju</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:group -->'));
    register_block_pattern('thermodul/gallery', array('title'=>'THERMODUL Gallery Grid', 'categories'=>array('gallery'), 'content'=>'<!-- wp:shortcode -->[thermodul_gallery limit="12"]<!-- /wp:shortcode -->'));
    register_block_pattern('thermodul/models', array('title'=>'THERMODUL Models', 'categories'=>array('columns'), 'content'=>'<!-- wp:shortcode -->[thermodul_models]<!-- /wp:shortcode -->'));
    register_block_pattern('thermodul/contact-form', array('title'=>'THERMODUL Ajax Contact Form', 'categories'=>array('call-to-action'), 'content'=>'<!-- wp:shortcode -->[thermodul_contact_form]<!-- /wp:shortcode -->'));
    register_block_pattern('thermodul/search', array('title'=>'THERMODUL Ajax Search', 'categories'=>array('text'), 'content'=>'<!-- wp:shortcode -->[thermodul_ajax_search]<!-- /wp:shortcode -->'));
}
add_action('init', 'thermodul_register_block_patterns');


function thermodul_all_demo_image_paths() {
    $paths = array();
    foreach (thermodul_gallery_raw_paths() as $group => $items) { foreach ($items as $p) { $paths[] = $p; } }
    foreach (thermodul_model_image_sources() as $path) {
        $paths[] = 'https://www.thermodul.eu/wp-content/uploads/' . ltrim($path, '/');
    }
    $paths[] = thermodul_uploads_img('2018/03/ka-THERMODUL-darbojas.jpg');
    $paths[] = 'https://www.thermodul.eu/wp-content/uploads/2014/01/award_min.jpg';
    $paths[] = 'https://www.thermodul.eu/wp-content/uploads/2014/01/inbioedilizia.jpg';
    $paths[] = 'https://www.thermodul.eu/wp-content/uploads/2014/01/EN442-b.jpg';
    return array_values(array_unique($paths));
}
function thermodul_sideload_demo_images($limit = 80) {
    if (!current_user_can('upload_files') && !current_user_can('manage_options')) { return; }
    if (!function_exists('media_handle_sideload')) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
    }
    @set_time_limit(240);
    $map = get_option('thermodul_demo_image_map', array());
    if (!is_array($map)) { $map = array(); }
    $done = 0;
    foreach (thermodul_all_demo_image_paths() as $path) {
        if ($done >= $limit) { break; }
        $key = preg_match('#^https?://#i', $path) ? md5($path) : ltrim($path, '/');
        if (!empty($map[$key]['url'])) { continue; }
        $remote = preg_match('#^https?://#i', $path) ? $path : apply_filters('thermodul_remote_img_base', 'https://www.thermodul.eu/wp-content/gallery/') . ltrim($path, '/');
        $tmp = download_url($remote, 25);
        if (is_wp_error($tmp)) { continue; }
        $name = sanitize_file_name(basename(parse_url($remote, PHP_URL_PATH)) ?: ('thermodul-' . md5($remote) . '.jpg'));
        $file = array('name'=>$name, 'tmp_name'=>$tmp);
        $id = media_handle_sideload($file, 0, 'THERMODUL demo image');
        if (is_wp_error($id)) { @unlink($tmp); continue; }
        $url = wp_get_attachment_url($id);
        $file_path = get_attached_file($id);
        $avif_path = $file_path ? thermodul_generate_avif_variant($file_path) : false;
        $avif_url = '';
        if ($avif_path) {
            $uploads = wp_get_upload_dir();
            $avif_url = str_replace($uploads['basedir'], $uploads['baseurl'], $avif_path);
        }
        $map[$key] = array('id'=>$id, 'url'=>$url, 'avif'=>$avif_url, 'source'=>$remote);
        $done++;
    }
    update_option('thermodul_demo_image_map', $map, false);
}

function thermodul_after_switch() { thermodul_create_demo_pages(); }
add_action('after_switch_theme', 'thermodul_after_switch');

function thermodul_create_demo_pages() {
    thermodul_sideload_demo_images(90);
    $existing = get_page_by_path('sakums');
    if ($existing) {
        $front_id = $existing->ID;
        wp_update_post(array('ID'=>$front_id, 'post_title'=>'Sākums', 'post_content'=>'', 'post_status'=>'publish'));
    } else {
        $front_id = wp_insert_post(array('post_title'=>'Sākums', 'post_name'=>'sakums', 'post_type'=>'page', 'post_status'=>'publish', 'post_content'=>''));
    }
    if (!empty($front_id) && !is_wp_error($front_id)) { update_option('show_on_front','page'); update_option('page_on_front', $front_id); }
    thermodul_cleanup_duplicate_demo_pages();
    thermodul_remove_extra_demo_pages();
    thermodul_ensure_polylang_front_pages();
    thermodul_clear_theme_cache();
    $menu_name = 'THERMODUL galvenā izvēlne';
    $menu = wp_get_nav_menu_object($menu_name); $menu_id = $menu ? $menu->term_id : wp_create_nav_menu($menu_name);
    if ($menu_id && !is_wp_error($menu_id)) {
        $wanted = array('Sākums'=>'/#sakums', 'Kāpēc izvēlēties?'=>'/#kapec', 'Kā tas darbojas'=>'/#ka-tas-darbojas', 'Modeļi'=>'/#modeli', 'Galerija'=>'/#galerija', 'Sertifikācija'=>'/#sertifikacija', 'Kontakti'=>'/#pieprasijums');
        $items = wp_get_nav_menu_items($menu_id); $existing_titles = array();
        if ($items) {
            foreach ($items as $it) {
                if (isset($wanted[$it->title])) { wp_update_nav_menu_item($menu_id, $it->ID, array('menu-item-title'=>$it->title, 'menu-item-url'=>home_url($wanted[$it->title]), 'menu-item-status'=>'publish')); }
                $existing_titles[$it->title] = true;
            }
        }
        foreach ($wanted as $label=>$url) { if (empty($existing_titles[$label])) { wp_update_nav_menu_item($menu_id, 0, array('menu-item-title'=>$label, 'menu-item-url'=>home_url($url), 'menu-item-status'=>'publish')); } }
        $loc = get_theme_mod('nav_menu_locations'); if (!is_array($loc)) { $loc = array(); }
        $loc['primary'] = $menu_id; $loc['footer'] = $menu_id; set_theme_mod('nav_menu_locations', $loc);
    }
    flush_rewrite_rules(false);
}
function thermodul_legacy_page_titles() {
    return array(
        'par-uznemumu' => 'Par uzņēmumu',
        'sertifikacija' => 'Sertifikācija',
        'galerija' => 'Galerija',
        'kontakti' => 'Kontakti',
        'udens-modelis' => 'ŪDENS MODELIS',
        'elektriskais-modelis' => 'ELEKTRISKAIS MODELIS',
        'dualais-modelis' => 'DUĀLAIS MODELIS',
        'abpusejais-modelis' => 'ABPUSĒJAIS MODELIS',
        'dubultais-horizontalais-modelis' => 'DUBULTAIS HORIZONTĀLAIS MODELIS',
        'dubultais-vertikalais-modelis' => 'DUBULTAIS VERTIKĀLAIS MODELIS',
        'lapas-karte' => 'Lapas karte',
    );
}
function thermodul_ensure_legacy_pages() {
    foreach (thermodul_legacy_page_titles() as $slug => $title) {
        $page = get_page_by_path($slug, OBJECT, 'page');
        $content = thermodul_page_content($slug);
        if ($page) {
            $update = array('ID' => $page->ID);
            $needs_update = false;
            if ($page->post_status === 'trash') { $update['post_status'] = 'publish'; $needs_update = true; }
            if (trim((string) $page->post_content) === '' && $content !== '') { $update['post_content'] = $content; $needs_update = true; }
            if ($needs_update) { wp_update_post($update); }
            continue;
        }
        wp_insert_post(array(
            'post_title' => $title,
            'post_name' => $slug,
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_content' => $content,
        ));
    }
}
function thermodul_remove_extra_demo_pages() {
    // v3.8: old indexed THERMODUL pages are migration assets, not disposable demo pages.
    thermodul_ensure_legacy_pages();
}

function thermodul_ensure_polylang_front_pages() {
    if (!function_exists('pll_set_post_language') || !function_exists('pll_save_post_translations')) { return false; }
    $front_id = (int) get_option('page_on_front');
    if (!$front_id || get_post_type($front_id) !== 'page') {
        $front = get_page_by_path('sakums', OBJECT, 'page');
        if (!$front) { return false; }
        $front_id = (int) $front->ID;
    }
    $specs = array(
        'lv' => array('title'=>'Sākums','slug'=>'sakums'),
        'en' => array('title'=>'Home','slug'=>'home'),
        'ru' => array('title'=>'Главная','slug'=>'glavnaya'),
        'lt' => array('title'=>'Pradžia','slug'=>'pradzia'),
        'et' => array('title'=>'Avaleht','slug'=>'avaleht'),
    );
    $translations = array('lv'=>$front_id);
    $changed = false;
    pll_set_post_language($front_id, 'lv');
    foreach ($specs as $lang=>$spec) {
        if ($lang === 'lv') { continue; }
        $id = function_exists('pll_get_post') ? (int) pll_get_post($front_id, $lang) : 0;
        if (!$id) {
            $existing = get_page_by_path($spec['slug'], OBJECT, 'page');
            $id = $existing ? (int) $existing->ID : 0;
        }
        if (!$id) {
            $id = wp_insert_post(array('post_title'=>$spec['title'], 'post_name'=>$spec['slug'], 'post_type'=>'page', 'post_status'=>'publish', 'post_content'=>''));
            if (is_wp_error($id)) { continue; }
            $changed = true;
        } else {
            $post = get_post($id);
            if ($post && ($post->post_status !== 'publish' || $post->post_title !== $spec['title'])) {
                wp_update_post(array('ID'=>$id,'post_status'=>'publish','post_title'=>$spec['title']));
                $changed = true;
            }
        }
        pll_set_post_language($id, $lang);
        $translations[$lang] = (int) $id;
    }
    if (count($translations) === count($specs)) {
        pll_save_post_translations($translations);
        update_option('show_on_front', 'page');
        update_option('page_on_front', $front_id);
        if ($changed) { flush_rewrite_rules(false); }
        return true;
    }
    return false;
}

function thermodul_v310_safe_migration() {
    if (!is_admin() || !current_user_can('manage_options')) { return; }
    $done = (string) get_option('thermodul_theme_data_version', '');
    if ($done === THERMODUL_THEME_VERSION) { return; }
    // Safe-only migration: preserve/create the legacy URLs and keep redirects off.
    thermodul_ensure_legacy_pages();
    thermodul_ensure_polylang_front_pages();
    thermodul_clear_theme_cache();
    if (get_option('thermodul_redirect_legacy_pages', null) === null) {
        add_option('thermodul_redirect_legacy_pages', 0, '', false);
    }
    update_option('thermodul_theme_data_version', THERMODUL_THEME_VERSION, false);
}
add_action('admin_init', 'thermodul_v310_safe_migration', 30);


/* v2.5 cleanup: prevent old demo blocks from stacking all sections on one page after repeated imports. */
function thermodul_cleanup_duplicate_demo_pages() {
    $front = get_page_by_path('sakums');
    if ($front && trim($front->post_content) !== '') {
        wp_update_post(array('ID' => $front->ID, 'post_content' => ''));
    }
    $contact = get_page_by_path('kontakti');
    if ($contact) {
        $clean = thermodul_page_content('kontakti');
        if (strpos($contact->post_content, '[thermodul_models]') !== false || strpos($contact->post_content, '[thermodul_gallery') !== false || strpos($contact->post_content, '[thermodul_ajax_search]') !== false) {
            wp_update_post(array('ID' => $contact->ID, 'post_content' => $clean, 'post_title' => 'Kontakti', 'post_status' => 'publish'));
        }
    }
}

function thermodul_body_classes($classes) {
    if (is_front_page()) { $classes[] = 'td-is-front-page'; }
    if (is_page('kontakti')) { $classes[] = 'td-is-contact-page'; }
    if (is_page('galerija')) { $classes[] = 'td-is-gallery-page'; }
    return $classes;
}
add_filter('body_class', 'thermodul_body_classes');

function thermodul_admin_menu() { add_theme_page('THERMODUL Demo Import', 'THERMODUL Demo Import', 'manage_options', 'thermodul-demo-import', 'thermodul_demo_import_page'); }
add_action('admin_menu', 'thermodul_admin_menu');
function thermodul_demo_import_page() {
    if (isset($_POST['thermodul_demo_import']) && check_admin_referer('thermodul_demo_import')) { thermodul_create_demo_pages(); echo '<div class="notice notice-success"><p>THERMODUL one-page homepage, anchor menu, media gallery and SEO settings have been refreshed.</p></div>'; }
    $captcha_status = function_exists('thermodul_hcaptcha_ready') && thermodul_hcaptcha_ready()
        ? '<span style="color:#138a43;font-weight:700">Ready - WP BBuilder hCaptcha keys detected.</span>'
        : '<span style="color:#b42318;font-weight:700">Not ready - enable hCaptcha and save both keys in WP BBuilder settings.</span>';
    echo '<div class="wrap"><h1>THERMODUL Demo Import</h1><p>This refreshes the THERMODUL homepage, anchor menu, gallery media and compressed AVIF variants. Existing indexed legacy pages are preserved/restored so old URLs keep working; optional redirects can be enabled separately under THERMODUL SEO & Analytics.</p><p><strong>Contact form hCaptcha:</strong> '.$captcha_status.'</p><form method="post">'; wp_nonce_field('thermodul_demo_import'); submit_button('Import / Refresh THERMODUL demo content', 'primary', 'thermodul_demo_import'); echo '</form></div>';
}


/* THERMODUL v2.2 performance, AJAX, language and editable block helpers */
function thermodul_supported_languages() {
    return array(
        'lv' => array('label' => 'LV', 'name' => 'Latviski', 'locale' => 'lv_LV', 'hreflang' => 'lv-LV'),
        'en' => array('label' => 'EN', 'name' => 'English', 'locale' => 'en_US', 'hreflang' => 'en'),
        'ru' => array('label' => 'RU', 'name' => 'Русский', 'locale' => 'ru_RU', 'hreflang' => 'ru'),
        'lt' => array('label' => 'LT', 'name' => 'Lietuvių', 'locale' => 'lt_LT', 'hreflang' => 'lt-LT'),
        'et' => array('label' => 'ET', 'name' => 'Eesti', 'locale' => 'et', 'hreflang' => 'et-EE'),
    );
}
function thermodul_current_lang() {
    $supported = array_keys(thermodul_supported_languages());
    $q_lang = get_query_var('thermodul_lang'); if ($q_lang && in_array($q_lang, $supported, true)) { return $q_lang; }
    if (function_exists('pll_current_language')) { $lang = pll_current_language('slug'); if ($lang && in_array($lang, $supported, true)) { return $lang; } }
    $uri = isset($_SERVER['REQUEST_URI']) ? trim((string) $_SERVER['REQUEST_URI'], '/') : '';
    if (preg_match('#^(en|ru|lt|et)(/|$)#', $uri, $m)) { return $m[1]; }
    if (isset($_GET['lang']) && in_array($_GET['lang'], $supported, true)) { return sanitize_key($_GET['lang']); }
    return 'lv';
}
function thermodul_i18n($lv, $en = '', $ru = '', $lt = '', $et = '') {
    $lang = thermodul_current_lang();
    if ($lang === 'en') { return $en !== '' ? $en : $lv; }
    if ($lang === 'ru') { return $ru !== '' ? $ru : $lv; }
    if ($lang === 'lt') { return $lt !== '' ? $lt : $lv; }
    if ($lang === 'et') { return $et !== '' ? $et : $lv; }
    return $lv;
}

function thermodul_wpbb_option($key, $default = '') {
    // Prefer WP BBuilder's public helper. Fall back to its stored option array so
    // the frontend form remains protected even if the helper is not loaded yet.
    if (function_exists('wpbb_get_option')) {
        $value = wpbb_get_option($key, $default);
        if ($value !== null) { return $value; }
    }
    $settings = get_option('wpbb_settings', array());
    if (is_array($settings) && array_key_exists($key, $settings)) { return $settings[$key]; }
    return $default;
}
function thermodul_hcaptcha_enabled() {
    return (bool) thermodul_wpbb_option('hcaptcha_enabled', 0);
}
function thermodul_hcaptcha_site_key() {
    return thermodul_hcaptcha_enabled() ? trim((string) thermodul_wpbb_option('hcaptcha_site_key', '')) : '';
}
function thermodul_hcaptcha_secret_key() {
    return thermodul_hcaptcha_enabled() ? trim((string) thermodul_wpbb_option('hcaptcha_secret_key', '')) : '';
}
function thermodul_hcaptcha_ready() {
    return thermodul_hcaptcha_enabled() && thermodul_hcaptcha_site_key() !== '' && thermodul_hcaptcha_secret_key() !== '';
}
function thermodul_hcaptcha_message($key) {
    $messages = array(
        'complete' => thermodul_i18n('Lūdzu, pabeidziet hCaptcha pārbaudi.', 'Please complete the hCaptcha check.', 'Пожалуйста, пройдите проверку hCaptcha.', 'Užbaikite hCaptcha patikrą.', 'Palun läbige hCaptcha kontroll.'),
        'missing'  => thermodul_i18n('hCaptcha atslēgas nav pilnībā iestatītas WP BBuilder iestatījumos.', 'hCaptcha keys are not fully configured in WP BBuilder settings.', 'Ключи hCaptcha не полностью настроены в параметрах WP BBuilder.', 'hCaptcha raktai nėra pilnai sukonfigūruoti WP BBuilder nustatymuose.', 'hCaptcha võtmed pole WP BBuilder seadetes täielikult seadistatud.'),
        'failed'   => thermodul_i18n('hCaptcha pārbaude neizdevās. Lūdzu, mēģiniet vēlreiz.', 'hCaptcha verification failed. Please try again.', 'Проверка hCaptcha не удалась. Попробуйте еще раз.', 'hCaptcha patikra nepavyko. Bandykite dar kartą.', 'hCaptcha kontroll ebaõnnestus. Palun proovige uuesti.'),
    );
    return $messages[$key] ?? $messages['failed'];
}
function thermodul_verify_hcaptcha() {
    if (!thermodul_hcaptcha_enabled()) { return true; }
    $secret = thermodul_hcaptcha_secret_key();
    $site = thermodul_hcaptcha_site_key();
    if (!$secret || !$site) { return new WP_Error('thermodul_hcaptcha_missing', thermodul_hcaptcha_message('missing')); }
    $token = isset($_POST['h-captcha-response']) ? sanitize_text_field(wp_unslash($_POST['h-captcha-response'])) : '';
    if (!$token) { return new WP_Error('thermodul_hcaptcha_token', thermodul_hcaptcha_message('complete')); }
    $response = wp_remote_post('https://api.hcaptcha.com/siteverify', array(
        'timeout' => 12,
        'body' => array(
            'secret' => $secret,
            'response' => $token,
            'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
            'sitekey' => $site,
        ),
    ));
    if (is_wp_error($response)) { return new WP_Error('thermodul_hcaptcha_request', thermodul_hcaptcha_message('failed')); }
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($body['success'])) { return new WP_Error('thermodul_hcaptcha_failed', thermodul_hcaptcha_message('failed')); }
    return true;
}
function thermodul_hcaptcha_widget() {
    $site_key = thermodul_hcaptcha_site_key();
    if (!$site_key) { return ''; }
    return '<div class="td-hcaptcha-wrap"><div class="h-captcha" data-sitekey="'.esc_attr($site_key).'" data-theme="light"></div><input type="hidden" name="wpbb_captcha_enabled" value="1"><input type="hidden" name="wpbb_captcha_provider" value="hcaptcha"></div>';
}
function thermodul_language_home_url($lang = null) {
    $lang = $lang ?: thermodul_current_lang();
    if (function_exists('pll_home_url')) { $url = pll_home_url($lang); if ($url) { return $url; } }
    return $lang === 'lv' ? home_url('/') : home_url('/' . $lang . '/');
}
function thermodul_language_url_for_current_object($lang) {
    if (is_front_page() || get_query_var('thermodul_onepage')) { return thermodul_language_home_url($lang); }
    if (is_singular() && function_exists('pll_get_post')) {
        $translated_id = pll_get_post(get_queried_object_id(), $lang);
        if ($translated_id) { return get_permalink($translated_id); }
    }
    if (is_singular() && $lang === 'lv') { return get_permalink(get_queried_object_id()); }
    return thermodul_language_home_url($lang);
}
function thermodul_language_switcher($class = 'td-lang-switch') {
    $langs = thermodul_supported_languages(); $current = thermodul_current_lang();
    echo '<div class="' . esc_attr($class) . '" aria-label="Language switcher">';
    foreach ($langs as $slug=>$info) { $active = $slug === $current ? ' is-active' : ''; echo '<a data-lang="'.esc_attr($slug).'" class="'.esc_attr(trim($active)).'" title="'.esc_attr($info['name']).'" hreflang="'.esc_attr($info['hreflang']).'" href="'.esc_url(thermodul_language_url_for_current_object($slug)).'">'.esc_html($info['label']).'</a>'; }
    echo '</div>';
}
function thermodul_hreflang_meta() {
    $all_languages = is_front_page() || get_query_var('thermodul_onepage') || (is_singular() && function_exists('pll_get_post'));
    if ($all_languages) {
        foreach (thermodul_supported_languages() as $slug=>$info) {
            $url = thermodul_language_url_for_current_object($slug);
            if ($url) { echo '<link rel="alternate" hreflang="'.esc_attr($info['hreflang']).'" href="'.esc_url($url).'">' . "\n"; }
        }
        echo '<link rel="alternate" hreflang="x-default" href="'.esc_url(thermodul_language_url_for_current_object('lv')).'">' . "\n";
    }
}
add_action('wp_head', 'thermodul_hreflang_meta', 4);
function thermodul_register_polylang_strings() {
    if (!function_exists('pll_register_string')) { return; }
    $strings = array(
        'topbar' => 'Siltās grīdlīstes apsildes sistēma',
        'search' => 'Meklēt',
        'request_info' => 'Pieprasīt informāciju',
        'contact_success' => 'Paldies! Ziņa nosūtīta.',
        'contact_error' => 'Neizdevās nosūtīt formu.',
    );
    foreach ($strings as $name=>$value) { pll_register_string('thermodul_' . $name, $value, 'THERMODUL Theme'); }
}
add_action('init', 'thermodul_register_polylang_strings');

function thermodul_avif_url_for($url) {
    if (!$url || preg_match('/\.avif(\?.*)?$/i', $url)) { return $url; }
    $map = get_option('thermodul_demo_image_map', array());
    if (is_array($map)) { foreach ($map as $m) { if (!empty($m['url']) && $m['url'] === $url && !empty($m['avif'])) { return $m['avif']; } } }
    $uploads = wp_get_upload_dir();
    if (!empty($uploads['baseurl']) && strpos($url, $uploads['baseurl']) === 0) {
        $rel = ltrim(str_replace($uploads['baseurl'], '', $url), '/');
        $file = trailingslashit($uploads['basedir']) . $rel;
        $avif = preg_replace('/\.(jpe?g|png|webp)$/i', '.avif', $file);
        if ($avif && file_exists($avif)) { return preg_replace('/\.(jpe?g|png|webp)$/i', '.avif', $url); }
    }
    $theme_url = get_template_directory_uri();
    if (strpos($url, $theme_url) === 0) {
        $rel = ltrim(str_replace($theme_url, '', $url), '/');
        $file = get_template_directory() . '/' . $rel;
        $avif = preg_replace('/\.(jpe?g|png|webp)$/i', '.avif', $file);
        if ($avif && file_exists($avif)) { return preg_replace('/\.(jpe?g|png|webp)$/i', '.avif', $url); }
    }
    return '';
}
function thermodul_generate_avif_variant($file) {
    if (!file_exists($file) || !preg_match('/\.(jpe?g|png|webp)$/i', $file)) { return false; }
    $avif = preg_replace('/\.(jpe?g|png|webp)$/i', '.avif', $file);
    if (file_exists($avif)) { return $avif; }
    $editor = wp_get_image_editor($file);
    if (!is_wp_error($editor) && method_exists($editor, 'set_quality')) {
        $editor->set_quality(55);
        $saved = $editor->save($avif, 'image/avif');
        if (!is_wp_error($saved) && file_exists($avif)) { return $avif; }
    }
    return false;
}
function thermodul_generate_avif_on_upload($metadata, $attachment_id) {
    $file = get_attached_file($attachment_id);
    if ($file) { thermodul_generate_avif_variant($file); }
    if (!empty($metadata['sizes']) && is_array($metadata['sizes'])) {
        $dir = dirname($file);
        foreach ($metadata['sizes'] as $size) { if (!empty($size['file'])) { thermodul_generate_avif_variant($dir . '/' . $size['file']); } }
    }
    return $metadata;
}
add_filter('wp_generate_attachment_metadata', 'thermodul_generate_avif_on_upload', 20, 2);

function thermodul_local_search_index() {
    return thermodul_cached('local_search_index', function() {
        $items = array(
            array('title'=>'Siltās grīdlīstes THERMODUL','url'=>home_url('/#sakums'),'type'=>'Sadaļa','excerpt'=>'Komforts, dizains un efektīva apsildes sistēma.'),
            array('title'=>'Kāpēc izvēlēties?','url'=>home_url('/#kapec'),'type'=>'Sadaļa','excerpt'=>'Vienmērīgs siltums, veselīgs gaiss, energoefektivitāte un dizains.'),
            array('title'=>'Kā tas darbojas','url'=>home_url('/#ka-tas-darbojas'),'type'=>'Sadaļa','excerpt'=>'Siltuma starojums, grīdlīstes sildelements un komforts.'),
            array('title'=>'Modeļi','url'=>home_url('/#modeli'),'type'=>'Sadaļa','excerpt'=>'Ūdens, elektriskais, duālais un dubultais modelis.'),
            array('title'=>'Galerija','url'=>thermodul_anchor_url('galerija'),'type'=>'Sadaļa','excerpt'=>'Uzstādījumu galerija un objektu kategorijas.'),
            array('title'=>'Kontakti','url'=>thermodul_anchor_url('pieprasijums'),'type'=>'Sadaļa','excerpt'=>'Saziņas forma, tālrunis un e-pasts.'),
        );
        foreach (thermodul_models() as $m) { $items[] = array('title'=>$m['title'], 'url'=>thermodul_legacy_page_url($m['slug'], 'modeli'), 'type'=>thermodul_i18n('Modelis','Model','Модель','Modelis','Mudel'), 'excerpt'=>$m['text']); }
        foreach (thermodul_posts_data() as $p) { $items[] = array('title'=>$p['title'], 'url'=>thermodul_anchor_url('padomi'), 'type'=>thermodul_i18n('Raksts','Post','Статья','Straipsnis','Artikkel'), 'excerpt'=>$p['excerpt']); }
        return $items;
    });
}
function thermodul_ajax_search() {
    check_ajax_referer('thermodul_search', 'nonce');
    $term = sanitize_text_field(wp_unslash($_GET['s'] ?? ($_GET['term'] ?? '')));
    $needle = function_exists('mb_strtolower') ? mb_strtolower($term) : strtolower($term);
    $items = array();
    if ($needle !== '') {
        foreach (thermodul_local_search_index() as $item) {
            $hay = implode(' ', $item); $hay = function_exists('mb_strtolower') ? mb_strtolower($hay) : strtolower($hay);
            if (strpos($hay, $needle) !== false) { $items[] = $item; }
        }
        $q = new WP_Query(array('s'=>$term,'post_type'=>array('post','page'),'post_status'=>'publish','posts_per_page'=>8));
        while ($q->have_posts()) { $q->the_post(); $items[] = array('title'=>get_the_title(),'url'=>get_permalink(),'type'=>get_post_type(),'excerpt'=>wp_trim_words(get_the_excerpt() ?: wp_strip_all_tags(get_the_content()), 22)); }
        wp_reset_postdata();
    }
    $seen = array(); $out = array();
    foreach ($items as $item) { $key = md5($item['url'] . '|' . $item['title']); if (isset($seen[$key])) { continue; } $seen[$key]=1; $out[]=$item; }
    wp_send_json_success(array('items'=>array_slice($out,0,12)));
}
add_action('wp_ajax_thermodul_search', 'thermodul_ajax_search');
add_action('wp_ajax_nopriv_thermodul_search', 'thermodul_ajax_search');

function thermodul_ajax_search_shortcode() {
    ob_start(); ?>
    <form class="td-ajax-search td-inline-search" role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get">
        <input class="td-site-search-input" type="search" name="s" placeholder="<?php echo esc_attr(thermodul_i18n('Meklēt lapā...', 'Search this site...', 'Поиск по сайту...', 'Ieškoti svetainėje...', 'Otsi lehelt...')); ?>" autocomplete="off">
        <button class="td-btn" type="submit"><?php echo esc_html(thermodul_i18n('Meklēt', 'Search', 'Поиск', 'Ieškoti', 'Otsi')); ?></button>
        <div class="td-site-search-results" aria-live="polite"></div>
    </form>
    <?php return ob_get_clean();
}
add_shortcode('thermodul_ajax_search', 'thermodul_ajax_search_shortcode');

function thermodul_form_config($attributes = array()) {
    $defaults = array('recipient'=>get_option('admin_email'), 'subject'=>'THERMODUL pieteikums', 'success'=>thermodul_i18n('Paldies! Ziņa nosūtīta.', 'Thank you! Message sent.', 'Спасибо! Сообщение отправлено.', 'Ačiū! Žinutė išsiųsta.', 'Aitäh! Sõnum saadetud.'), 'submit'=>thermodul_i18n('Nosūtīt pieteikumu →','Send request →','Отправить заявку →','Siųsti užklausą →','Saada päring →'));
    $cfg = shortcode_atts($defaults, $attributes, 'thermodul_contact_form');
    $cfg['recipient'] = sanitize_email($cfg['recipient'] ?: 'info@thermodul.eu');
    return $cfg;
}
function thermodul_contact_form_shortcode($atts = array()) {
    $cfg = thermodul_form_config($atts); ob_start(); ?>
    <form class="td-contact-form" data-td-ajax-form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="thermodul_contact_submit">
        <?php wp_nonce_field('thermodul_contact_submit', 'thermodul_contact_nonce'); ?>
        <input type="hidden" name="recipient" value="<?php echo esc_attr($cfg['recipient']); ?>">
        <div class="td-form-row"><input name="name" required placeholder="<?php echo esc_attr(thermodul_i18n('Vārds','Name','Имя','Vardas','Nimi')); ?>"><input type="email" name="email" required placeholder="<?php echo esc_attr(thermodul_i18n('E-pasts','Email','Эл. почта','El. paštas','E-post')); ?>"></div>
        <div class="td-form-row"><input name="phone" placeholder="<?php echo esc_attr(thermodul_i18n('Tālrunis','Phone','Телефон','Telefonas','Telefon')); ?>"><span class="td-select-wrap"><select name="interest"><option value=""><?php echo esc_html(thermodul_i18n('Interesējošais risinājums','Solution of interest','Интересующее решение','Dominantis sprendimas','Huvipakkuv lahendus')); ?></option><option>Ūdens modelis</option><option>Elektriskais modelis</option><option>Duālais modelis</option><option>Galerijas/objekta konsultācija</option></select></span></div>
        <textarea name="message" required placeholder="<?php echo esc_attr(thermodul_i18n('Pastāstiet par projektu, telpu platību vai jautājumu...','Tell us about the project, room area or question...','Расскажите о проекте, площади помещения или вопросе...','Papasakokite apie projektą, patalpos plotą ar klausimą...','Rääkige projektist, ruumi suurusest või küsimusest...')); ?>"></textarea>
        <label class="td-consent"><input type="checkbox" name="privacy" required><span><?php echo esc_html(thermodul_i18n('Piekrītu, ka ar mani sazinās par šo pieprasījumu.','I agree to be contacted about this request.','Я согласен, чтобы со мной связались по этому запросу.','Sutinku, kad su manimi susisiektų dėl šios užklausos.','Nõustun, et minuga võetakse selle päringu osas ühendust.')); ?></span></label>
        <?php echo thermodul_hcaptcha_widget(); ?>
        <button class="td-btn" type="submit"><?php echo esc_html($cfg['submit']); ?></button>
        <div class="td-form-status" aria-live="polite"></div>
    </form>
    <?php return ob_get_clean();
}
add_shortcode('thermodul_contact_form', 'thermodul_contact_form_shortcode');
function thermodul_handle_contact_submit($ajax = false) {
    if (!isset($_POST['thermodul_contact_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['thermodul_contact_nonce'])), 'thermodul_contact_submit')) {
        if ($ajax) { wp_send_json_error(array('message'=>thermodul_i18n('Drošības pārbaude neizdevās.', 'Security check failed.', 'Ошибка проверки безопасности.', 'Saugumo patikra nepavyko.', 'Turvakontroll ebaõnnestus.'))); }
        wp_safe_redirect(add_query_arg('thermodul_form','error',wp_get_referer() ?: home_url('/'))); exit;
    }
    $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));
    if (!$name || !$email || !$message || empty($_POST['privacy'])) {
        if ($ajax) { wp_send_json_error(array('message'=>thermodul_i18n('Lūdzu, aizpildiet obligātos laukus.', 'Please fill in required fields.', 'Пожалуйста, заполните обязательные поля.', 'Užpildykite privalomus laukus.', 'Palun täitke kohustuslikud väljad.'))); }
        wp_safe_redirect(add_query_arg('thermodul_form','error',wp_get_referer() ?: home_url('/'))); exit;
    }
    $captcha_check = thermodul_verify_hcaptcha();
    if (is_wp_error($captcha_check)) {
        if ($ajax) { wp_send_json_error(array('message'=>$captcha_check->get_error_message())); }
        wp_safe_redirect(add_query_arg('thermodul_form','captcha',wp_get_referer() ?: home_url('/'))); exit;
    }
    $to = sanitize_email(wp_unslash($_POST['recipient'] ?? '')) ?: 'info@thermodul.eu';
    $subject = 'THERMODUL pieteikums no ' . $name;
    $lines = array('Name: '.$name, 'Email: '.$email);
    foreach (array('phone'=>'Phone','interest'=>'Interest','message'=>'Message') as $key=>$label) { if (!empty($_POST[$key])) { $lines[] = $label . ': ' . ($key === 'message' ? sanitize_textarea_field(wp_unslash($_POST[$key])) : sanitize_text_field(wp_unslash($_POST[$key]))); } }
    $sent = wp_mail($to, $subject, implode("\n", $lines), array('Reply-To: ' . $name . ' <' . $email . '>'));
    if ($ajax) { $sent ? wp_send_json_success(array('message'=>thermodul_i18n('Paldies! Ziņa nosūtīta.', 'Thank you! Message sent.', 'Спасибо! Сообщение отправлено.', 'Ačiū! Žinutė išsiųsta.', 'Aitäh! Sõnum saadetud.'))) : wp_send_json_error(array('message'=>thermodul_i18n('Neizdevās nosūtīt formu.', 'Unable to send the form.', 'Не удалось отправить форму.', 'Nepavyko išsiųsti formos.', 'Vormi saatmine ebaõnnestus.'))); }
    wp_safe_redirect(add_query_arg('thermodul_form',$sent ? 'sent' : 'error',wp_get_referer() ?: home_url('/'))); exit;
}
function thermodul_handle_contact_submit_post() { thermodul_handle_contact_submit(false); }
function thermodul_handle_contact_submit_ajax() { thermodul_handle_contact_submit(true); }
add_action('admin_post_thermodul_contact_submit', 'thermodul_handle_contact_submit_post');
add_action('admin_post_nopriv_thermodul_contact_submit', 'thermodul_handle_contact_submit_post');
add_action('wp_ajax_thermodul_contact_submit', 'thermodul_handle_contact_submit_ajax');
add_action('wp_ajax_nopriv_thermodul_contact_submit', 'thermodul_handle_contact_submit_ajax');

function thermodul_gallery_ajax() {
    check_ajax_referer('thermodul_gallery', 'nonce');
    $offset = max(0, intval($_GET['offset'] ?? 0)); $limit = min(24, max(4, intval($_GET['limit'] ?? 12)));
    $all = thermodul_gallery_flat_items();
    $chunk = array_slice($all, $offset, $limit); $html='';
    foreach ($chunk as $item) { $html .= thermodul_gallery_card($item, thermodul_i18n('Skatīt attēlu','View image','Смотреть изображение','Žiūrėti nuotrauką','Vaata pilti')); }
    wp_send_json_success(array('html'=>$html, 'next'=>$offset + count($chunk), 'done'=>($offset + count($chunk)) >= count($all), 'total'=>count($all)));
}
add_action('wp_ajax_thermodul_gallery', 'thermodul_gallery_ajax');
add_action('wp_ajax_nopriv_thermodul_gallery', 'thermodul_gallery_ajax');

function thermodul_register_dynamic_blocks() {
    if (!function_exists('register_block_type')) { return; }
    register_block_type('thermodul/contact-form', array('api_version'=>3, 'title'=>'THERMODUL Contact Form', 'category'=>'widgets', 'render_callback'=>function($attrs){ return thermodul_contact_form_shortcode($attrs); }, 'attributes'=>array('recipient'=>array('type'=>'string'), 'submit'=>array('type'=>'string')), 'supports'=>array('html'=>false)));
    register_block_type('thermodul/ajax-search', array('api_version'=>3, 'title'=>'THERMODUL Ajax Search', 'category'=>'widgets', 'render_callback'=>function(){ return thermodul_ajax_search_shortcode(); }, 'supports'=>array('html'=>false)));
    register_block_type('thermodul/gallery', array('api_version'=>3, 'title'=>'THERMODUL Gallery', 'category'=>'media', 'render_callback'=>function($attrs){ $limit = isset($attrs['limit']) ? (int)$attrs['limit'] : 0; return thermodul_gallery_shortcode(array('limit'=>$limit)); }, 'attributes'=>array('limit'=>array('type'=>'number')), 'supports'=>array('html'=>false)));
    register_block_type('thermodul/models', array('api_version'=>3, 'title'=>'THERMODUL Models', 'category'=>'widgets', 'render_callback'=>function(){ return thermodul_models_shortcode(); }, 'supports'=>array('html'=>false)));
}
add_action('init', 'thermodul_register_dynamic_blocks', 20);

function thermodul_bbuilder_notice() {
    if (is_admin() && current_user_can('activate_plugins') && !class_exists('WP_BBuilder')) { echo '<div class="notice notice-info"><p><strong>THERMODUL Theme:</strong> WP BBuilder is supported. If it is not active, fallback Gutenberg blocks/shortcodes keep demo pages editable.</p></div>'; }
}
add_action('admin_notices', 'thermodul_bbuilder_notice');


function thermodul_onepage_seo_meta_v27() {
    $lang = thermodul_current_lang();
    $title = thermodul_i18n('THERMODUL siltās grīdlīstes apsildes sistēma', 'THERMODUL warm baseboard heating system', 'THERMODUL система теплого плинтуса', 'THERMODUL šiltų grindjuosčių šildymo sistema', 'THERMODUL sooja põrandaliistu küttesüsteem');
    $desc = thermodul_i18n('THERMODUL siltās grīdlīstes: komforts, dizains un efektīva perimetra apkure mājām, birojiem un sabiedriskām telpām. Modeļi, galerija, sertifikācija un kontakti vienā lapā.', 'THERMODUL warm baseboards: comfort, design and efficient perimeter heating for homes, offices and public spaces. Models, gallery, certification and contacts on one page.', 'THERMODUL теплый плинтус: комфорт, дизайн и эффективное периметральное отопление для домов, офисов и общественных помещений.', 'THERMODUL šiltos grindjuostės: komfortas, dizainas ir efektyvus perimetrinis šildymas namams, biurams ir viešosioms erdvėms.', 'THERMODUL soojad põrandaliistud: mugav, disainitud ja tõhus perimeetriküte kodudele, kontoritele ja avalikele ruumidele.');
    $url = thermodul_language_home_url($lang);
    $img = thermodul_remote_img('dzivojama-istaba/dz2.jpg');
    echo "
".'<meta name="description" content="'.esc_attr($desc).'">' . "
";
    echo '<link rel="canonical" href="'.esc_url($url).'">' . "
";
    echo '<meta name="robots" content="index,follow,max-image-preview:large">' . "
";
    echo '<meta property="og:type" content="website">' . "
";
    echo '<meta property="og:locale" content="'.esc_attr(str_replace('-', '_', thermodul_supported_languages()[$lang]['hreflang'] ?? 'lv_LV')).'">' . "
";
    echo '<meta property="og:title" content="'.esc_attr($title).'">' . "
";
    echo '<meta property="og:description" content="'.esc_attr($desc).'">' . "
";
    echo '<meta property="og:url" content="'.esc_url($url).'">' . "
";
    echo '<meta property="og:image" content="'.esc_url($img).'">' . "
";
    echo '<meta name="twitter:card" content="summary_large_image">' . "
";
    echo '<script type="application/ld+json">'.wp_json_encode(array(
        '@context'=>'https://schema.org',
        '@type'=>'LocalBusiness',
        'name'=>'THERMODUL',
        'url'=>$url,
        'image'=>$img,
        'email'=>'info@thermodul.eu',
        'telephone'=>'+37129256299',
        'address'=>array('@type'=>'PostalAddress','streetAddress'=>'Dārza iela 17','addressLocality'=>'Ikšķile','postalCode'=>'LV-5052','addressCountry'=>'LV'),
        'makesOffer'=>array('@type'=>'Offer','itemOffered'=>array('@type'=>'Product','name'=>'THERMODUL warm baseboard heating system'))
    ), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'</script>' . "
";
}
// v3.8 replaces the old blanket one-page meta output with context-aware SEO below.
// add_action('wp_head', 'thermodul_onepage_seo_meta_v27', 2);


/* THERMODUL v3.8 migration-safe SEO, analytics and legacy URL support */
function thermodul_has_external_seo_plugin() {
    return defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION');
}
function thermodul_seo_current_slug() {
    if (is_front_page() || get_query_var('thermodul_onepage')) { return 'home'; }
    if (is_page()) { $obj = get_queried_object(); return !empty($obj->post_name) ? $obj->post_name : ''; }
    return '';
}
function thermodul_seo_data() {
    $slug = thermodul_seo_current_slug();
    $home = array(
        'title' => thermodul_i18n(
            'Siltās grīdlīstes THERMODUL | Efektīva apkures sistēma',
            'THERMODUL Warm Baseboard Heating | Efficient Heating System',
            'Тёплый плинтус THERMODUL | Эффективная система отопления',
            'THERMODUL šiltos grindjuostės | Efektyvi šildymo sistema',
            'THERMODUL soe põrandaliist | Tõhus küttesüsteem'
        ),
        'description' => thermodul_i18n(
            'THERMODUL siltās grīdlīstes vienmērīgai un energoefektīvai telpu apkurei. Ūdens, elektriskie un kombinētie modeļi mājām un komerctelpām.',
            'THERMODUL warm baseboard heating for even, efficient room comfort. Water, electric and combined models for homes and commercial spaces.',
            'Тёплый плинтус THERMODUL для равномерного и эффективного отопления. Водяные, электрические и комбинированные модели для дома и бизнеса.',
            'THERMODUL šiltos grindjuostės tolygiam ir efektyviam patalpų šildymui. Vandens, elektriniai ir kombinuoti modeliai namams ir verslui.',
            'THERMODUL soe põrandaliist ühtlaseks ja tõhusaks kütmiseks. Vee-, elektri- ja kombineeritud mudelid kodudesse ja äripindadele.'
        ),
        'type' => 'website',
    );
    $pages = array(
        'par-uznemumu' => array(
            'title' => thermodul_i18n('Par THERMODUL un AQUADOCK BALTIJA','About THERMODUL and AQUADOCK BALTIJA','О THERMODUL и AQUADOCK BALTIJA','Apie THERMODUL ir AQUADOCK BALTIJA','THERMODUL ja AQUADOCK BALTIJA'),
            'description' => thermodul_i18n('Uzziniet par THERMODUL grīdlīstes apkures risinājumu, AQUADOCK BALTIJA pieredzi, pielietojumu un sistēmas priekšrocībām.','Learn about the THERMODUL baseboard heating solution, AQUADOCK BALTIJA, applications and system advantages.','Узнайте о системе плинтусного отопления THERMODUL, AQUADOCK BALTIJA, применении и преимуществах решения.','Sužinokite apie THERMODUL grindjuosčių šildymo sprendimą, AQUADOCK BALTIJA, pritaikymą ir sistemos privalumus.','Tutvuge THERMODUL põrandaliistukütte, AQUADOCK BALTIJA, kasutusalade ja süsteemi eelistega.'),
        ),
        'sertifikacija' => array(
            'title' => thermodul_i18n('THERMODUL sertifikācija un standarti','THERMODUL Certification and Standards','Сертификация и стандарты THERMODUL','THERMODUL sertifikavimas ir standartai','THERMODUL sertifikaadid ja standardid'),
            'description' => thermodul_i18n('THERMODUL kvalitātes, drošības un siltuma atdeves informācija, Eiropas standarti un sertifikācijas materiāli.','THERMODUL quality, safety and heat-output information, European standards and certification materials.','Информация о качестве, безопасности, теплоотдаче, европейских стандартах и сертификационных материалах THERMODUL.','THERMODUL kokybės, saugos, šilumos atidavimo, Europos standartų ir sertifikavimo informacija.','THERMODUL kvaliteedi, ohutuse, soojusvõimsuse, Euroopa standardite ja sertifitseerimise info.'),
        ),
        'galerija' => array(
            'title' => thermodul_i18n('THERMODUL galerija | Realizētie objekti','THERMODUL Gallery | Installed Projects','Галерея THERMODUL | Реализованные объекты','THERMODUL galerija | Įgyvendinti objektai','THERMODUL galerii | Valminud objektid'),
            'description' => thermodul_i18n('THERMODUL uzstādījumu piemēri mājās, birojos, skolās, restorānos, sabiedriskās un citās telpās.','THERMODUL installation examples in homes, offices, schools, restaurants, public buildings and other spaces.','Примеры установки THERMODUL в домах, офисах, школах, ресторанах, общественных и других помещениях.','THERMODUL montavimo pavyzdžiai namuose, biuruose, mokyklose, restoranuose, viešose ir kitose patalpose.','THERMODUL paigaldusnäited kodudes, kontorites, koolides, restoranides, avalikes hoonetes ja mujal.'),
            'type' => 'website',
        ),
        'kontakti' => array(
            'title' => thermodul_i18n('THERMODUL kontakti un informācijas pieprasījums','THERMODUL Contacts and Information Request','Контакты THERMODUL и запрос информации','THERMODUL kontaktai ir informacijos užklausa','THERMODUL kontaktid ja infopäring'),
            'description' => thermodul_i18n('Sazinieties ar THERMODUL / AQUADOCK BALTIJA par grīdlīstes apkures projektu, modeļa izvēli, jaudu un uzstādīšanas risinājumu.','Contact THERMODUL / AQUADOCK BALTIJA about a baseboard heating project, model choice, output and installation.','Свяжитесь с THERMODUL / AQUADOCK BALTIJA по проекту плинтусного отопления, выбору модели, мощности и монтажу.','Susisiekite su THERMODUL / AQUADOCK BALTIJA dėl grindjuosčių šildymo projekto, modelio, galios ir montavimo.','Võtke THERMODUL / AQUADOCK BALTIJA-ga ühendust põrandaliistukütte projekti, mudeli, võimsuse ja paigalduse osas.'),
            'type' => 'website',
        ),
        'udens-modelis' => array(
            'title' => thermodul_i18n('THERMODUL ūdens modelis | Siltās grīdlīstes','THERMODUL Water Model | Warm Baseboard Heating','Водяная модель THERMODUL | Тёплый плинтус','THERMODUL vandens modelis | Šiltos grindjuostės','THERMODUL veemudel | Soe põrandaliist'),
            'description' => thermodul_i18n('THERMODUL ūdens modelis katliem, siltumsūkņiem un citām ūdens apkures sistēmām. Piemērots jaunbūvēm, renovācijām un radiatoru nomaiņai.','THERMODUL water model for boilers, heat pumps and other hydronic systems. Suitable for new builds, renovations and radiator replacement.','Водяная модель THERMODUL для котлов, тепловых насосов и других водяных систем. Для новостроек, реконструкции и замены радиаторов.','THERMODUL vandens modelis katilams, šilumos siurbliams ir kitoms vandens sistemoms. Naujai statybai, renovacijai ir radiatorių keitimui.','THERMODUL veemudel kateldele, soojuspumpadele ja muudele vesiküttesüsteemidele. Uusehituseks, renoveerimiseks ja radiaatorite asendamiseks.'),
            'type' => 'product',
        ),
        'elektriskais-modelis' => array(
            'title' => thermodul_i18n('THERMODUL elektriskais modelis | Siltās grīdlīstes','THERMODUL Electric Model | Warm Baseboard Heating','Электрическая модель THERMODUL | Тёплый плинтус','THERMODUL elektrinis modelis | Šiltos grindjuostės','THERMODUL elektrimudel | Soe põrandaliist'),
            'description' => thermodul_i18n('THERMODUL elektriskais modelis telpām bez ūdens apkures pieslēguma. Ātra uzstādīšana, termostata vadība un diskrēts grīdlīstes dizains.','THERMODUL electric model for spaces without hydronic heating. Fast installation, thermostat control and discreet baseboard design.','Электрическая модель THERMODUL для помещений без водяного отопления. Быстрый монтаж, управление термостатом и компактный дизайн.','THERMODUL elektrinis modelis patalpoms be vandens šildymo. Greitas montavimas, termostato valdymas ir diskretiškas dizainas.','THERMODUL elektrimudel ruumidesse ilma vesikütteta. Kiire paigaldus, termostaadijuhtimine ja diskreetne disain.'),
            'type' => 'product',
        ),
        'dualais-modelis' => array(
            'title' => thermodul_i18n('THERMODUL duālais modelis | Ūdens + elektriskā apkure','THERMODUL Dual Model | Water + Electric Heating','Дуальная модель THERMODUL | Вода + электричество','THERMODUL dualinis modelis | Vanduo + elektra','THERMODUL duaalmudel | Vesi + elekter'),
            'description' => thermodul_i18n('THERMODUL duālais modelis apvieno ūdens un elektrisko apkuri vienā grīdlīstes risinājumā starpsezonai un papildu siltuma rezervei.','THERMODUL dual model combines water and electric heating in one baseboard solution for shoulder seasons and backup heat.','Дуальная модель THERMODUL объединяет водяное и электрическое отопление для межсезонья и резервного обогрева.','THERMODUL dualinis modelis sujungia vandens ir elektrinį šildymą viename sprendime tarpsezoniui ir atsarginiam šildymui.','THERMODUL duaalmudel ühendab vee- ja elektrikütte ühes lahenduses üleminekuperioodiks ja varukütteks.'),
            'type' => 'product',
        ),
        'abpusejais-modelis' => array(
            'title' => thermodul_i18n('THERMODUL abpusējais modelis | Logiem līdz grīdai','THERMODUL Double-Sided Model | Floor-to-Ceiling Windows','Двусторонняя модель THERMODUL | Окна до пола','THERMODUL dvipusis modelis | Langams iki grindų','THERMODUL kahepoolne mudel | Põrandani aknad'),
            'description' => thermodul_i18n('Abpusējais THERMODUL modelis logiem līdz grīdai un vietām, kur sistēmu nevar stiprināt pie sienas. Stiprināms pie grīdas, jauda x2.','THERMODUL double-sided model for floor-to-ceiling windows and areas where wall mounting is not possible. Floor mounted with double output.','Двусторонняя модель THERMODUL для окон до пола и мест без настенного монтажа. Крепление к полу и двойная мощность.','Dvipusis THERMODUL modelis langams iki grindų ir vietoms, kur negalimas tvirtinimas prie sienos. Tvirtinamas prie grindų, dviguba galia.','THERMODUL kahepoolne mudel põrandani akendele ja kohtadesse, kus seinakinnitus pole võimalik. Põrandakinnitus ja topeltvõimsus.'),
            'type' => 'product',
        ),
        'dubultais-horizontalais-modelis' => array(
            'title' => thermodul_i18n('THERMODUL dubultais horizontālais modelis','THERMODUL Double Horizontal Model','Двойная горизонтальная модель THERMODUL','THERMODUL dvigubas horizontalus modelis','THERMODUL topelt horisontaalne mudel'),
            'description' => thermodul_i18n('Dubultais horizontālais THERMODUL risinājums lielām telpām, skolām, restorāniem, sporta zālēm, baznīcām un citiem objektiem.','THERMODUL double horizontal solution for large rooms, schools, restaurants, sports halls, churches and other projects.','Двойное горизонтальное решение THERMODUL для больших помещений, школ, ресторанов, спортзалов, церквей и других объектов.','THERMODUL dvigubas horizontalus sprendimas didelėms patalpoms, mokykloms, restoranams, sporto salėms ir kitiems objektams.','THERMODUL topelt horisontaalne lahendus suurtesse ruumidesse, koolidesse, restoranidesse, spordisaalidesse ja mujale.'),
            'type' => 'product',
        ),
        'dubultais-vertikalais-modelis' => array(
            'title' => thermodul_i18n('THERMODUL dubultais vertikālais modelis','THERMODUL Double Vertical Model','Двойная вертикальная модель THERMODUL','THERMODUL dvigubas vertikalus modelis','THERMODUL topelt vertikaalne mudel'),
            'description' => thermodul_i18n('Dubultais vertikālais THERMODUL modelis lielākai siltuma jaudai, īpaši ziemas dārziem, stiklotām lodžijām un specifiskām zonām.','THERMODUL double vertical model for higher heat output, especially winter gardens, glazed loggias and demanding zones.','Двойная вертикальная модель THERMODUL для повышенной мощности, особенно для зимних садов, остеклённых лоджий и сложных зон.','THERMODUL dvigubas vertikalus modelis didesnei šilumos galiai, ypač žiemos sodams, įstiklintoms lodžijoms ir sudėtingoms zonoms.','THERMODUL topelt vertikaalmudel suurema soojusvõimsuse jaoks, eriti talveaedadesse, klaasitud lodžadele ja nõudlikesse tsoonidesse.'),
            'type' => 'product',
        ),
        'lapas-karte' => array(
            'title' => thermodul_i18n('THERMODUL lapas karte','THERMODUL Sitemap','Карта сайта THERMODUL','THERMODUL svetainės žemėlapis','THERMODUL saidikaart'),
            'description' => thermodul_i18n('THERMODUL vietnes sadaļas, modeļi, galerija, sertifikācija un kontaktinformācija.','THERMODUL website sections, models, gallery, certification and contact information.','Разделы сайта THERMODUL, модели, галерея, сертификация и контакты.','THERMODUL svetainės skyriai, modeliai, galerija, sertifikavimas ir kontaktai.','THERMODUL veebisaidi jaotised, mudelid, galerii, sertifikaadid ja kontaktid.'),
        ),
    );
    if ($slug === 'home') { $data = $home; }
    elseif (isset($pages[$slug])) { $data = $pages[$slug]; }
    elseif (is_singular()) {
        $title = wp_strip_all_tags(get_the_title(get_queried_object_id()));
        $excerpt = get_the_excerpt(get_queried_object_id());
        if (!$excerpt) { $excerpt = wp_strip_all_tags(get_post_field('post_content', get_queried_object_id())); }
        $data = array('title' => $title . ' | THERMODUL', 'description' => wp_trim_words($excerpt, 28, ''), 'type' => 'article');
    } else { return array(); }
    if (empty($data['type'])) { $data['type'] = 'website'; }
    if ($slug === 'home') { $data['url'] = thermodul_language_home_url(thermodul_current_lang()); }
    elseif (is_singular()) { $data['url'] = get_permalink(get_queried_object_id()); }
    else { $data['url'] = home_url('/'); }
    $data['image'] = thermodul_remote_img('dzivojama-istaba/dz2.jpg');
    $model = $slug ? thermodul_model_by_slug($slug) : null;
    if ($model && !empty($model['img'])) { $data['image'] = $model['img']; }
    return $data;
}
function thermodul_filter_document_title($title) {
    if (thermodul_has_external_seo_plugin()) { return $title; }
    $seo = thermodul_seo_data();
    return !empty($seo['title']) ? $seo['title'] : $title;
}
add_filter('pre_get_document_title', 'thermodul_filter_document_title', 20);

function thermodul_wp_robots($robots) {
    if (is_search() || is_404()) { $robots['noindex'] = true; $robots['follow'] = true; }
    else { $robots['max-image-preview'] = 'large'; }
    return $robots;
}
add_filter('wp_robots', 'thermodul_wp_robots');

function thermodul_fallback_seo_meta() {
    if (thermodul_has_external_seo_plugin()) { return; }
    $seo = thermodul_seo_data();
    if (empty($seo)) { return; }
    echo '<meta name="description" content="'.esc_attr($seo['description']).'">' . "\n";
    echo '<link rel="canonical" href="'.esc_url($seo['url']).'">' . "\n";
    echo '<meta property="og:type" content="'.esc_attr($seo['type'] === 'article' ? 'article' : 'website').'">' . "\n";
    echo '<meta property="og:title" content="'.esc_attr($seo['title']).'">' . "\n";
    echo '<meta property="og:description" content="'.esc_attr($seo['description']).'">' . "\n";
    echo '<meta property="og:url" content="'.esc_url($seo['url']).'">' . "\n";
    echo '<meta property="og:image" content="'.esc_url($seo['image']).'">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="'.esc_attr($seo['title']).'">' . "\n";
    echo '<meta name="twitter:description" content="'.esc_attr($seo['description']).'">' . "\n";
    echo '<meta name="twitter:image" content="'.esc_url($seo['image']).'">' . "\n";
}
add_action('wp_head', 'thermodul_fallback_seo_meta', 2);

function thermodul_schema_graph() {
    if (thermodul_has_external_seo_plugin()) { return; }
    $seo = thermodul_seo_data(); if (empty($seo)) { return; }
    $org_id = home_url('/#organization');
    $graph = array(
        array('@type'=>'Organization','@id'=>$org_id,'name'=>'THERMODUL','url'=>home_url('/'),'email'=>'info@thermodul.eu','telephone'=>'+37129256299','address'=>array('@type'=>'PostalAddress','streetAddress'=>'Dārza iela 17','addressLocality'=>'Ikšķile','postalCode'=>'LV-5052','addressCountry'=>'LV')),
        array('@type'=>'WebPage','@id'=>$seo['url'].'#webpage','url'=>$seo['url'],'name'=>$seo['title'],'description'=>$seo['description'],'isPartOf'=>array('@id'=>home_url('/#website')),'about'=>array('@id'=>$org_id),'primaryImageOfPage'=>array('@type'=>'ImageObject','url'=>$seo['image'])),
    );
    if (is_front_page() || get_query_var('thermodul_onepage')) {
        $graph[] = array('@type'=>'WebSite','@id'=>home_url('/#website'),'url'=>home_url('/'),'name'=>'THERMODUL','publisher'=>array('@id'=>$org_id),'inLanguage'=>get_bloginfo('language'));
    }
    $slug = thermodul_seo_current_slug(); $model = $slug ? thermodul_model_by_slug($slug) : null;
    if ($model) {
        $graph[] = array('@type'=>'Product','name'=>$model['title'],'description'=>$model['text'],'image'=>$model['img'],'url'=>$seo['url'],'brand'=>array('@type'=>'Brand','name'=>'THERMODUL'),'manufacturer'=>array('@id'=>$org_id));
    }
    echo '<script type="application/ld+json">'.wp_json_encode(array('@context'=>'https://schema.org','@graph'=>$graph), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'</script>' . "\n";
}
add_action('wp_head', 'thermodul_schema_graph', 6);

// Fill Yoast output with the migration-safe metadata when Yoast is active.
function thermodul_yoast_title($value) { $seo=thermodul_seo_data(); return !empty($seo['title']) ? $seo['title'] : $value; }
function thermodul_yoast_desc($value) { $seo=thermodul_seo_data(); return !empty($seo['description']) ? $seo['description'] : $value; }
function thermodul_yoast_canonical($value) { $seo=thermodul_seo_data(); return !empty($seo['url']) ? $seo['url'] : $value; }
function thermodul_yoast_image($value) { $seo=thermodul_seo_data(); return !empty($seo['image']) ? $seo['image'] : $value; }
add_filter('wpseo_title', 'thermodul_yoast_title', 20);
add_filter('wpseo_metadesc', 'thermodul_yoast_desc', 20);
add_filter('wpseo_canonical', 'thermodul_yoast_canonical', 20);
add_filter('wpseo_opengraph_title', 'thermodul_yoast_title', 20);
add_filter('wpseo_opengraph_desc', 'thermodul_yoast_desc', 20);
add_filter('wpseo_opengraph_url', 'thermodul_yoast_canonical', 20);
add_filter('wpseo_opengraph_image', 'thermodul_yoast_image', 20);
add_filter('wpseo_twitter_title', 'thermodul_yoast_title', 20);
add_filter('wpseo_twitter_description', 'thermodul_yoast_desc', 20);
add_filter('wpseo_twitter_image', 'thermodul_yoast_image', 20);

function thermodul_register_migration_settings() {
    register_setting('thermodul_seo_analytics', 'thermodul_ga4_id', array('sanitize_callback'=>'sanitize_text_field','default'=>''));
    register_setting('thermodul_seo_analytics', 'thermodul_gtm_id', array('sanitize_callback'=>'sanitize_text_field','default'=>''));
    register_setting('thermodul_seo_analytics', 'thermodul_google_site_verification', array('sanitize_callback'=>'sanitize_text_field','default'=>''));
    register_setting('thermodul_seo_analytics', 'thermodul_bing_site_verification', array('sanitize_callback'=>'sanitize_text_field','default'=>''));
    register_setting('thermodul_seo_analytics', 'thermodul_redirect_legacy_pages', array('sanitize_callback'=>function($v){return $v ? 1 : 0;},'default'=>0));
    register_setting('thermodul_performance', 'thermodul_performance_mode', array('sanitize_callback'=>function($v){return $v ? 1 : 0;},'default'=>1));
    register_setting('thermodul_performance', 'thermodul_cache_ttl', array('sanitize_callback'=>function($v){return max(300,min(86400,(int)$v));},'default'=>3600));
}
add_action('admin_init', 'thermodul_register_migration_settings');
function thermodul_seo_analytics_menu() {
    add_theme_page('THERMODUL SEO & Analytics', 'THERMODUL SEO & Analytics', 'manage_options', 'thermodul-seo-analytics', 'thermodul_seo_analytics_page');
}
add_action('admin_menu', 'thermodul_seo_analytics_menu');
function thermodul_seo_analytics_page() {
    if (!current_user_can('manage_options')) { return; }
    ?>
    <div class="wrap"><h1>THERMODUL SEO & Analytics</h1>
    <p>Use a current GA4 Measurement ID (G-...) or a Google Tag Manager container (GTM-...). The old Universal Analytics UA property is intentionally not reused.</p>
    <form method="post" action="options.php"><?php settings_fields('thermodul_seo_analytics'); ?>
    <table class="form-table" role="presentation">
      <tr><th scope="row"><label for="thermodul_ga4_id">GA4 Measurement ID</label></th><td><input class="regular-text" id="thermodul_ga4_id" name="thermodul_ga4_id" value="<?php echo esc_attr(get_option('thermodul_ga4_id','')); ?>" placeholder="G-XXXXXXXXXX"><p class="description">Used only when GTM is empty.</p></td></tr>
      <tr><th scope="row"><label for="thermodul_gtm_id">Google Tag Manager ID</label></th><td><input class="regular-text" id="thermodul_gtm_id" name="thermodul_gtm_id" value="<?php echo esc_attr(get_option('thermodul_gtm_id','')); ?>" placeholder="GTM-XXXXXXX"></td></tr>
      <tr><th scope="row"><label for="thermodul_google_site_verification">Google Search Console verification</label></th><td><input class="regular-text" id="thermodul_google_site_verification" name="thermodul_google_site_verification" value="<?php echo esc_attr(get_option('thermodul_google_site_verification','')); ?>"></td></tr>
      <tr><th scope="row"><label for="thermodul_bing_site_verification">Bing Webmaster verification</label></th><td><input class="regular-text" id="thermodul_bing_site_verification" name="thermodul_bing_site_verification" value="<?php echo esc_attr(get_option('thermodul_bing_site_verification','')); ?>"></td></tr>
      <tr><th scope="row">Legacy page redirects</th><td><label><input type="checkbox" name="thermodul_redirect_legacy_pages" value="1" <?php checked(1, (int)get_option('thermodul_redirect_legacy_pages',0)); ?>> Redirect old page URLs to homepage sections.</label><p class="description"><strong>Recommended during migration: leave unchecked</strong> so existing indexed URLs keep their own pages and SEO value.</p></td></tr>
    </table><?php submit_button(); ?></form></div>
    <?php
}
function thermodul_performance_menu() {
    add_theme_page('THERMODUL Performance', 'THERMODUL Performance', 'manage_options', 'thermodul-performance', 'thermodul_performance_page');
}
add_action('admin_menu', 'thermodul_performance_menu');
function thermodul_performance_page() {
    if (!current_user_can('manage_options')) { return; }
    if (isset($_POST['thermodul_clear_cache']) && check_admin_referer('thermodul_clear_cache')) {
        thermodul_clear_theme_cache();
        echo '<div class="notice notice-success"><p>THERMODUL cache cleared.</p></div>';
    }
    $persistent = wp_using_ext_object_cache();
    ?>
    <div class="wrap"><h1>THERMODUL Performance</h1>
      <p><strong>Persistent object cache:</strong> <?php echo $persistent ? '<span style="color:#138a43">Active</span>' : '<span style="color:#b54708">Not detected - transient fallback is used</span>'; ?></p>
      <p>The homepage uses server-rendered HTML, local responsive grid CSS, lazy hCaptcha, AVIF images and cached theme data. Full-page caching is intentionally left to the server/cache plugin because the contact form contains nonces.</p>
      <form method="post" action="options.php"><?php settings_fields('thermodul_performance'); ?>
        <table class="form-table" role="presentation">
          <tr><th scope="row">Performance mode</th><td><label><input type="checkbox" name="thermodul_performance_mode" value="1" <?php checked(1,(int)get_option('thermodul_performance_mode',1)); ?>> Optimize the static THERMODUL homepage and use theme data caching.</label></td></tr>
          <tr><th scope="row"><label for="thermodul_cache_ttl">Theme data cache TTL</label></th><td><input id="thermodul_cache_ttl" name="thermodul_cache_ttl" type="number" min="300" max="86400" step="60" value="<?php echo esc_attr(thermodul_cache_ttl()); ?>"> seconds</td></tr>
        </table><?php submit_button('Save performance settings'); ?>
      </form>
      <form method="post" style="margin-top:18px"><?php wp_nonce_field('thermodul_clear_cache'); ?><input type="hidden" name="thermodul_clear_cache" value="1"><?php submit_button('Clear THERMODUL cache','secondary'); ?></form>
    </div><?php
}
function thermodul_frontend_performance_cleanup() {
    if (is_admin() || !thermodul_performance_enabled() || !(is_front_page() || get_query_var('thermodul_onepage'))) { return; }
    foreach (array('thermodul-bootstrap-grid','wp-block-library','wp-block-library-theme','classic-theme-styles','global-styles') as $handle) { wp_dequeue_style($handle); }
    $wpbb_styles = array('wpbb-bootstrap-full','wpbb-bootstrap-core','wpbb-bootstrap-grid','wpbb-bootstrap-utilities','wpbb-bootstrap-reboot');
    foreach (array('buttons','forms','nav','navbar','transitions','accordion','dropdown','card','button-group','modal','offcanvas','breadcrumb','pagination','badge','alert','progress','list-group','close','toasts','tooltip','popover','carousel','spinners','placeholders','images','tables','helpers') as $component) { $wpbb_styles[] = 'wpbb-bootstrap-css-' . $component; }
    foreach ($wpbb_styles as $handle) { wp_dequeue_style($handle); }
    foreach (array('wpbb-bootstrap-bundle','wpbb-bootstrap-js-collapse','wpbb-bootstrap-js-dropdown','wpbb-bootstrap-js-tab','wpbb-bootstrap-js-modal','wpbb-bootstrap-js-offcanvas','wpbb-bootstrap-js-tooltip','wpbb-bootstrap-js-popover','wpbb-bootstrap-js-toast','wpbb-bootstrap-js-carousel','wpbb-bootstrap-js-alert','wpbb-bootstrap-js-button','wpbb-bootstrap-js-scrollspy') as $handle) { wp_dequeue_script($handle); }
}
add_action('wp_enqueue_scripts', 'thermodul_frontend_performance_cleanup', 1000);
function thermodul_disable_frontend_emoji_assets() {
    if (!thermodul_performance_enabled()) { return; }
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('wp_enqueue_scripts', 'wp_enqueue_emoji_styles');
}
add_action('init', 'thermodul_disable_frontend_emoji_assets', 20);
function thermodul_preload_lcp_image() {
    if (!(is_front_page() || get_query_var('thermodul_onepage'))) { return; }
    $jpg = thermodul_asset_img('demo/hero-interior.jpg');
    $avif = thermodul_avif_url_for($jpg);
    $src = $avif ?: $jpg;
    echo '<link rel="preload" as="image" href="'.esc_url($src).'"'.($avif ? ' type="image/avif"' : '').' fetchpriority="high">' . "\n";
}
add_action('wp_head', 'thermodul_preload_lcp_image', 2);

function thermodul_tracking_head() {
    $gtm = trim((string)get_option('thermodul_gtm_id',''));
    $ga4 = trim((string)get_option('thermodul_ga4_id',''));
    $gsv = trim((string)get_option('thermodul_google_site_verification',''));
    $bsv = trim((string)get_option('thermodul_bing_site_verification',''));
    if ($gsv !== '') { echo '<meta name="google-site-verification" content="'.esc_attr($gsv).'">' . "\n"; }
    if ($bsv !== '') { echo '<meta name="msvalidate.01" content="'.esc_attr($bsv).'">' . "\n"; }
    if (preg_match('/^GTM-[A-Z0-9]+$/i', $gtm)) {
        echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','".esc_js($gtm)."');</script>\n";
    } elseif (preg_match('/^G-[A-Z0-9]+$/i', $ga4)) {
        echo '<script async src="https://www.googletagmanager.com/gtag/js?id='.esc_attr($ga4).'"></script>' . "\n";
        echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','".esc_js($ga4)."');</script>\n";
    }
}
add_action('wp_head', 'thermodul_tracking_head', 1);
function thermodul_tracking_body() {
    $gtm = trim((string)get_option('thermodul_gtm_id',''));
    if (preg_match('/^GTM-[A-Z0-9]+$/i', $gtm)) { echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id='.esc_attr($gtm).'" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>'; }
}
add_action('wp_body_open', 'thermodul_tracking_body', 1);
