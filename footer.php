</main>
<section class="td-cta">
    <div class="td-container">
        <div><h2><?php echo esc_html(thermodul_i18n('Vai vēlaties uzzināt vairāk par THERMODUL?','Would you like to learn more about THERMODUL?','Хотите узнать больше о THERMODUL?','Norite sužinoti daugiau apie THERMODUL?','Kas soovite THERMODULi kohta rohkem teada?')); ?></h2><p><?php echo esc_html(thermodul_i18n('Mūsu speciālisti palīdzēs atrast labāko risinājumu jūsu projektam.','Our specialists will help find the best solution for your project.','Наши специалисты помогут подобрать лучшее решение для вашего проекта.','Mūsų specialistai padės rasti geriausią sprendimą jūsų projektui.','Meie spetsialistid aitavad leida teie projektile parima lahenduse.')); ?></p></div>
        <a class="td-btn td-btn-outline" href="<?php echo esc_url(thermodul_anchor_url('pieprasijums')); ?>"><?php echo esc_html(thermodul_i18n('Pieprasīt informāciju','Request information','Запросить информацию','Prašyti informacijos','Küsi infot')); ?> →</a>
    </div>
</section>
<footer class="td-footer">
    <div class="td-container td-footer-grid">
        <div><?php thermodul_brand(); ?><p><?php echo esc_html(thermodul_i18n('Mūsdienīga un efektīva siltās grīdlīstes apsildes sistēma komfortam, veselībai un enerģijas taupīšanai.','A modern and efficient warm baseboard heating system for comfort, health, and energy savings.','Современная и эффективная система тёплого плинтуса для комфорта, здоровья и экономии энергии.','Moderni ir efektyvi šiltų grindjuosčių šildymo sistema komfortui, sveikatai ir energijos taupymui.','Kaasaegne ja tõhus soe põrandaliistu küttesüsteem mugavuseks, terviseks ja energiasäästuks.')); ?></p></div>
        <div><h3><?php echo esc_html(thermodul_i18n('Ātrās saites','Quick links','Быстрые ссылки','Greitos nuorodos','Kiirlingid')); ?></h3><?php thermodul_nav('footer'); ?></div>
        <div><h3><?php echo esc_html(thermodul_i18n('Kontakti','Contacts','Контакты','Kontaktai','Kontakt')); ?></h3><p>AQUADOCK BALTIJA SIA<br>Dārza iela 17, Ikšķile, LV-5052</p><p><a href="tel:+37129256299">+371 29256299</a><br><a href="mailto:info@thermodul.eu">info@thermodul.eu</a></p></div>
        <div><h3><?php echo esc_html(thermodul_i18n('Darba laiks','Working hours','Время работы','Darbo laikas','Tööaeg')); ?></h3><p><?php echo esc_html(thermodul_i18n('Pirmdiena – Ceturtdiena','Monday – Thursday','Понедельник – Четверг','Pirmadienis – Ketvirtadienis','Esmaspäev – Neljapäev')); ?><br>08:00 – 17:00</p><p><?php echo esc_html(thermodul_i18n('Piektdiena','Friday','Пятница','Penktadienis','Reede')); ?><br>08:00 – 16:00</p><p><?php echo esc_html(thermodul_i18n('Sestdiena – Svētdiena','Saturday – Sunday','Суббота – Воскресенье','Šeštadienis – Sekmadienis','Laupäev – Pühapäev')); ?><br><?php echo esc_html(thermodul_i18n('Slēgts','Closed','Закрыто','Uždaryta','Suletud')); ?></p></div>
    </div>
    <div class="td-footer-bottom"><div class="td-container">© <?php echo esc_html(date('Y')); ?> THERMODUL. <?php echo esc_html(thermodul_i18n('Visas tiesības aizsargātas.','All rights reserved.','Все права защищены.','Visos teisės saugomos.','Kõik õigused kaitstud.')); ?></div></div>
</footer>
<div class="td-floating-actions" aria-label="Quick actions">
    <a class="td-float-btn" href="#sakums" aria-label="Scroll up">↑</a>
    <a class="td-float-btn" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url(rawurlencode(home_url('/'))); ?>" target="_blank" rel="noopener" aria-label="Share on Facebook">f</a>
    <a class="td-float-btn" href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_url(rawurlencode(home_url('/'))); ?>" target="_blank" rel="noopener" aria-label="Share on LinkedIn">in</a>
</div>
<?php wp_footer(); ?>
</body>
</html>
