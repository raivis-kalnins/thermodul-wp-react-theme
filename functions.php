<?php
if (!defined('ABSPATH')) { exit; }

define('THERMODUL_THEME_VERSION', '3.7.0');

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

function thermodul_scripts() {
    if (!wp_style_is('wp-bbuilder-bootstrap', 'enqueued') && !wp_style_is('bootstrap', 'enqueued')) {
        wp_enqueue_style('thermodul-bootstrap-grid', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap-grid.min.css', array(), '5.3.3');
    }
    $theme_css = file_exists(get_template_directory() . '/assets/css/theme.min.css') ? get_template_directory_uri() . '/assets/css/theme.min.css' : get_stylesheet_uri();
    $theme_css_ver = file_exists(get_template_directory() . '/assets/css/theme.min.css') ? thermodul_asset_ver('assets/css/theme.min.css') : thermodul_asset_ver('style.css');
    wp_enqueue_style('thermodul-style', $theme_css, array(), $theme_css_ver);
    wp_enqueue_style('thermodul-wpbb', get_template_directory_uri() . '/assets/css/wpbb-compat.css', array('thermodul-style'), thermodul_asset_ver('assets/css/wpbb-compat.css'));
    $theme_js = file_exists(get_template_directory() . '/assets/js/theme.min.js') ? 'assets/js/theme.min.js' : 'assets/js/theme.js';
    wp_enqueue_script('thermodul-theme', get_template_directory_uri() . '/' . $theme_js, array(), thermodul_asset_ver($theme_js), true);
    wp_localize_script('thermodul-theme', 'thermodulTheme', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'searchNonce' => wp_create_nonce('thermodul_search'),
        'formNonce' => wp_create_nonce('thermodul_contact'),
        'galleryNonce' => wp_create_nonce('thermodul_gallery'),
        'lang' => function_exists('thermodul_current_lang') ? thermodul_current_lang() : 'lv',
        'labels' => array(
            'searching' => function_exists('thermodul_i18n') ? thermodul_i18n('Meklē...', 'Searching...', 'Идет поиск...', 'Ieškoma...', 'Otsin...') : 'Meklē...',
            'noResults' => function_exists('thermodul_i18n') ? thermodul_i18n('Nekas netika atrasts.', 'No results found.', 'Ничего не найдено.', 'Nieko nerasta.', 'Tulemusi ei leitud.') : 'Nekas netika atrasts.',
            'minChars' => function_exists('thermodul_i18n') ? thermodul_i18n('Ievadiet vismaz 2 simbolus.', 'Type at least 2 characters.', 'Введите минимум 2 символа.', 'Įveskite bent 2 simbolius.', 'Sisestage vähemalt 2 tähemärki.') : 'Ievadiet vismaz 2 simbolus.',
            'loading' => function_exists('thermodul_i18n') ? thermodul_i18n('Ielādē...', 'Loading...', 'Загрузка...', 'Įkeliama...', 'Laadin...') : 'Ielādē...',
            'formError' => function_exists('thermodul_i18n') ? thermodul_i18n('Neizdevās nosūtīt formu. Lūdzu, mēģiniet vēlreiz.', 'Unable to send the form. Please try again.', 'Не удалось отправить форму. Попробуйте еще раз.', 'Nepavyko išsiųsti formos. Bandykite dar kartą.', 'Vormi saatmine ebaõnnestus. Palun proovige uuesti.') : 'Neizdevās nosūtīt formu.',
            'formRequired' => function_exists('thermodul_i18n') ? thermodul_i18n('Lūdzu, aizpildiet obligātos laukus.', 'Please fill in required fields.', 'Пожалуйста, заполните обязательные поля.', 'Užpildykite privalomus laukus.', 'Palun täitke kohustuslikud väljad.') : 'Lūdzu, aizpildiet obligātos laukus.',
        ),
        'localIndex' => function_exists('thermodul_local_search_index') ? thermodul_local_search_index() : array(),
    ));
    if (function_exists('thermodul_hcaptcha_site_key') && thermodul_hcaptcha_site_key()) {
        $hc_lang = function_exists('thermodul_current_lang') ? thermodul_current_lang() : 'lv';
        if (wp_script_is('hcaptcha-api', 'registered') || wp_script_is('hcaptcha-api', 'enqueued')) {
            wp_dequeue_script('hcaptcha-api');
            wp_deregister_script('hcaptcha-api');
        }
        wp_enqueue_script('hcaptcha-api', 'https://js.hcaptcha.com/1/api.js?hl=' . rawurlencode($hc_lang), array(), null, true);
    }
    if (is_front_page()) {
        wp_enqueue_script('thermodul-react-home', get_template_directory_uri() . '/assets/js/headless-home.js', array('wp-element'), thermodul_asset_ver('assets/js/headless-home.js'), true);
        wp_localize_script('thermodul-react-home', 'thermodulHomeData', thermodul_home_data());
    }
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
        'Dzīvojamā istaba' => $make('dzivojama-istaba','dz',10),
        'Kafejnīcas un restorāni' => $make('kafejnicas-restorani','kr',8),
        'Muzeji' => $make('muzejs','mu',8),
        'Ofisi un veselības iestādes' => $make('ofisi-veselibas-iestades','of',9),
        'Palīgtelpas' => $make('paligtelpas','p',9),
        'Reliģiskās telpas' => $make('religiskas-iestades','rel',6),
        'Skolas & bērnu dārzi' => $make('skolas-bernudarzi','sk',8),
        'Veikali' => $make('veikali','v',6),
    );
}
function thermodul_upload_img($path) { return content_url('uploads/thermodul-demo/' . ltrim($path, '/')); }
function thermodul_picture($src, $alt = '', $class = '', $loading = 'lazy', $extra = '') {
    $avif = function_exists('thermodul_avif_url_for') ? thermodul_avif_url_for($src) : '';
    $cls = $class ? ' class="' . esc_attr($class) . '"' : '';
    $html = '<picture>';
    if ($avif && $avif !== $src) { $html .= '<source srcset="' . esc_url($avif) . '" type="image/avif">'; }
    $html .= '<img' . $cls . ' src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '" loading="' . esc_attr($loading) . '" decoding="async" ' . $extra . '>';
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

function thermodul_models() {
    $items = array(
        array('title'=>thermodul_i18n('ABPUSĒJAIS MODELIS','DOUBLE-SIDED MODEL','ДВУСТОРОННЯЯ МОДЕЛЬ','DVIPUSIS MODELIS','KAHEPOOLNE MUDEL'),'slug'=>'abpusejais-modelis','img'=>'https://www.thermodul.eu/wp-content/uploads/2014/01/modellobifacciale2.jpg'),
        array('title'=>thermodul_i18n('DUĀLAIS MODELIS','DUAL MODEL','ДУАЛЬНАЯ МОДЕЛЬ','DUALINIS MODELIS','DUAALMUDEL'),'slug'=>'dualais-modelis','img'=>'https://www.thermodul.eu/wp-content/uploads/2014/01/modellobivalente.jpg'),
        array('title'=>thermodul_i18n('DUBULTAIS HORIZONTĀLAIS MODELIS','DOUBLE HORIZONTAL MODEL','ДВОЙНАЯ ГОРИЗОНТАЛЬНАЯ МОДЕЛЬ','DVIGUBAS HORIZONTALUS MODELIS','TOPELT HORISONTAALNE MUDEL'),'slug'=>'dubultais-horizontalais-modelis','img'=>'https://www.thermodul.eu/wp-content/uploads/2014/01/modellofasciaorizz.jpg'),
        array('title'=>thermodul_i18n('DUBULTAIS VERTIKĀLAIS MODELIS','DOUBLE VERTICAL MODEL','ДВОЙНАЯ ВЕРТИКАЛЬНАЯ МОДЕЛЬ','DVIGUBAS VERTIKALUS MODELIS','TOPELT VERTIKAALNE MUDEL'),'slug'=>'dubultais-vertikalais-modelis','img'=>'https://www.thermodul.eu/wp-content/uploads/2014/01/modellofasciavert.jpg'),
        array('title'=>thermodul_i18n('ELEKTRISKAIS MODELIS','ELECTRIC MODEL','ЭЛЕКТРИЧЕСКАЯ МОДЕЛЬ','ELEKTRINIS MODELIS','ELEKTRIMUDEL'),'slug'=>'elektriskais-modelis','img'=>'https://www.thermodul.eu/wp-content/uploads/2014/01/modelloelettrico.jpg'),
    );
    foreach ($items as &$item) {
        $copy = thermodul_model_texts($item['slug']);
        $item['meta'] = $copy['meta'];
        $item['text'] = $copy['text'];
    }
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
        array('✓', thermodul_i18n('Vienmērīgs siltums','Even warmth','Равномерное тепло','Tolygi šiluma','Ühtlane soojus'), thermodul_i18n('Siltums izplatās pa telpas perimetru un palīdz novērst aukstās zonas.','Heat spreads around the room perimeter and helps remove cold zones.','Тепло распределяется по периметру помещения и устраняет холодные зоны.','Šiluma sklinda perimetru ir padeda pašalinti šaltas zonas.','Soojus levib ruumi perimeetris ja aitab vältida külmi tsoone.')),
        array('✓', thermodul_i18n('Veselīgs gaiss','Healthy air','Здоровый воздух','Sveikas oras','Tervislik õhk'), thermodul_i18n('Mazāka putekļu cirkulācija un patīkamāks mikroklimats bērniem un alerģiskiem cilvēkiem.','Less dust circulation and a more pleasant climate for children and allergy-sensitive users.','Меньше циркуляции пыли и более приятный микроклимат для детей и аллергиков.','Mažesnė dulkių cirkuliacija ir malonesnis mikroklimatas vaikams bei alergiškiems žmonėms.','Vähem tolmuringlust ning meeldivam mikrokliima lastele ja allergikutele.')),
        array('✓', thermodul_i18n('Energoefektīvi','Energy efficient','Энергоэффективно','Energiškai efektyvu','Energiasäästlik'), thermodul_i18n('Komfortu iespējams sasniegt ar zemāku telpas temperatūru un precīzu regulāciju.','Comfort can be reached at a lower room temperature with precise control.','Комфорт достигается при более низкой температуре и точном управлении.','Komfortą galima pasiekti esant žemesnei patalpos temperatūrai ir tiksliai reguliuojant.','Mugavuse saab saavutada madalama ruumitemperatuuri ja täpse juhtimisega.')),
        array('✓', thermodul_i18n('Droši un uzticami','Safe and reliable','Безопасно и надёжно','Saugūs ir patikimi','Ohutu ja töökindel'), thermodul_i18n('Zema virsmas temperatūra un pārbaudīti Eiropas standarti.','Low surface temperature and tested European standards.','Низкая температура поверхности и проверенные европейские стандарты.','Žema paviršiaus temperatūra ir patikrinti Europos standartai.','Madal pinnatemperatuur ja kontrollitud Euroopa standardid.')),
        array('✓', thermodul_i18n('Elegants dizains','Elegant design','Элегантный дизайн','Elegantiškas dizainas','Elegantne disain'), thermodul_i18n('Diskrēts risinājums, kas neaizņem sienas un saglabā tīru interjeru.','A discreet solution that keeps walls free and preserves a clean interior.','Деликатное решение освобождает стены и сохраняет чистый интерьер.','Diskretiškas sprendimas neužima sienų ir išlaiko švarų interjerą.','Diskreetne lahendus hoiab seinad vabad ja interjööri puhta.')),
        array('✓', thermodul_i18n('Viegla uzstādīšana','Easy installation','Простой монтаж','Lengvas montavimas','Lihtne paigaldus'), thermodul_i18n('Piemērots jaunbūvēm, renovācijām un radiatoru nomaiņai.','Suitable for new builds, renovations, and replacing radiators.','Подходит для новостроек, ремонта и замены радиаторов.','Tinka naujai statybai, renovacijai ir radiatorių keitimui.','Sobib uusehitustele, renoveerimiseks ja radiaatorite asendamiseks.')),
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
            'image' => 'https://www.thermodul.eu/wp-content/uploads/2014/01/modellobivalente.jpg'
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
    $gallery = thermodul_gallery_groups();
    $flat = thermodul_gallery_flat_items();
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

function thermodul_models_shortcode() {
    ob_start(); echo '<div class="row g-4 td-models-grid">';
    foreach (thermodul_models() as $m) { ?>
        <div class="col-md-6 col-xl-4"><article class="td-card td-model-card">
            <button class="td-model-image-link td-open-card" type="button" data-modal-title="<?php echo esc_attr($m['title']); ?>" data-modal-body="<?php echo esc_attr($m['text'] . ' ' . $m['meta']); ?>" data-modal-image="<?php echo esc_url($m['img']); ?>"><?php echo thermodul_picture($m['img'], $m['title'], 'td-model-img', 'lazy'); ?></button>
            <div class="td-card-body"><h3><?php echo esc_html($m['title']); ?></h3><p><strong><?php echo esc_html($m['meta']); ?></strong></p><p><?php echo esc_html($m['text']); ?></p><button type="button" class="td-link td-post-trigger td-as-link" data-post-title="<?php echo esc_attr($m['title']); ?>" data-post-body="<?php echo esc_attr($m['text'] . ' ' . $m['meta']); ?>" data-post-image="<?php echo esc_url($m['img']); ?>"><?php echo esc_html(thermodul_i18n('Skatīt vairāk','View more','Подробнее','Žiūrėti daugiau','Vaata rohkem')); ?> →</button></div></article></div>
    <?php } echo '</div>'; return ob_get_clean();
}
add_shortcode('thermodul_models', 'thermodul_models_shortcode');


function thermodul_home_blocks() {
    return '<!-- wp:html --><div class="td-admin-edit-note"><strong>THERMODUL demo:</strong> the front page is rendered by editable theme sections and shortcodes. Use THERMODUL block patterns/shortcodes to add custom content below if needed.</div><!-- /wp:html -->';
}

function thermodul_page_content($slug) {
    $model = thermodul_model_by_slug($slug);
    if ($model) {
        return '<!-- wp:paragraph --><p>'.esc_html($model['text']).'</p><!-- /wp:paragraph -->'
        .'<!-- wp:list --><ul><li>Viegli savietojams ar interjeru un esošu apkures sistēmu.</li><li>Vienmērīga siltuma sadale gar sienām un telpas perimetru.</li><li>Diskrēts korpuss ar plašām krāsu un apdares iespējām.</li></ul><!-- /wp:list -->'
        .'<!-- wp:paragraph --><p><strong>'.esc_html($model['meta']).'</strong></p><!-- /wp:paragraph -->'
        .'<!-- wp:shortcode -->[thermodul_contact_form]<!-- /wp:shortcode -->';
    }
    $map = array(
        'par-uznemumu' => '<!-- wp:heading --><h2>Par uzņēmumu</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Jau vairāk nekā 16 gadus Baltijas tirgū SIA “AQUADOCK BALTIJA” piedāvā grīdlīstes apsildes sistēmu THERMODUL. Sistēma atbilst Eiropas Savienības normām EN442 un ļauj radiatoru vietā izmantot estētisku, drošu un energoefektīvu perimetra apsildi.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>THERMODUL ir piemērots jaunbūvēm, renovācijām, sabiedriskām telpām un privātmājām. Nelielais izmērs netraucē mēbelēm, saglabā tīras interjera līnijas un palīdz uzturēt veselīgu mikroklimatu.</p><!-- /wp:paragraph --><!-- wp:shortcode -->[thermodul_models]<!-- /wp:shortcode -->',
        'sertifikacija' => '<!-- wp:heading --><h2>Mūsu idejām ir konkrēta forma un sertifikācija</h2><!-- /wp:heading --><!-- wp:paragraph --><p>THERMODUL ir pārbaudīta kvalitāte un apstiprināta efektivitāte. Produkts atbilst Eiropas kvalitātes un drošības standartiem, tostarp CE marķējumam, RoHS prasībām, EN442 testiem un IP20 aizsardzības klasei.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Sistēma atbilst ekoloģiskās būvniecības principiem, jo palīdz nodrošināt komfortu ar zemāku telpas temperatūru un samazinātu lieku gaisa kustību.</p><!-- /wp:paragraph --><!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><div class="td-card td-cert-card"><strong>CE</strong><span>Atbilst ES direktīvām</span></div></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><div class="td-card td-cert-card"><strong>RoHS</strong><span>Videi draudzīgi materiāli</span></div></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><div class="td-card td-cert-card"><strong>EN442</strong><span>Siltuma atdeves tests</span></div></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><div class="td-card td-cert-card"><strong>IP20</strong><span>Aizsardzības klase</span></div></div><!-- /wp:column --></div><!-- /wp:columns -->',
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
    foreach (thermodul_models() as $m) { if (!empty($m['img'])) { $paths[] = $m['img']; } }
    $paths[] = thermodul_uploads_img('2018/03/ka-THERMODUL-darbojas.jpg');
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
function thermodul_remove_extra_demo_pages() {
    foreach (array_keys(thermodul_anchor_map()) as $slug) {
        $page = get_page_by_path($slug);
        if ($page && $page->post_type === 'page') { wp_trash_post($page->ID); }
    }
}


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
    echo '<div class="wrap"><h1>THERMODUL Demo Import</h1><p>This refreshes the single-page THERMODUL homepage, anchor menu, gallery media and compressed AVIF variants. Extra legacy pages are moved to trash and redirected to homepage sections.</p><form method="post">'; wp_nonce_field('thermodul_demo_import'); submit_button('Import / Refresh THERMODUL demo content', 'primary', 'thermodul_demo_import'); echo '</form></div>';
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

function thermodul_hcaptcha_enabled() {
    return function_exists('wpbb_get_option') && (bool) wpbb_get_option('hcaptcha_enabled', 0);
}
function thermodul_hcaptcha_site_key() {
    return thermodul_hcaptcha_enabled() && function_exists('wpbb_get_option') ? trim((string) wpbb_get_option('hcaptcha_site_key', '')) : '';
}
function thermodul_hcaptcha_secret_key() {
    return thermodul_hcaptcha_enabled() && function_exists('wpbb_get_option') ? trim((string) wpbb_get_option('hcaptcha_secret_key', '')) : '';
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
    $response = wp_remote_post('https://hcaptcha.com/siteverify', array(
        'timeout' => 12,
        'body' => array(
            'secret' => $secret,
            'response' => $token,
            'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
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
    return '<div class="td-hcaptcha-wrap"><div class="h-captcha" data-sitekey="'.esc_attr($site_key).'" data-theme="light"></div><input type="hidden" name="wpbb_captcha_enabled" value="1"><input type="hidden" name="wpbb_captcha_provider" value="hcaptcha"><p class="td-hcaptcha-note">'.esc_html(thermodul_i18n('Aizsargāts ar hCaptcha.', 'Protected by hCaptcha.', 'Защищено hCaptcha.', 'Apsaugota naudojant hCaptcha.', 'Kaitstud hCaptcha abil.')).'</p></div>';
}
function thermodul_language_home_url($lang = null) {
    $lang = $lang ?: thermodul_current_lang();
    if (function_exists('pll_home_url')) { $url = pll_home_url($lang); if ($url) { return $url; } }
    return $lang === 'lv' ? home_url('/') : home_url('/' . $lang . '/');
}
function thermodul_language_url_for_current_object($lang) {
    return thermodul_language_home_url($lang);
}
function thermodul_language_switcher($class = 'td-lang-switch') {
    $langs = thermodul_supported_languages(); $current = thermodul_current_lang();
    echo '<div class="' . esc_attr($class) . '" aria-label="Language switcher">';
    foreach ($langs as $slug=>$info) { $active = $slug === $current ? ' is-active' : ''; echo '<a data-lang="'.esc_attr($slug).'" class="'.esc_attr(trim($active)).'" title="'.esc_attr($info['name']).'" hreflang="'.esc_attr($info['hreflang']).'" href="'.esc_url(thermodul_language_url_for_current_object($slug)).'">'.esc_html($info['label']).'</a>'; }
    echo '</div>';
}
function thermodul_hreflang_meta() {
    foreach (thermodul_supported_languages() as $slug=>$info) { echo '<link rel="alternate" hreflang="'.esc_attr($info['hreflang']).'" href="'.esc_url(thermodul_language_url_for_current_object($slug)).'">' . "\n"; }
    echo '<link rel="alternate" hreflang="x-default" href="'.esc_url(thermodul_language_url_for_current_object('lv')).'">' . "\n";
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
    $items = array(
        array('title'=>'Siltās grīdlīstes THERMODUL','url'=>home_url('/#sakums'),'type'=>'Sadaļa','excerpt'=>'Komforts, dizains un efektīva apsildes sistēma.'),
        array('title'=>'Kāpēc izvēlēties?','url'=>home_url('/#kapec'),'type'=>'Sadaļa','excerpt'=>'Vienmērīgs siltums, veselīgs gaiss, energoefektivitāte un dizains.'),
        array('title'=>'Kā tas darbojas','url'=>home_url('/#ka-tas-darbojas'),'type'=>'Sadaļa','excerpt'=>'Siltuma starojums, grīdlīstes sildelements un komforts.'),
        array('title'=>'Modeļi','url'=>home_url('/#modeli'),'type'=>'Sadaļa','excerpt'=>'Ūdens, elektriskais, duālais un dubultais modelis.'),
        array('title'=>'Galerija','url'=>thermodul_anchor_url('galerija'),'type'=>'Sadaļa','excerpt'=>'Uzstādījumu galerija un objektu kategorijas.'),
        array('title'=>'Kontakti','url'=>thermodul_anchor_url('pieprasijums'),'type'=>'Sadaļa','excerpt'=>'Saziņas forma, tālrunis un e-pasts.'),
    );
    foreach (thermodul_models() as $m) { $items[] = array('title'=>$m['title'], 'url'=>thermodul_anchor_url('modeli'), 'type'=>thermodul_i18n('Modelis','Model','Модель','Modelis','Mudel'), 'excerpt'=>$m['text']); }
    foreach (thermodul_posts_data() as $p) { $items[] = array('title'=>$p['title'], 'url'=>thermodul_anchor_url('padomi'), 'type'=>thermodul_i18n('Raksts','Post','Статья','Straipsnis','Artikkel'), 'excerpt'=>$p['excerpt']); }
    return $items;
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
        <label class="td-consent"><input type="checkbox" name="privacy" required> <?php echo esc_html(thermodul_i18n('Piekrītu, ka ar mani sazinās par šo pieprasījumu.','I agree to be contacted about this request.','Я согласен, чтобы со мной связались по этому запросу.','Sutinku, kad su manimi susisiektų dėl šios užklausos.','Nõustun, et minuga võetakse selle päringu osas ühendust.')); ?></label>
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
add_action('wp_head', 'thermodul_onepage_seo_meta_v27', 2);
