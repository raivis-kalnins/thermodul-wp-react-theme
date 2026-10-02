(function(){
  const $=(sel,root=document)=>root.querySelector(sel);
  const $$=(sel,root=document)=>Array.from(root.querySelectorAll(sel));
  const theme=window.thermodulTheme||{};
  const toggle=$('.td-menu-toggle');
  const menu=$('#td-mobile-menu');
  if(toggle&&menu){const openLabel=toggle.getAttribute('aria-label')||'Open menu';const closeLabel={lv:'Aizvērt izvēlni',en:'Close menu',ru:'Закрыть меню',lt:'Uždaryti meniu',et:'Sulge menüü'}[theme.lang]||'Close menu';toggle.addEventListener('click',()=>{const open=menu.classList.toggle('is-open');toggle.setAttribute('aria-expanded',open?'true':'false');toggle.setAttribute('aria-label',open?closeLabel:openLabel);});}
  function initLazy(root=document){const imgs=$$('img[data-src],source[data-srcset]',root);if(!imgs.length)return;const load=(el)=>{if(el.dataset.src){el.src=el.dataset.src;delete el.dataset.src;}if(el.dataset.srcset){el.srcset=el.dataset.srcset;delete el.dataset.srcset;}el.classList.add('is-loaded');};if(!('IntersectionObserver'in window)){imgs.forEach(load);return;}const io=new IntersectionObserver((entries)=>{entries.forEach(e=>{if(e.isIntersecting){load(e.target);io.unobserve(e.target);}});},{rootMargin:'350px 0px'});imgs.forEach(el=>io.observe(el));}
  initLazy();

  /* hCaptcha is intentionally not part of the initial page payload. Load and
     explicitly render it when the contact form approaches the viewport or the
     user starts interacting with the form. */
  let hcaptchaPromise=null;
  function renderHcaptcha(root=document){
    if(!window.hcaptcha)return;
    $$('.h-captcha',root).forEach(el=>{
      if(el._tdHcaptchaId!==undefined)return;
      const key=el.dataset.sitekey||theme.hcaptchaSiteKey||'';
      if(!key)return;
      try{el._tdHcaptchaId=window.hcaptcha.render(el,{sitekey:key,theme:el.dataset.theme||'light'});}catch(err){}
    });
  }
  function loadHcaptcha(root=document){
    if(!theme.hcaptchaEnabled)return Promise.resolve(false);
    if(window.hcaptcha){renderHcaptcha(root);return Promise.resolve(true);}
    if(hcaptchaPromise)return hcaptchaPromise.then(()=>{renderHcaptcha(root);return true;});
    hcaptchaPromise=new Promise((resolve,reject)=>{
      const existing=document.querySelector('script[data-td-hcaptcha]');
      if(existing){existing.addEventListener('load',()=>resolve(true),{once:true});existing.addEventListener('error',reject,{once:true});return;}
      const sc=document.createElement('script');sc.async=true;sc.defer=true;sc.dataset.tdHcaptcha='1';
      sc.src='https://js.hcaptcha.com/1/api.js?render=explicit&hl='+encodeURIComponent(theme.hcaptchaLang||theme.lang||'lv');
      sc.onload=()=>resolve(true);sc.onerror=reject;document.head.appendChild(sc);
    });
    return hcaptchaPromise.then(()=>{renderHcaptcha(root);return true;}).catch(()=>false);
  }
  function resetHcaptcha(form){
    const el=$('.h-captcha',form);if(!el||!window.hcaptcha)return;
    try{if(el._tdHcaptchaId!==undefined)window.hcaptcha.reset(el._tdHcaptchaId);else window.hcaptcha.reset();}catch(err){}
  }
  function initHcaptchaLazy(){
    if(!theme.hcaptchaEnabled)return;
    const forms=$$('[data-td-ajax-form]');if(!forms.length)return;
    const prime=form=>loadHcaptcha(form);
    forms.forEach(form=>{
      form.addEventListener('focusin',()=>prime(form),{once:true,passive:true});
      form.addEventListener('pointerdown',()=>prime(form),{once:true,passive:true});
    });
    if(!('IntersectionObserver'in window)){return;}
    const io=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){prime(entry.target);io.unobserve(entry.target);}}),{rootMargin:'350px 0px'});
    forms.forEach(form=>io.observe(form));
  }
  initHcaptchaLazy();
  document.addEventListener('click',function(e){
    const link=e.target.closest('[data-td-lightbox]');if(!link)return;e.preventDefault();
    const scope=link.closest('.td-gallery-showcase')||document;
    const links=$$('[data-td-lightbox]',scope);
    let index=Math.max(0,links.indexOf(link));
    const overlay=document.createElement('div');overlay.className='td-lightbox-overlay';overlay.setAttribute('role','dialog');overlay.setAttribute('aria-modal','true');
    const close=document.createElement('button');close.type='button';close.className='td-lightbox-close';close.setAttribute('aria-label','Close');close.innerHTML='×';
    const prev=document.createElement('button');prev.type='button';prev.className='td-lightbox-nav td-lightbox-prev';prev.setAttribute('aria-label','Previous image');prev.innerHTML='‹';
    const next=document.createElement('button');next.type='button';next.className='td-lightbox-nav td-lightbox-next';next.setAttribute('aria-label','Next image');next.innerHTML='›';
    const frame=document.createElement('figure');frame.className='td-lightbox-frame';
    const img=document.createElement('img');const cap=document.createElement('figcaption');
    function show(i){index=(i+links.length)%links.length;const current=links[index];img.src=current.href;img.alt=current.querySelector('img')?.alt||'';cap.textContent=(current.querySelector('.td-gallery-caption strong')?.textContent||img.alt||'')+'  '+(index+1)+'/'+links.length;prev.hidden=next.hidden=links.length<2;}
    frame.appendChild(img);frame.appendChild(cap);overlay.append(close,prev,frame,next);
    const done=()=>{overlay.remove();document.body.classList.remove('td-lightbox-open');document.removeEventListener('keydown',onKey);};
    function onKey(ev){if(ev.key==='Escape')done();if(ev.key==='ArrowLeft')show(index-1);if(ev.key==='ArrowRight')show(index+1);}
    close.addEventListener('click',done);prev.addEventListener('click',()=>show(index-1));next.addEventListener('click',()=>show(index+1));overlay.addEventListener('click',ev=>{if(ev.target===overlay)done();});document.addEventListener('keydown',onKey);
    show(index);document.body.classList.add('td-lightbox-open');document.body.appendChild(overlay);
  });
  const searchToggle=$('#tdSearchToggle');const searchPanel=$('#tdSearchPanel');function setSearch(open){if(!searchToggle||!searchPanel)return;searchPanel.classList.toggle('is-open',open);searchToggle.setAttribute('aria-expanded',open?'true':'false');const input=$('.td-site-search-input',searchPanel);if(open&&input)setTimeout(()=>input.focus(),30);}if(searchToggle&&searchPanel){searchToggle.addEventListener('click',e=>{e.preventDefault();setSearch(!searchPanel.classList.contains('is-open'));});document.addEventListener('click',e=>{if(!e.target.closest('.td-search-panel,.td-search-toggle'))setSearch(false);});}
  function esc(s){return String(s||'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));}
  function renderSearch(form,items,state){const box=$('.td-site-search-results',form);if(!box)return;if(state){box.innerHTML='<div class="td-search-state">'+esc(state)+'</div>';return;}if(!items||!items.length){box.innerHTML='<div class="td-search-state">'+esc(theme.labels?.noResults||'Nekas netika atrasts.')+'</div>';return;}box.innerHTML=items.map(item=>'<a class="td-search-result" href="'+esc(item.url)+'"><span>'+esc(item.type||'Rezultāts')+'</span><strong>'+esc(item.title)+'</strong><small>'+esc(item.excerpt||'')+'</small></a>').join('');}
  function initSearch(form){const input=$('.td-site-search-input',form);if(!input)return;let timer=null,controller=null;function local(q){const n=q.toLowerCase();return(theme.localIndex||[]).filter(i=>(Object.values(i).join(' ').toLowerCase().includes(n))).slice(0,8);}function doSearch(){const q=input.value.trim();if(q.length<2){renderSearch(form,[],theme.labels?.minChars||'Ievadiet vismaz 2 simbolus.');return;}renderSearch(form,local(q),theme.labels?.searching||'Meklē...');if(controller)controller.abort();controller=new AbortController();const params=new URLSearchParams({action:'thermodul_search',nonce:theme.searchNonce||'',s:q});fetch((theme.ajaxUrl||'/wp-admin/admin-ajax.php')+'?'+params.toString(),{signal:controller.signal,credentials:'same-origin'}).then(r=>r.json()).then(data=>{const remote=(data&&data.success&&data.data&&data.data.items)||[];const merged=[...local(q),...remote];const seen=new Set();const out=[];merged.forEach(i=>{const k=(i.url||'')+'|'+(i.title||'');if(!seen.has(k)){seen.add(k);out.push(i);}});renderSearch(form,out.slice(0,12));}).catch(()=>renderSearch(form,local(q)));}input.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(doSearch,250);});form.addEventListener('submit',e=>{e.preventDefault();doSearch();});}
  $$('.td-ajax-search').forEach(initSearch);
  document.addEventListener('submit',function(e){const form=e.target.closest('[data-td-ajax-form]');if(!form)return;e.preventDefault();const status=$('.td-form-status',form);const fd=new FormData(form);if(theme.hcaptchaEnabled&&!fd.get('h-captcha-response')){if(status){status.textContent=theme.labels?.captchaRequired||'Lūdzu, apstipriniet hCaptcha.';status.classList.add('is-error');status.classList.remove('is-success');}loadHcaptcha(form);return;}if(status){status.textContent=theme.labels?.loading||'Ielādē...';status.classList.remove('is-error','is-success');}fd.set('action','thermodul_contact_submit');fetch(theme.ajaxUrl||'/wp-admin/admin-ajax.php',{method:'POST',body:fd,credentials:'same-origin'}).then(r=>r.json()).then(data=>{if(status){status.textContent=(data&&data.data&&data.data.message)||((data&&data.success)?'Paldies!':'Kļūda');status.classList.toggle('is-success',!!data.success);status.classList.toggle('is-error',!data.success);}if(data&&data.success)form.reset();resetHcaptcha(form);}).catch(()=>{if(status){status.textContent=theme.labels?.formError||'Kļūda';status.classList.add('is-error');}resetHcaptcha(form);});});
  $$('.td-gallery-showcase').forEach(showcase=>{if(showcase.dataset.ajaxReady)return;showcase.dataset.ajaxReady='1';const grid=$('[data-ajax-gallery]',showcase);if(!grid)return;const total=parseInt(showcase.dataset.total||String($$('.td-gallery-item',showcase).length),10)||$$('.td-gallery-item',showcase).length;let offset=$$('.td-gallery-item',showcase).length;const step=parseInt(showcase.dataset.step||'12',10)||12;const group=showcase.dataset.group||'';const btnLabel=showcase.dataset.buttonLabel||'Ielādēt vēl attēlus';if(offset>=total)return;const btn=document.createElement('button');btn.type='button';btn.className='td-btn td-btn-outline td-load-gallery';btn.textContent=btnLabel;showcase.appendChild(btn);btn.addEventListener('click',()=>{btn.disabled=true;btn.textContent=theme.labels?.loading||'Ielādē...';const params=new URLSearchParams({action:'thermodul_gallery',nonce:theme.galleryNonce||'',offset:String(offset),limit:String(step)});if(group)params.set('group',group);fetch((theme.ajaxUrl||'/wp-admin/admin-ajax.php')+'?'+params.toString(),{credentials:'same-origin'}).then(r=>r.json()).then(data=>{if(data&&data.success){const frag=document.createElement('div');frag.innerHTML=data.data.html||'';Array.from(frag.children).forEach(ch=>{ch.classList.add('is-appended');grid.appendChild(ch);requestAnimationFrame(()=>ch.classList.remove('is-appended'));});offset=data.data.next||offset;initLazy(grid);if(data.data.done||offset>=total)btn.remove();else{btn.disabled=false;btn.textContent=btnLabel;}}else{btn.remove();}}).catch(()=>btn.remove());});});

  function openContentModal(opts){
    const old=document.querySelector('.td-modal-overlay'); if(old)old.remove();
    const overlay=document.createElement('div'); overlay.className='td-modal-overlay'; overlay.setAttribute('role','dialog'); overlay.setAttribute('aria-modal','true');
    const card=document.createElement('div'); card.className='td-modal-card'+(opts.variant?' td-modal-card--'+opts.variant:'');
    const close=document.createElement('button'); close.className='td-modal-close'; close.type='button'; close.setAttribute('aria-label','Close'); close.innerHTML='×';
    const image=opts.image?'<div class="td-modal-media"><img src="'+esc(opts.image)+'" alt="'+esc(opts.title||'')+'"></div>':'';
    const shareUrl=encodeURIComponent(opts.url||location.href.split('#')[0]);
    const shareText=encodeURIComponent(opts.title||document.title);
    const share=opts.share?'<div class="td-modal-share"><span>Share</span><a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u='+shareUrl+'">f</a><a target="_blank" rel="noopener" href="https://www.linkedin.com/shareArticle?mini=true&url='+shareUrl+'&title='+shareText+'">in</a><a href="mailto:?subject='+shareText+'&body='+shareUrl+'">@</a></div>':'';
    card.innerHTML=image+'<div class="td-modal-copy"><h2>'+esc(opts.title||'')+'</h2><p>'+esc(opts.body||'')+'</p>'+share+'</div>';
    card.appendChild(close); overlay.appendChild(card); document.body.appendChild(overlay); document.body.classList.add('td-modal-open');
    const done=()=>{overlay.remove();document.body.classList.remove('td-modal-open');};
    close.addEventListener('click',done); overlay.addEventListener('click',e=>{if(e.target===overlay)done();});
    document.addEventListener('keydown',function onKey(e){if(e.key==='Escape'){done();document.removeEventListener('keydown',onKey);}});
  }
  document.addEventListener('click',function(e){
    const post=e.target.closest('.td-post-trigger'); if(!post)return;
    e.preventDefault();
    openContentModal({title:post.dataset.postTitle||post.dataset.modalTitle,body:post.dataset.postBody||post.dataset.modalBody,image:post.dataset.postImage||'',url:post.getAttribute('href')||location.href,share:true,variant:'post'});
  });
  document.addEventListener('click',function(e){
    const card=e.target.closest('.td-open-card'); if(!card)return;
    e.preventDefault();
    openContentModal({title:card.dataset.modalTitle||'',body:card.dataset.modalBody||'',image:card.dataset.modalImage||'',variant:'model'});
  });

  const pathLang=(location.pathname.match(/^\/(en|ru|lt|et)(\/|$)/)||[,new URLSearchParams(location.search).get('lang')||'lv'])[1]||'lv';$$('.td-lang-switch a').forEach(a=>{if(a.dataset.lang===pathLang)a.classList.add('is-active');});
})();
