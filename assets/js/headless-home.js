(function (wp) {
  var root = document.getElementById('thermodul-react-home');
  if (!root || root.getAttribute('data-static-rendered') === 'true' || !wp || !wp.element) return;

  var e = wp.element.createElement;
  var data = window.thermodulHomeData || {};

  function A(props) {
    var children = Array.prototype.slice.call(arguments, 1);
    return e.apply(null, ['a', props].concat(children));
  }

  function SectionTitle(props) {
    return e('h2', { className: 'td-section-title ' + (props.center ? 'center' : '') }, props.children);
  }

  function Icon(props) {
    return e('div', { className: 'td-icon', 'aria-hidden': 'true' }, props.char);
  }

  var features = [
    ['🌡️', 'Vienmērīgs starojuma siltums', 'THERMODUL darbojas galvenokārt (80–85%) ar siltuma starojumu; vienmērīga siltuma sadale ļauj komfortu sasniegt arī pie zemākas telpas temperatūras.'],
    ['🍃', 'Mierīgs mikroklimats', 'Lēnā konvekcija (15–20%) paredzēta, lai mazinātu strauju gaisa un putekļu kustību telpā.'],
    ['💧', 'Mazs ūdens daudzums', 'Pašreizējā THERMODUL informācijā norādīti aptuveni 276 ml ūdens uz vienu sistēmas metru.'],
    ['🔧', 'Vienkārši un funkcionāli', 'Stiprināma pie sienas, bez īpašas konstrukcijas; viegli pārbaudāma un tīrāma.'],
    ['🏠', 'Plašs pielietojums', 'Jaunbūvēm, renovācijām, radiatoru nomaiņai un kombinēšanai ar citām apkures sistēmām.'],
    ['📏', 'Dizains un apdares', 'Baltā, tumši brūnā un anodēta alumīnija apdare; pieejamas arī RAL, koka un marmora imitācijas.']
  ];

  function Home() {
    var heroStyle = { '--td-hero-image': 'url(' + (data.heroImage || '') + ')' };
    var diagramStyle = { '--td-diagram-image': 'url(' + (data.diagramImage || '') + ')' };
    var models = data.models || [];
    var gallery = data.gallery || [];

    return e('div', null,
      e('section', { id: 'sakums', className: 'td-hero', style: heroStyle },
        e('div', { className: 'td-container' },
          e('div', { className: 'td-hero-copy' },
            e('h1', null, 'Siltās grīdlīstes ', e('span', { className: 'red' }, 'THERMODUL')),
            e('p', { className: 'td-kicker' }, 'Komforts. Dizains. Efektivitāte.'),
            e('p', { className: 'td-lead' }, 'Mūsdienīga siltās grīdlīstes apsildes sistēma, kas nodrošina vienmērīgu siltumu visā telpā, taupa enerģiju un saglabā interjera estētiku.'),
            e('div', { className: 'td-hero-actions' },
              A({ className: 'td-btn', href: data.contactUrl || '/kontakti/' }, 'Uzzināt vairāk →'),
              A({ className: 'td-btn td-btn-outline', href: data.galleryUrl || '/galerija/' }, 'Skatīt galeriju')
            ),
            e('div', { className: 'td-pill-row' }, ['Veselīgs mikroklimats', 'Droši un uzticami', 'Zems enerģijas patēriņš'].map(function (t) {
              return e('span', { className: 'td-pill', key: t },
                e('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '2' }, e('path', { d: 'M20 6L9 17l-5-5' })),
                t
              );
            }))
          )
        )
      ),

      e('section', { id: 'kapec', className: 'td-section' },
        e('div', { className: 'td-container' },
          e(SectionTitle, { center: true }, 'Kāpēc izvēlēties?'),
          e('div', { className: 'row g-4' }, features.map(function (f) {
            return e('div', { className: 'col-md-6 col-xl-2', key: f[1] },
              e('article', { className: 'td-card' },
                e('div', { className: 'td-card-body' },
                  e(Icon, { char: f[0] }),
                  e('h3', null, f[1]),
                  e('p', null, f[2])
                )
              )
            );
          }))
        )
      ),

      e('section', { id: 'ka-tas-darbojas', className: 'td-section td-how' },
        e('div', { className: 'td-container' },
          e('div', { className: 'row g-5 align-items-center' },
            e('div', { className: 'col-lg-5' },
              e(SectionTitle, null, 'Kā tas darbojas'),
              e('p', null, 'THERMODUL siltās grīdlīstes izmanto dabisko konvekciju un starojumu, lai vienmērīgi sasildītu telpu.'),
              e('ol', { className: 'td-number-list' }, [
                'Aukstais gaiss no telpas ieplūst zem grīdlīstes.',
                'Sildelements sasilda gaisu grīdlīstes iekšpusē.',
                'Siltais gaiss paceļas gar sienu un izplatās telpā.',
                'Cirkulācija nodrošina komfortablu un vienmērīgu siltumu bez straujas gaisa kustības.'
              ].map(function (t) { return e('li', { key: t }, t); }))
            ),
            e('div', { className: 'col-lg-7' },
              e('div', { className: 'td-how-diagram', style: diagramStyle }, e('div', { className: 'td-flow-line' }))
            )
          )
        )
      ),

      e('section', { id: 'modeli', className: 'td-section' },
        e('div', { className: 'td-container' },
          e(SectionTitle, null, 'Modeļi'),
          e('div', { className: 'row g-4' }, models.map(function (m) {
            return e('div', { className: 'col-md-6 col-xl-4', key: m.slug },
              e('article', { className: 'td-card' },
                e('img', { className: 'td-model-img', src: m.img, alt: m.title, loading: 'lazy' }),
                e('div', { className: 'td-card-body' },
                  e('h3', null, m.title),
                  e('p', null, e('strong', null, m.meta)),
                  e('p', null, m.text),
                  A({ className: 'td-link', href: m.url || ('/' + m.slug + '/') }, 'Skatīt vairāk →')
                )
              )
            );
          }))
        )
      ),

      e('section', { id: 'galerija', className: 'td-section td-section-soft' },
        e('div', { className: 'td-container' },
          e(SectionTitle, null, 'Galerija'),
          e('div', { className: 'td-gallery-grid' }, gallery.map(function (g, i) {
            return A({ className: 'td-gallery-item', href: g.src, 'data-td-lightbox': 'true', key: g.src + i },
              e('img', { src: g.src, alt: g.label, loading: 'lazy' }),
              e('span', { className: 'td-gallery-caption' }, g.label)
            );
          }))
        )
      ),

      e('section', { id: 'sertifikacija', className: 'td-section' },
        e('div', { className: 'td-container' },
          e('div', { className: 'row g-5 align-items-center' },
            e('div', { className: 'col-lg-4' },
              e(SectionTitle, null, 'Sertifikācija'),
              e('p', null, 'Pārbaudīta kvalitāte un apstiprināta efektivitāte. THERMODUL produkts atbilst Eiropas kvalitātes un drošības standartiem.')
            ),
            e('div', { className: 'col-lg-8' },
              e('div', { className: 'row g-3' }, ['EN442', 'EU', 'ECO', '2006'].map(function (t) {
                return e('div', { className: 'col-6 col-md-3', key: t },
                  e('div', { className: 'td-card td-cert-card' },
                    e('div', null,
                      e('strong', null, t),
                      e('span', null, t === 'EN442' ? 'Siltuma atdeves prasības' : (t === '2006' ? 'Plan Expo Fair, Dublin' : (t === 'ECO' ? 'Ekoloģiskās būvniecības principi' : 'Apkures efektivitātes normas')))
                    )
                  )
                );
              }))
            )
          )
        )
      )
    );
  }

  if (wp.element.createRoot) {
    wp.element.createRoot(root).render(e(Home));
  } else {
    wp.element.render(e(Home), root);
  }
})(window.wp);
