<?php
get_header();
$data = thermodul_home_data();
$t = thermodul_home_text();
$features = thermodul_feature_translations();
$posts = thermodul_posts_data();
?>
<div id="thermodul-react-home" class="td-react-root" data-static-rendered="true">
    <section class="td-hero td-hero-premium td-hero-v30" id="sakums">
        <div class="td-container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="td-hero-copy">
                        <span class="td-eyebrow"><?php echo esc_html($t['hero_eyebrow']); ?></span>
                        <h1><?php echo esc_html($t['hero_title_a']); ?> <span class="red"><?php echo esc_html($t['hero_title_b']); ?></span></h1>
                        <p class="td-kicker"><?php echo esc_html($t['hero_kicker']); ?></p>
                        <p class="td-lead"><?php echo esc_html($t['hero_lead']); ?></p>
                        <div class="td-hero-actions">
                            <a class="td-btn" href="#pieprasijums"><?php echo esc_html($t['request']); ?> →</a>
                            <a class="td-btn td-btn-outline" href="#galerija"><?php echo esc_html($t['gallery']); ?></a>
                        </div>
                        <div class="td-pill-row">
                            <span class="td-pill">✓ <?php echo esc_html(thermodul_i18n('Veselīgs mikroklimats','Healthy microclimate','Здоровый микроклимат','Sveikas mikroklimatas','Tervislik mikrokliima')); ?></span>
                            <span class="td-pill">✓ <?php echo esc_html(thermodul_i18n('Zems enerģijas patēriņš','Low energy demand','Низкое энергопотребление','Mažas energijos poreikis','Väike energiatarve')); ?></span>
                            <span class="td-pill">✓ <?php echo esc_html(thermodul_i18n('Diskrēta montāža','Discreet installation','Дискретный монтаж','Diskretiškas montavimas','Diskreetne paigaldus')); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="td-hero-visual td-hero-photo">
                        <?php echo thermodul_picture($data['heroImage'], 'THERMODUL interior heating example', '', 'eager', 'fetchpriority="high"'); ?>
                        <div class="td-hero-stat"><strong>80–85%</strong><span><?php echo esc_html(thermodul_i18n('siltuma starojums','radiant heat','лучистое тепло','spindulinė šiluma','kiirgussoojus')); ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="td-section" id="kapec">
        <div class="td-container">
            <h2 class="td-section-title center"><?php echo esc_html($t['why']); ?></h2>
            <div class="row g-4">
                <?php foreach ($features as $f) : ?>
                    <div class="col-md-6 col-xl-4">
                        <button class="td-card td-feature-card td-open-card" type="button" data-modal-title="<?php echo esc_attr($f[1]); ?>" data-modal-body="<?php echo esc_attr($f[2]); ?>">
                            <div class="td-card-body">
                                <div class="td-icon" aria-hidden="true"><?php echo esc_html($f[0]); ?></div>
                                <h3><?php echo esc_html($f[1]); ?></h3>
                                <p><?php echo esc_html($f[2]); ?></p>
                            </div>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="td-section td-section-soft td-about-bridge" id="par-thermodul">
        <div class="td-container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <span class="td-eyebrow"><?php echo esc_html(thermodul_i18n('Par THERMODUL','About THERMODUL','О THERMODUL','Apie THERMODUL','THERMODUList')); ?></span>
                    <h2 class="td-section-title"><?php echo esc_html(thermodul_i18n('Pārbaudīts grīdlīstes apkures risinājums','A proven baseboard heating solution','Проверенное решение плинтусного отопления','Patikrintas grindjuosčių šildymo sprendimas','Tõestatud põrandaliistukütte lahendus')); ?></h2>
                </div>
                <div class="col-lg-7">
                    <p><?php echo esc_html(thermodul_i18n('THERMODUL Baltijā piedāvā AQUADOCK BALTIJA SIA. Sistēma paredzēta jaunbūvēm, renovācijām un radiatoru nomaiņai, un tā ir pieejama ūdens, elektriskā, duālā un paaugstinātas jaudas konfigurācijās.','THERMODUL is represented in the Baltics by AQUADOCK BALTIJA SIA. The system is designed for new builds, renovations and radiator replacement, with water, electric, dual and higher-output configurations.','THERMODUL в странах Балтии представляет AQUADOCK BALTIJA SIA. Система подходит для новостроек, реконструкции и замены радиаторов; доступны водяные, электрические, дуальные и усиленные варианты.','Baltijos šalyse THERMODUL atstovauja AQUADOCK BALTIJA SIA. Sistema skirta naujai statybai, renovacijai ir radiatorių keitimui; galimi vandens, elektriniai, dualiniai ir didesnės galios variantai.','Baltikumis esindab THERMODULit AQUADOCK BALTIJA SIA. Süsteem sobib uusehituseks, renoveerimiseks ja radiaatorite asendamiseks ning on saadaval vee-, elektri-, duaal- ja suurema võimsusega lahendustes.')); ?></p>
                    <p><a class="td-link" href="<?php echo esc_url(thermodul_legacy_page_url('par-uznemumu', 'kapec')); ?>"><?php echo esc_html(thermodul_i18n('Par uzņēmumu un sistēmu','About the company and system','О компании и системе','Apie įmonę ir sistemą','Ettevõttest ja süsteemist')); ?> →</a></p>
                </div>
            </div>
        </div>
    </section>

    <section class="td-section td-how" id="ka-tas-darbojas">
        <div class="td-container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <span class="td-eyebrow"><?php echo esc_html(thermodul_i18n('Darbības princips','Operating principle','Принцип работы','Veikimo principas','Tööpõhimõte')); ?></span>
                    <h2 class="td-section-title"><?php echo esc_html($t['how']); ?></h2>
                    <p><?php echo esc_html(thermodul_i18n('THERMODUL galvenokārt darbojas ar siltuma starojumu — siena uzsilst vienmērīgi, mazinās putekļu kustība un komforts ir jūtams arī pie zemākas gaisa temperatūras.','THERMODUL works mainly through radiant heat: the wall warms evenly, dust movement is reduced, and comfort is felt even at a lower air temperature.','THERMODUL работает в основном за счёт лучистого тепла: стена прогревается равномерно, движение пыли уменьшается, а комфорт ощущается даже при более низкой температуре воздуха.','THERMODUL daugiausia veikia spinduline šiluma: siena šyla tolygiai, sumažėja dulkių judėjimas, o komfortas jaučiamas ir esant žemesnei oro temperatūrai.','THERMODUL toimib peamiselt kiirgussoojusega: sein soojeneb ühtlaselt, tolmu liikumine väheneb ja mugavus on tuntav ka madalama õhutemperatuuriga.')); ?></p>
                    <ol class="td-number-list">
                        <li><?php echo esc_html(thermodul_i18n('Aukstais gaiss ieplūst zem grīdlīstes.','Cold air enters below the baseboard.','Холодный воздух поступает под плинтус.','Šaltas oras patenka po grindjuoste.','Külm õhk siseneb põrandaliistu alt.')); ?></li>
                        <li><?php echo esc_html(thermodul_i18n('Sildelements uzsilda gaisu un sienas perimetru.','The element heats the air and wall perimeter.','Нагревательный элемент прогревает воздух и периметр стены.','Elementas šildo orą ir sienos perimetrą.','Kütteelement soojendab õhku ja seina perimeetrit.')); ?></li>
                        <li><?php echo esc_html(thermodul_i18n('Siltums paceļas gar sienu un izplatās telpā.','Heat rises along the wall and spreads through the room.','Тепло поднимается вдоль стены и распространяется по помещению.','Šiluma kyla palei sieną ir pasklinda patalpoje.','Soojus tõuseb mööda seina ja levib ruumis.')); ?></li>
                        <li><?php echo esc_html(thermodul_i18n('Veidojas vienmērīgs mikroklimats bez radiatoru karstajiem punktiem.','An even microclimate forms without radiator hot spots.','Формируется ровный микроклимат без горячих зон радиаторов.','Susidaro tolygus mikroklimatas be radiatorių karštų zonų.','Tekib ühtlane mikrokliima ilma radiaatorite kuumade punktideta.')); ?></li>
                    </ol>
                </div>
                <div class="col-lg-7">
                    <div class="td-how-panel" aria-label="THERMODUL operating diagram">
                        <?php echo thermodul_picture($data['diagramImage'], 'THERMODUL operating diagram', '', 'lazy'); ?>
                        <div class="td-flow-badges"><span><?php echo esc_html(thermodul_i18n('Aukstais gaiss','Cold air','Холодный воздух','Šaltas oras','Külm õhk')); ?></span><span><?php echo esc_html(thermodul_i18n('Siltais starojums','Radiant warmth','Лучистое тепло','Spindulinė šiluma','Kiirgussoojus')); ?></span><span><?php echo esc_html(thermodul_i18n('Vienmērīga telpa','Even room','Равномерная комната','Tolygi patalpa','Ühtlane ruum')); ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="td-section" id="modeli">
        <div class="td-container">
            <div class="td-section-head">
                <div><span class="td-eyebrow"><?php echo esc_html(thermodul_i18n('Produkti','Products','Продукты','Produktai','Tooted')); ?></span><h2 class="td-section-title"><?php echo esc_html($t['models']); ?></h2></div>
            </div>
            <?php echo do_shortcode('[thermodul_models]'); ?>
        </div>
    </section>

    <section class="td-section td-section-soft" id="galerija">
        <div class="td-container">
            <div class="td-section-head">
                <div><span class="td-eyebrow"><?php echo esc_html(thermodul_i18n('Realizētie objekti','Installed projects','Реализованные объекты','Įgyvendinti objektai','Valminud objektid')); ?></span><h2 class="td-section-title"><?php echo esc_html($t['gallery_title']); ?></h2></div>
            </div>
            <?php echo do_shortcode('[thermodul_gallery grouped="no" ajax="yes" compact="yes" limit="5" step="5" button_label="'.$t['load_more'].'"]'); ?>
        </div>
    </section>

    <section class="td-section" id="sertifikacija">
        <div class="td-container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <span class="td-eyebrow"><?php echo esc_html(thermodul_i18n('Drošība un standarti','Safety and standards','Безопасность и стандарты','Sauga ir standartai','Ohutus ja standardid')); ?></span>
                    <h2 class="td-section-title"><?php echo esc_html($t['cert']); ?></h2>
                    <p><?php echo esc_html(thermodul_i18n('Pašreizējā THERMODUL vietne dokumentē atbilstību EN442 un sertifikāciju saskaņā ar Eiropas apkures efektivitātes normām.','The current THERMODUL site documents EN442 compliance and certification under European heating-efficiency standards.','Текущий сайт THERMODUL документирует соответствие EN442 и сертификацию по европейским нормам эффективности отопления.','Dabartinėje THERMODUL svetainėje dokumentuojama atitiktis EN442 ir sertifikavimas pagal Europos šildymo efektyvumo normas.','Praegune THERMODULi veebileht dokumenteerib EN442 vastavust ja sertifitseerimist Euroopa kütte tõhususe normide järgi.')); ?></p>
                    <ul class="td-check-list"><li>EN442</li><li><?php echo esc_html(thermodul_i18n('Eiropas apkures efektivitātes normas','European heating-efficiency standards','Европейские нормы эффективности отопления','Europos šildymo efektyvumo normos','Euroopa kütte tõhususe normid')); ?></li><li><?php echo esc_html(thermodul_i18n('Ekoloģiskās būvniecības principi','Ecological building principles','Принципы экологичного строительства','Ekologiškos statybos principai','Ökoloogilise ehituse põhimõtted')); ?></li><li>Plan Expo Fair 2006</li></ul>
                </div>
                <div class="col-lg-7">
                    <div class="row g-3">
                        <?php foreach (array(
                            'EN442'=>thermodul_i18n('Siltuma atdeves prasības','Heat-output requirements','Требования к теплоотдаче','Šilumos atidavimo reikalavimai','Soojusvõimsuse nõuded'),
                            'EU'=>thermodul_i18n('Apkures efektivitātes normas','Heating-efficiency standards','Нормы эффективности отопления','Šildymo efektyvumo normos','Kütte tõhususe normid'),
                            'ECO'=>thermodul_i18n('Ekoloģiskās būvniecības principi','Ecological building principles','Экологичное строительство','Ekologiškos statybos principai','Ökoloogilise ehituse põhimõtted'),
                            '2006'=>thermodul_i18n('Plan Expo Fair, Dublina','Plan Expo Fair, Dublin','Plan Expo Fair, Дублин','Plan Expo Fair, Dublinas','Plan Expo Fair, Dublin')
                        ) as $cert=>$text) : ?>
                            <div class="col-6 col-md-3"><button class="td-card td-cert-card td-open-card" type="button" data-modal-title="<?php echo esc_attr($cert); ?>" data-modal-body="<?php echo esc_attr($text); ?>"><strong><?php echo esc_html($cert); ?></strong><span><?php echo esc_html($text); ?></span></button></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="td-section td-posts-section" id="padomi">
        <div class="td-container">
            <div class="td-section-head">
                <div><span class="td-eyebrow"><?php echo esc_html(thermodul_i18n('Raksti un padomi','Articles and tips','Статьи и советы','Straipsniai ir patarimai','Artiklid ja nõuanded')); ?></span><h2 class="td-section-title"><?php echo esc_html($t['posts']); ?></h2></div>
            </div>
            <div class="row g-4">
                <?php foreach ($posts as $post) : ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="td-post-card">
                            <button type="button" class="td-post-trigger" data-post-title="<?php echo esc_attr($post['title']); ?>" data-post-body="<?php echo esc_attr($post['body']); ?>" data-post-image="<?php echo esc_url(!empty($post['modal_image']) ? $post['modal_image'] : $post['image']); ?>">
                                <?php echo thermodul_picture($post['image'], $post['title'], 'td-post-img', 'lazy'); ?>
                                <span class="td-post-content">
                                    <strong><?php echo esc_html($post['title']); ?></strong>
                                    <small><?php echo esc_html($post['excerpt']); ?></small>
                                    <em><?php echo esc_html(thermodul_i18n('Lasīt vairāk','Read more','Читать далее','Skaityti daugiau','Loe edasi')); ?> →</em>
                                </span>
                            </button>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<section class="td-section td-contact-premium-section" id="pieprasijums">
    <div class="td-container">
        <div class="td-contact-premium">
            <div class="td-contact-copy">
                <span class="td-eyebrow"><?php echo esc_html(thermodul_i18n('Saziņa','Contact','Контакт','Kontaktai','Kontakt')); ?></span>
                <h2 class="td-section-title"><?php echo esc_html($t['contact']); ?></h2>
                <p><?php echo esc_html(thermodul_i18n('Nosūtiet īsu projekta aprakstu, un THERMODUL speciālists palīdzēs izvēlēties piemērotu modeli, jaudu un uzstādīšanas risinājumu.','Send a short project description and a THERMODUL specialist will help choose the right model, output, and installation solution.','Отправьте краткое описание проекта, и специалист THERMODUL поможет выбрать подходящую модель, мощность и решение монтажа.','Atsiųskite trumpą projekto aprašymą, ir THERMODUL specialistas padės parinkti modelį, galią ir montavimo sprendimą.','Saatke lühike projekti kirjeldus ja THERMODUL spetsialist aitab valida sobiva mudeli, võimsuse ja paigalduslahenduse.')); ?></p>
                <ul class="td-check-list"><li><?php echo esc_html(thermodul_i18n('Privātmājām un dzīvokļiem','Private homes and apartments','Частные дома и квартиры','Privatiems namams ir butams','Eramutele ja korteritele')); ?></li><li><?php echo esc_html(thermodul_i18n('Birojiem un komerctelpām','Offices and commercial spaces','Офисы и коммерческие помещения','Biurams ir komercinėms patalpoms','Kontoritele ja äripindadele')); ?></li><li><?php echo esc_html(thermodul_i18n('Ūdens, elektriskie un kombinētie modeļi','Water, electric, and combined models','Водяные, электрические и комбинированные модели','Vandens, elektriniai ir kombinuoti modeliai','Vee-, elektri- ja kombineeritud mudelid')); ?></li></ul>
                <div class="td-contact-direct"><strong>+371 29256299</strong><span>info@thermodul.eu</span></div>
            </div>
            <div class="td-contact-form-wrap"><?php echo do_shortcode('[thermodul_contact_form]'); ?></div>
        </div>
    </div>
</section>
<?php get_footer(); ?>
