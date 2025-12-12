/* Supreme Heir Inflatables front-end logic */
(function(){
  // Get the BASE_URL and BASE_PATH from global scope if set (via server-side templating), otherwise default to current location

  const { BASE_URL = '', BASE_PATH='' } = window.APP_CONFIG || {};


  /* THEME PALETTE (edit these 5 values) */
  /* DATA: Rentals catalog */
  // const ITEMS = [
  //   { id:'item-castle-royal',    name:'Royal Bounce Castle',   type:'castle',   wet:'dry', price:185, file:'royalbouncecastle' },
  //   { id:'item-slide-majestic',  name:'Majestic Water Slide',  type:'slide',    wet:'wet', price:240, file:'majesticwaterslide' },
  //   { id:'item-obstacle-crown',  name:'Crown Obstacles Course', type:'obstacle', wet:'dry', price:260, file:'crownobstaclecourse' },
  //   { id:'item-castle-sapphire', name:'Sapphire Splash Castle',type:'castle',   wet:'wet', price:205, file:'sapphiresplashcastle' },
  // ];
  // const CAROUSEL_IDS = ['item-castle-royal','item-slide-majestic','item-obstacle-crown'];

  // function pictureMarkup(fileOrName, page){
  //   const base = page==='home' ? 'assets/images/' : '../assets/images/';
  //   let fname = (fileOrName||'').toString();
  //   if (fname.includes('.')) fname = fname.split('.')[0];
  //   fname = fname.toLowerCase().replace(/[^a-z]/g,''); // letters only
  //   const src = `${base}${fname}.png`;
  //   const alt = (fname||'inflatable').replace(/([a-z])([A-Z])/g,'$1 $2');
  //   return `<img src="${src}" alt="${alt}">`;
  // }

  // function renderCarousel(){
  //   const track = document.getElementById('carouselTrack');
  //   if (!track) return;
  //   track.innerHTML = '';
  //   CAROUSEL_IDS.map(id => ITEMS.find(i=>i.id===id)).filter(Boolean).forEach(item=>{
  //     const a = document.createElement('a');
  //     a.className = 'carousel-card';
  //     a.href = `pages/rentals.html#${item.id}`;
  //     a.dataset.jumpid = item.id;
  //     a.title = `See ${item.name}`;
  //     a.innerHTML = pictureMarkup(item.file || item.name, 'home') + `<span class="carousel-caption">${item.name}</span>`;
  //     track.appendChild(a);
  //   });
  // }

  // function renderRentalsGrid(){
  //   const grid = document.getElementById('rentalsGrid');
  //   if (!grid) return;
  //   grid.innerHTML = '';
  //   ITEMS.forEach(it => {
  //     const art = document.createElement('article');
  //     art.className = 'rental-card';
  //     art.id = it.id;
  //     art.dataset.type = it.type;
  //     art.dataset.wet = it.wet;
  //     art.dataset.price = String(it.price);
  //     art.dataset.name = it.name;
  //     art.innerHTML = `
  //       <div class="rental-media">
  //         ${pictureMarkup(it.file || it.name, 'rentals')}
  //       </div>
  //       <div class="rental-info">
  //         <h2>${it.name}</h2>
  //         <p class="price" data-price>$${it.price} / day</p>
  //         <p class="tags">Type: ${it.type.charAt(0).toUpperCase()+it.type.slice(1)} · ${it.wet.charAt(0).toUpperCase()+it.wet.slice(1)}</p>
  //         <button class="btn btn-secondary" data-details>Details</button>
  //       </div>`;
  //     grid.appendChild(art);
  //   });
  // }

  const THEME = {
    vermilion: '#E6493B',
    blue:      '#4D88E7',
    yellow:    '#FDD96D',
    coffee:    '#6A0DAD',
    vanDyke:   '#6A0DAD'
  };
  function applyTheme(vars){
    const r = document.documentElement;
    r.style.setProperty('--c-vermilion', vars.vermilion);
    r.style.setProperty('--c-blue', vars.blue);
    r.style.setProperty('--c-yellow', vars.yellow);
    r.style.setProperty('--c-coffee', vars.coffee);
    r.style.setProperty('--c-van-dyke', vars.vanDyke);
    r.style.setProperty('--bg', vars.vanDyke);
    r.style.setProperty('--bg-elev', vars.coffee);
    r.style.setProperty('--primary', vars.blue);
    r.style.setProperty('--accent', vars.yellow);
    r.style.setProperty('--danger', vars.vermilion);
  }
  document.addEventListener('DOMContentLoaded', ()=> applyTheme(THEME));

  const qs = (s,root=document)=>root.querySelector(s);
  const qsa = (s,root=document)=>[...root.querySelectorAll(s)];
  const byId = id => document.getElementById(id);

  qsa('[data-year]').forEach(n => n.textContent = String(new Date().getFullYear()));

  // Change navigation to mobile and back
  const nav = qs('.site-nav');
  const toggle = qs('.nav-toggle');
  if (toggle && nav){
    toggle.addEventListener('click', ()=>{
      const expanded = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', String(!expanded));
      nav.setAttribute('aria-expanded', String(!expanded));
    });
  }

  const backdrop = qs('[data-backdrop]');
  const modal = qs('[data-modal]');
  const modalContent = qs('[data-modal-content]');
  const closeBtn = qs('[data-modal-close]');

  function openModal(html){
    if (!modal || !backdrop || !modalContent) return;
    // set modal content
    modalContent.innerHTML = html;
    // ensure a close button exists inside the modal content; create and attach if missing
    let btn = modalContent.querySelector('[data-modal-close]');
    if (!btn){
      btn = document.createElement('button');
      btn.className = 'modal-close';
      btn.setAttribute('data-modal-close', '');
      btn.setAttribute('aria-label', 'Close');
      btn.innerHTML = '×';
      modalContent.prepend(btn);
    }
    // attach click listener (idempotent)
    try { btn.removeEventListener('click', closeModal); } catch(e){}
    btn.addEventListener('click', closeModal);
    backdrop.hidden = false; modal.hidden = false;
    document.documentElement.style.overflow = 'hidden';
  }
  function closeModal(){
    if (!modal || !backdrop) return;
    backdrop.hidden = true; modal.hidden = true;
    modalContent.innerHTML = '';
    document.documentElement.style.overflow = '';
  }
  backdrop?.addEventListener('click', closeModal);
  closeBtn?.addEventListener('click', closeModal);
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeModal(); });
  if (backdrop) backdrop.hidden = true;
  if (modal) modal.hidden = true;

  if (document.body.dataset.page === 'home'){
    renderCarousel();
    const track = qs('.carousel-track');
    const enlarge = qs('.carousel-enlarge');
    let idx = 0, timer = null;
    const cards = qsa('.carousel-card', track);

    function goNext(){
      idx = (idx + 1) % cards.length;
      cards[idx].scrollIntoView({behavior:'smooth', inline:'center', block:'nearest'});
    }
    function start(){
      if (track?.dataset.autoplay === 'true'){
        timer = setInterval(goNext, Number(track.dataset.interval||3500));
      }
    }
    function stop(){ if(timer) clearInterval(timer); timer = null; }
    start();

    cards.forEach(card=>{
      const img = qs('img', card);
      card.addEventListener('mouseenter', ()=>{
        stop();
        if (enlarge){
          enlarge.style.display = 'grid';
          qs('img', enlarge).src = img.src;
          qs('img', enlarge).alt = img.alt;
        }
      });
      card.addEventListener('mouseleave', ()=>{
        if (enlarge){ enlarge.style.display = 'none'; }
        start();
      });
      card.addEventListener('click', ()=>{
        const id = card.getAttribute('data-jumpid');
        sessionStorage.setItem('jumpHighlightId', id);
      });
    });
  }

  if (document.body.dataset.page === 'rentals'){
    // Do NOT call this here anymore! renderRentalsGrid();
    const grid = byId('rentalsGrid');
    const filterType = byId('filterType');
    const filterWet = byId('filterWet');
    const sortPrice = byId('sortPrice');
    const cards = qsa('.rental-card', grid);

    const hoverLayer = qs('[data-hover-enlarge]');
    function hideHover(){ if (hoverLayer){ hoverLayer.hidden = true; document.documentElement.style.overflow=''; } }
    function showHover(src, alt){
      if (!hoverLayer) return;
      hoverLayer.hidden = false;
      qs('img', hoverLayer).src = src;
      qs('img', hoverLayer).alt = alt || '';
      document.documentElement.style.overflow='hidden';
    }
    hideHover();
    qsa('.rental-media img').forEach(img=>{
      let t;
      img.addEventListener('mouseenter', ()=>{ t=setTimeout(()=>showHover(img.src,img.alt),100); });
      img.addEventListener('mouseleave', ()=>{ clearTimeout(t); });
    });
    hoverLayer?.addEventListener('click', hideHover);
    document.addEventListener('keydown', (e)=>{ if (e.key==='Escape') hideHover(); });

    function applyFilter(){
      const t = filterType.value;
      const w = filterWet.value;
      cards.forEach(c=>{
        const okT = !t || c.dataset.type === t;
        const okW = !w || c.dataset.wet === w;
        c.style.display = (okT && okW) ? '' : 'none';
      });
    }
    filterType?.addEventListener('change', applyFilter);
    filterWet?.addEventListener('change', applyFilter);

    function sortCards(dir){
      const visible = cards.filter(c=>c.style.display !== 'none');
      const parent = visible[0]?.parentElement || grid;
      const sorted = [...visible].sort((a,b)=>{
        const pa = Number(a.dataset.price), pb = Number(b.dataset.price);
        return dir === 'desc' ? pb - pa : pa - pb;
      });
      sorted.forEach(card=> parent.appendChild(card));
    }
    sortPrice?.addEventListener('change', ()=>{
      if (!sortPrice.value) return;
      sortCards(sortPrice.value);
    });

    function detailsHtml(card){
      const name = card.dataset.name;
      const price = Number(card.dataset.price);
      const imgSrc = qs('img', card).src;
      return `
        <div class="detail-head">
          <img src="${imgSrc}" alt="${name}">
          <div>
            <h2 id="modalTitle">${name}</h2>
            <p class="muted">Type: ${card.dataset.type} · ${card.dataset.wet.toUpperCase()}</p>
            <p><strong>$${price}</strong> per day (tax included)</p>
          </div>
        </div>
        <div class="detail-body">
          <p>Dimensions, capacity, and safety details can be added here later.</p>
        </div>
        <div class="detail-cta">
          <button class="btn btn-primary" data-add-reservation>Add Reservation to Cart</button>
        </div>
      `;
    }


    function reservationFormHtml(item){
      const name = item.dataset.name;
      const imgSrc = qs('img', item).src;
      return `
        <h2 id="modalTitle">Reserve: ${name}</h2>
        <div class="detail-head">
          <img src="${imgSrc}" alt="${name}">
          <div><p class="muted">We serve the State Panhandle only</p></div>
        </div>
        <form class="res-form" id="resForm" novalidate>
          <div class="grid">
            <label>Street Address<input name="street1" required></label>
            <label>City<input name="city" required></label>
            <label>Zipcode<input name="zip" ></label>
            <label>Start Date<input name="startDate" type="date" required></label>
            <label>End Date<input name="endDate" type="date" required></label>
          </div>
          <div class="detail-cta">
            <button class="btn btn-secondary check-availability" type="button" data-check-dates>Check Date Availability</button>
            <div class="actions">
              <button class="btn btn-primary" type="button" data-checkout-now>Check Out Now</button>
              <button class="btn btn-secondary" type="button" data-add-to-cart>Add to Cart</button>
            </div>
          </div>
        </form>
      `;
    }

    grid.addEventListener('click', (e)=>{
      const detailsBtn = e.target.closest('[data-details]');
      const card = e.target.closest('.rental-card');
      if (detailsBtn && card){
        openModal( detailsHtml(card) );
        // When user clicks "Add Reservation to Cart" from the details view,
        // add the item immediately with minimal/default reservation data.
        qs('[data-add-reservation]', modal)?.addEventListener('click', ()=>{
          const item = {
            id: card.id,
            name: card.dataset.name,
            price: Number(card.dataset.price),
            type: card.dataset.type,
            wet: card.dataset.wet,
            // no dates chosen yet; leave empty so user can edit later in cart
            startDate: '',
            endDate: '',
            street1: '', street2: '', apt: '', city: '', zip: '',
            thumb: qs('img', card)?.src
          };
          addToCart({ item });
          alert('Added to cart!');
          closeModal();
        });
      }
    });

    // Note: I THINK these following few constatns and functions IS WHERE THE IMG DOESN'T TRANSFER. IT SHOULD BE FIXABLE EASILY ONCE THE DATA FOR THE INVENTORY ITEMS (including the ID) MOVE WITH THE CHECKOUT FUNCTION. 
    const hashId = window.location.hash?.slice(1) || sessionStorage.getItem('jumpHighlightId');
    if (hashId){
      const el = byId(hashId);
      if (el){
        el.scrollIntoView({behavior:'smooth', block:'center'});
        el.classList.add('highlight');
        setTimeout(()=> el.classList.remove('highlight'), 3000);
      }
      sessionStorage.removeItem('jumpHighlightId');
    }

    function gatherReservation(form, card){
      if (!form.checkValidity()){
        form.reportValidity();
        return null;
      }
      const fd = new FormData(form);
      const startDate = fd.get('startDate')?.toString();
      const endDate = fd.get('endDate')?.toString();
 ////////// HERE, I think, is where some stuff needs to change for making the checkout functional AND, in doing so, the images could be made visible in the cart page.
     const item = {
        id: card.id,
        name: card.dataset.name,
        price: Number(card.dataset.price),
        type: card.dataset.type,
        wet: card.dataset.wet,
        startDate, endDate,
        // changed 'street1' to 'address' and removed street2
        address: fd.get('address')?.toString().trim(),
        apt: fd.get('apt')?.toString().trim(),
        city: fd.get('city')?.toString().trim(),
        zip: fd.get('zip')?.toString().trim(),
        thumb: qs('img', card)?.src
      };
      return { item };
    }
  }

  if (document.body.dataset.page === 'cart'){
  const cartListEl = byId('cartList');
  const list = byId('cartItems');
    const totalEl = byId('grandTotal');
    const cartDatesSection = byId('cartDates');
    const cartStartInput = byId('cartStartDate');
    const cartEndInput = byId('cartEndDate');
    const cartDatesPreview = byId('cartDatesPreview');

    function formatDateToMDY(d){
      // expect d in YYYY-MM-DD (HTML date input value) or falsy
      if (!d) return '—';
      try{
        const parts = d.split('-');
        if (parts.length !== 3) return d;
        const [y,m,day] = parts;
        return `${m}/${day}/${y}`;
      }catch{ return d; }
    }

    function updateDatesPreview(start, end){
      if (!cartDatesPreview) return;
      if (!start && !end){ cartDatesPreview.textContent = 'Selected: —'; return; }
      const s = start ? formatDateToMDY(start) : '—';
      const e = end ? formatDateToMDY(end) : '—';
      cartDatesPreview.textContent = `Selected: ${s} → ${e}`;
    }

    function syncCartDatesToUI(items){
      if (!cartDatesSection) return;
      if (!items.length){
        cartDatesSection.style.display = 'none';
        if (cartStartInput) cartStartInput.value = '';
        if (cartEndInput) cartEndInput.value = '';
        updateDatesPreview('', '');
        return;
      }
      cartDatesSection.style.display = '';
      // Note: I think 'personal' might be a holdover from when items could be reserved on different dates in one order
      const personal = getCart().personal || {};
      // prefer personal dates, otherwise fall back to first item's dates
      const start = personal.startDate || items[0].startDate || '';
      const end = personal.endDate || items[0].endDate || '';
      if (cartStartInput) cartStartInput.value = start;
      if (cartEndInput) cartEndInput.value = end;
      updateDatesPreview(start, end);
    }

    // When cart-level date inputs change, update personal defaults and all item dates, then re-render
    cartStartInput?.addEventListener('change', ()=>{
      const s = cartStartInput.value || '';
      const cur = getCart();
      const personal = { ...(cur.personal||{}), startDate: s };
      // apply start date to all items
      const updated = cur.items.map(it => ({ ...it, startDate: s }));
      setCart({ items: updated, personal });
      updateDatesPreview(cartEndInput?.value || '', s);
      render();
    });
    cartEndInput?.addEventListener('change', ()=>{
      const e = cartEndInput.value || '';
      const cur = getCart();
      const personal = { ...(cur.personal||{}), endDate: e };
      // apply end date to all items
      const updated = cur.items.map(it => ({ ...it, endDate: e }));
      setCart({ items: updated, personal });
      updateDatesPreview(cartStartInput?.value || '', e);
      render();
    });

    function render(){
      const {items} = getCart();
      // show/hide cart-level date inputs and set their values
      syncCartDatesToUI(items);
      list.innerHTML = '';
      if (!items.length){
        // hide dates when empty
        if (cartDatesSection) cartDatesSection.style.display = 'none';

// Browse Rentals Cart Adjustment
// Note: Remove browseHref 

        //const browseHref = `${BASE_URL || ''}/../index.php?page=rentals`;
        const browseHref = `${BASE_URL || ''}/index.php?page=rentals`;
        // create the empty-card via DOM so we can apply inline styles (prevents external CSS hover rules collapsing it)
        const emptyCard = document.createElement('div');
        emptyCard.className = 'card';
        emptyCard.innerHTML = `<p>Your cart is empty.</p><p><button class="btn btn-primary" data-browse type="button">Browse Rentals</button></p>`;
        // lock spacing via inline styles to avoid layout shifts from stylesheet hover rules
        emptyCard.style.boxSizing = 'border-box';
        emptyCard.style.paddingTop = '20px';
        emptyCard.style.paddingBottom = '20px';
        emptyCard.style.marginTop = '20px';
        emptyCard.style.marginBottom = '20px';
        emptyCard.style.minHeight = '114px';
        list.appendChild(emptyCard);

      } else {
        for (const it of items){
          const el = document.createElement('div');
          el.className = 'cart-item';
          el.dataset.id = it.id + '::' + it.startDate + '::' + it.endDate;
          const qty = Number(it.quantity || 1);
          const days = dayCountInclusive(it.startDate, it.endDate);
          const itemTotal = (Number(it.price||0) * days * qty).toFixed(2);
          el.innerHTML = `
            <div class="thumb"><img src="${it.thumb}" alt="${it.name}"></div>
            <div class="meta">
              <h3>${it.name}</h3>
              <div class="line">
                <span>Type: ${it.type}</span>
                <span>${it.wet.toUpperCase()}</span>
                <span>Price: $${it.price}/day</span>
              </div>
              <div class="line">
                <label>Quantity <input type="number" min="1" value="${qty}" data-quantity aria-label="Quantity for ${it.name}"></label>
              </div>
              <div class="line"><strong>Item Total: $${itemTotal}</strong></div>
              <div class="actions">
                <button class="remove" data-remove><span aria-hidden="true">✖  </span><span class="sr-only">  Remove</span></button>
              </div>
            </div>
          `;
          list.appendChild(el);
        }
      }
      const grand = getCart().items.reduce((s,i)=>{
        const days = dayCountInclusive(i.startDate, i.endDate);
        const qty = Number(i.quantity || 1);
        return s + (Number(i.price||0) * days * qty);
      }, 0).toFixed(2);
      totalEl.textContent = `$${grand}`;
    }

    list.addEventListener('click', (e)=>{
      const itemEl = e.target.closest('.cart-item');
      if (!itemEl) return;
      const key = itemEl.dataset.id;
      const {items} = getCart();
      const it = items.find(x => (x.id+'::'+x.startDate+'::'+x.endDate) === key);
      if (!it) return;

      if (e.target.closest('[data-view]')){
        openModal(`
          <h2 id="modalTitle">${it.name}</h2>
          <p class="muted">${it.type} · ${it.wet.toUpperCase()}</p>
          <p>$${it.price}/day (tax included)</p>
          <p>Address: ${it.street1||''} ${it.street2||''} ${it.apt||''}, ${it.city||''} ${it.zip||''}</p>
          <p>Start: ${it.startDate || '—'}</p>
          <p>End: ${it.endDate || '—'}</p>
        `);
      }
      // changed street1 to address
      if (e.target.closest('[data-edit]')){
        openModal(`
          <h2 id="modalTitle">Edit Reservation</h2>
          <form id="editForm" class="res-form">
            <div class="grid">
              <label>Street Address<input name="address" value="${it.address ||''}" required></label>
              <label>City<input name="city" value="${it.city||''}" required></label>
              <label>Zipcode<input name="zip" value="${it.zip||''}" required pattern="\\d{5}"></label>
              <label>Start Date<input name="startDate" type="date" value="${it.startDate||''}" required></label>
              <label>End Date<input name="endDate" type="date" value="${it.endDate||''}" required></label>
            </div>
            <div class="actions">
              <button class="btn btn-primary" type="submit">Save</button>
            </div>
          </form>
        `);
        // changed street1 to address and removed street 2 address
        qs('#editForm')?.addEventListener('submit', (ev)=>{
          ev.preventDefault();
          const fd = new FormData(ev.currentTarget);
          it.street1 = fd.get('address')?.toString().trim();
          it.apt = fd.get('apt')?.toString().trim();
          it.city = fd.get('city')?.toString().trim();
          it.zip = fd.get('zip')?.toString().trim();
          it.startDate = fd.get('startDate')?.toString();
          it.endDate = fd.get('endDate')?.toString();
          setCart({ items });
          closeModal();
          render();
        });
      }
      if (e.target.closest('[data-remove]')){
        openModal(`
          <h2 id="modalTitle">Remove item?</h2>
          <p>Are you sure you want to remove <strong>${it.name}</strong> from your cart?</p>
          <div class="detail-cta">
            <button class="btn btn-danger" data-confirm-remove>Remove from cart</button>
            <button class="btn btn-secondary" data-cancel-remove>Leave in cart</button>
          </div>
        `);
        qs('[data-cancel-remove]')?.addEventListener('click', closeModal);
        qs('[data-confirm-remove]')?.addEventListener('click', ()=>{
          const next = items.filter(x => x !== it);
          setCart({ items: next });
          closeModal();
          render();
        });
      }
    });

    // handle quantity changes
    list.addEventListener('change', (e)=>{
      if (!e.target.matches('[data-quantity]')) return;
      const input = e.target;
      const itemEl = input.closest('.cart-item');
      if (!itemEl) return;
      const key = itemEl.dataset.id;
      const cur = getCart();
      const it = cur.items.find(x => (x.id+'::'+x.startDate+'::'+x.endDate) === key);
      if (!it) return;
      const val = Math.max(1, parseInt(input.value || '1', 10));
      it.quantity = val;
      setCart({ items: cur.items });
      render();
    });

    // Change from merging
    // Note: Remove the browseHref
    // Delegated handler for the empty-cart 'Browse Rentals' button
    list.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-browse]');
      if (!btn) return;
      //const browseHref = `${window.BASE_URL || ''}/../index.php?page=rentals`;
      const browseHref = `${window.BASE_URL || ''}/index.php?page=rentals`

      // navigate via JS to avoid any native anchor focus/scroll quirks
      window.location.href = browseHref;
    });


    byId('btnCheckoutCart')?.addEventListener('click', ()=>{
      if (!getCart().items.length){
        alert('Your cart is empty.');
        return;
      }
      beginCheckoutFlow();
    });

    render();
  }

  function getCart(){
    const raw = localStorage.getItem('shi_cart');
    if (!raw) return { items: [], personal: null };
    try { const obj = JSON.parse(raw); return { items: obj.items || [], personal: obj.personal || null }; }
    catch { return { items: [], personal: null }; }
  }
  function setCart({items, personal}){
    const cur = getCart();
    const next = { items: items!==undefined? items: cur.items, personal: personal!==undefined? personal: cur.personal };
    localStorage.setItem('shi_cart', JSON.stringify(next));
    return next;
  }
  function dayCountInclusive(start, end){
    try{
      const s = new Date(start), e = new Date(end);
      if (isNaN(s) || isNaN(e)) return 1;
      const ms = e.setHours(0,0,0,0) - s.setHours(0,0,0,0);
      const days = Math.floor(ms / 86400000) + 1;
      // days - 1 ensure that overnight is equal to just one day.
      return Math.max(1, days - 1);
    }catch{ return 1; }
  }

  function addToCart(payload){
    const cur = getCart();
    if (payload.item){
      if (payload.item.quantity === undefined) payload.item.quantity = 1;
      // If an identical reservation (same id and same dates) exists, increment its quantity
      const existing = cur.items.find(x => x.id === payload.item.id && (x.startDate||'') === (payload.item.startDate||'') && (x.endDate||'') === (payload.item.endDate||''));
      if (existing){
        existing.quantity = Number(existing.quantity || 1) + Number(payload.item.quantity || 1);
      } else {
        cur.items.push(payload.item);
      }
    }
    setCart({ items: cur.items });
  }

//#region Personal Info

  // Checkout sequence helpers
  function promptPersonalInfo(next){
    const existing = getCart().personal || {};
    // When checking out, collect personal info plus a default address and start/end dates
    openModal(`
      <h2 id="modalTitle">Contact & Reservation Defaults</h2>
      <form id="personalForm" class="res-form" method= "POST" action="index.php?page=cart">
        <div class="grid">
          <!--Since dates are in a different form, these hidden dates will get the values from that form, and insert them into these fields-->
          <input type="hidden" name="startDate" id="modalStartDate">
          <input type="hidden" name="endDate" id="modalEndDate">

          <!--Hidden field that will hold all the info of the cart-->
          <input type="hidden" name="cart_json" id="cart_json">

          <label>First Name<input id="firstName" type="text" name="firstName" value="${existing.firstName||''}"></label>
          <label>Last Name<input id="lastName" type="text" name="lastName" value="${existing.lastName||''}"></label>
          <label>Phone<input id="phone" type="text" name="phone" value="${existing.phone||''}"></label>
          <label>Email<input id="email" type="text" name="email" value="${existing.email||''}"></label>
          <label>Street Address<input id="address" type="text" name="address" value="${existing.street1||''}"></label>
          <label>City<input id="city" type="text" name="city" value="${existing.city||''}"></label>
          <label>Zipcode<input id="zip" type="text" name="zip" value="${existing.zip||''}"></label>

          <!-- Dates moved to cart top so they can update totals live -->
        </div>
        <div class="actions">
          <button class="btn btn-primary" type="submit" name="continue" value="1">Continue</button>
        </div>
      </form>
    `);
    qs('#personalForm')?.addEventListener('submit', (e) => {
        //This will get the dates from the dates form and copy them to the personal Info form
        document.getElementById('modalStartDate').value =
        document.getElementById('cartStartDate').value;

        document.getElementById('modalEndDate').value =
        document.getElementById('cartEndDate').value;

        //Copy cart items
        const cart = getCart();
        document.getElementById('cart_json').value = JSON.stringify(cart.items);
    });


}

//#endregion

  // changed street1 to address and removed street2
    function showFinalConfirm(){
    const {items, personal} = getCart();
      const list = items.map(i => `<li><strong>${i.name}</strong> — $${i.price}/day × ${dayCountInclusive(i.startDate,i.endDate)} days × ${i.quantity||1}<br>Address: ${i.address||''} ${i.apt||''}, ${i.city||''} ${i.zip||''}<br>Dates: ${i.startDate||'—'} → ${i.endDate||'—'}</li>`).join('');
      const total = items.reduce((s,i)=> s + (Number(i.price||0) * dayCountInclusive(i.startDate,i.endDate) * (Number(i.quantity||1))),0).toFixed(2);
    openModal(`
      <h2 id="modalTitle">Review & Confirm</h2>
      <p class="muted">Please confirm all details before finalizing your reservation.</p>
      <h3>Contact</h3>
      <p>${personal?.firstName||''} ${personal?.lastName||''} · ${personal?.phone||''} · ${personal?.email||''}</p>
      <h3>Items</h3>
      <ul>${list}</ul>
      <h3>Total</h3>
      <p><strong>$${total}</strong> (tax included)</p>
      <p class="muted">Payment is in person on delivery day, full amount up front.</p>
      <div class="detail-cta">
        <button class="btn btn-primary" data-finalize>Confirm Reservation</button>
        <button class="btn btn-secondary" data-cancel>Cancel</button>
      </div>
    `);
    qs('[data-cancel]')?.addEventListener('click', closeModal);
    // Original data finalize
    qs('[data-finalize]')?.addEventListener('click', ()=>{
      alert('Reservation submitted! (Server integration pending)');
        setCart({ items: [], personal: null });
      closeModal();
      if (document.body.dataset.page === 'cart'){
        const list = byId('cartList');
        const totalEl = byId('grandTotal');
          const cartItems = list.querySelector('#cartItems');
          const cartDatesSection = byId('cartDates');
         // if (cartItems){ cartItems.innerHTML = `<div class="card"><p>Your cart is empty.</p><p><a class="btn btn-primary" href="${BASE_URL || ''}/../index.php?page=rentals#">Browse Rentals</a></p></div>`; }
         if (cartItems){cartItems.innerHTML = `<div class="card"><p>Your cart is empty.</p><p><a class="btn btn-primary" href="${BASE_URL || ''}/index.php?page=rentals#">Browse Rentals</a></p></div>`;}

         if (cartDatesSection) cartDatesSection.style.display = 'none';
        if (totalEl){ totalEl.textContent = '$0'; }
      }


      // ALTERNATE DATA FINALIZE:
      /*     
      qs('[data-finalize]')?.addEventListener('click', async ()=>{
      const payload = getCart();
      // include cart-level personal info and dates
      const body = {
        personal: payload.personal || {},
        items: payload.items || [],
        startDate: (payload.personal && payload.personal.startDate) || '',
        endDate: (payload.personal && payload.personal.endDate) || ''
      };
      try{
        const resp = await fetch((window.BASE_URL || '') + '/api/create_reservation.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(body)
        });
        const data = await resp.json();
        if (!data.success){
          alert('Reservation failed: ' + (data.message || 'unknown'));
          return;
        }
        alert('Reservation created. ID: ' + (data.reservationId || 'n/a'));
        // clear cart on success
        setCart({ items: [], personal: null });
        closeModal();
        if (document.body.dataset.page === 'cart'){
          const cartDatesSection = byId('cartDates');
          if (cartDatesSection) cartDatesSection.style.display = 'none';
          const totalEl = byId('grandTotal');
          if (totalEl) totalEl.textContent = '$0';
          render();
        }
      }
      catch(err){
        console.error('Checkout error', err);
        alert('Network/server error during reservation. See console for details.');
      }
      */
    });
  }

  // function confirmGeneratorThenFinalize(next){
  //   openModal(`
  //     <h2 id="modalTitle">Do you need a generator?</h2>
  //     <p>Some venues require power where outlets are not available.</p>
  //     <div class="detail-cta">
  //       <button class="btn btn-secondary" data-gen="no">No, continue</button>
  //       <button class="btn btn-primary" data-gen="yes">Yes, add generator (TBD)</button>
  //     </div>
  //   `);
  //   qsa('[data-gen]').forEach(btn=>{
  //     btn.addEventListener('click', ()=>{
  //       const needGen = btn.getAttribute('data-gen') === 'yes';
  //       if (needGen){ alert('Generator option will be added at checkout (server-side).'); }
  //       closeModal();
  //       next();
  //     });
  //   });
  // }

  function beginCheckoutFlow(){
    promptPersonalInfo(()=>{
      // confirmGeneratorThenFinalize(()=>{
        showFinalConfirm();
      // });
    });
  }
})();

/* ===== Admin & Login logic (placeholder, no real auth) ===== */
// // Change: Copied this function from other website version and changed supremeheir in links to root
// (function(){
//   console.log('Login clicked');
//   if (document.body.dataset.page === 'login'){
//     console.log('If statment entered');
//     const form = document.getElementById('loginForm');
//     form?.addEventListener('submit', (e)=>{
//       e.preventDefault();
//       // Per requirement: clicking Log in with no credentials goes to admin.
//       const fd = new FormData(form);
//       const user = (fd.get('username')||'').toString().trim();
//       const pass = (fd.get('password')||'').toString().trim();
//       if (!user && !pass){
//         window.location.href = `${window.APP_CONFIG.BASE_URL || ''}/../index.php?page=admin`;
//         return;
//       }
//       // Optional: if provided, still proceed to admin for now
//       window.location.href = '${window.APP_CONFIG.BASE_URL}/../index.php?page=admin';
//     });
//   }

  if (document.body.dataset.page === 'admin'){
    // Demo-only: wire placeholder actions
    document.getElementById('adminSearchForm')?.addEventListener('submit', (e)=>{
      e.preventDefault();
      alert('Search submitted. (Hook up to PHP later.)');
    });
    document.getElementById('adminAddForm')?.addEventListener('submit', (e)=>{
      e.preventDefault();
      alert('Create submitted. (Hook up to PHP later.)');
    });
    document.getElementById('adminEditForm')?.addEventListener('submit', (e)=>{
      e.preventDefault();
      alert('Update submitted. (Hook up to PHP later.)');
    });
    document.querySelector('[data-admin-delete]')?.addEventListener('click', ()=>{
      if (confirm('Delete this reservation? (Demo only)')){
        alert('Deleted. (Hook up to PHP later.)');
      }
    });
  }
//})();