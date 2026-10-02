<?php if (!defined('ABSPATH')) { exit; } ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class('td-theme'); ?>>
<?php wp_body_open(); ?>
<header class="td-site-head">
    <div class="td-topbar">
        <div class="td-container">
            <div><?php echo esc_html(thermodul_i18n('Siltās grīdlīstes apsildes sistēma','Warm baseboard heating system','Система тёплого плинтуса','Šiltų grindjuosčių šildymo sistema','Sooja põrandaliistu küttesüsteem')); ?></div>
            <div class="td-topbar-contact">
                <a class="td-contact-link" href="tel:+37129256299" aria-label="Phone +371 29256299"><span class="td-contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.8a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7A2 2 0 0 1 22 16.9z"/></svg></span><span>+371 29256299</span></a>
                <a class="td-contact-link" href="mailto:info@thermodul.eu" aria-label="Email info@thermodul.eu"><span class="td-contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/><path d="m22 7-10 6L2 7"/></svg></span><span>info@thermodul.eu</span></a>
                <?php thermodul_language_switcher('td-lang-switch td-top-lang'); ?>
            </div>
        </div>
    </div>
    <div class="td-header">
        <div class="td-container td-header-inner">
            <?php thermodul_brand(); ?>
            <nav class="td-nav" aria-label="<?php esc_attr_e('Galvenā izvēlne','thermodul'); ?>"><?php thermodul_nav('primary'); ?></nav>
            <div class="td-header-tools"><button class="td-search-toggle" id="tdSearchToggle" type="button" aria-expanded="false" aria-controls="tdSearchPanel"><?php echo esc_html(thermodul_i18n('Meklēt','Search','Поиск','Ieškoti','Otsi')); ?></button><div class="td-header-cta"><a class="td-btn" href="<?php echo esc_url(thermodul_anchor_url('pieprasijums')); ?>"><?php echo esc_html(thermodul_i18n('Pieprasīt informāciju','Request information','Запросить информацию','Prašyti informacijos','Küsi infot')); ?></a></div></div>
            <button class="td-menu-toggle" type="button" aria-expanded="false" aria-controls="td-mobile-menu" aria-label="<?php echo esc_attr(thermodul_i18n('Atvērt izvēlni','Open menu','Открыть меню','Atidaryti meniu','Ava menüü')); ?>">
                <span class="td-menu-icon td-menu-icon-open" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M4 6h16M4 12h16M4 18h16"/></svg></span>
                <span class="td-menu-icon td-menu-icon-close" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M6 6l12 12M18 6L6 18"/></svg></span>
            </button>
        </div>
        <div id="tdSearchPanel" class="td-search-panel td-container"><?php echo do_shortcode('[thermodul_ajax_search]'); ?></div>
        <div id="td-mobile-menu" class="td-mobile-menu td-container"><?php thermodul_nav('primary'); ?><?php thermodul_language_switcher('td-lang-switch td-mobile-lang'); ?><?php echo do_shortcode('[thermodul_ajax_search]'); ?></div>
    </div>
</header>
<main id="content" class="td-main">
