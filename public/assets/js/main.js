

/* NAVBAR */
const nav = document.getElementById('nav');
addEventListener('scroll', () => nav.classList.toggle('solid', scrollY > 55), {passive:true});

/* BURGER */
const burger = document.getElementById('nav-burger');
const navLinks = document.getElementById('nav-links');
const navActions = document.querySelector('.nav-actions');
if (burger) {
  burger.addEventListener('click', function() {
    const open = this.classList.toggle('open');
    this.setAttribute('aria-expanded', open);
    navLinks.classList.toggle('open', open);
    if (navActions) navActions.classList.toggle('open', open);
  });
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.nav')) {
      burger.classList.remove('open');
      burger.setAttribute('aria-expanded', 'false');
      navLinks.classList.remove('open');
      if (navActions) navActions.classList.remove('open');
    }
  });
}

/* PARALLAX */
const hbg  = document.getElementById('hbg');
const ctabg= document.getElementById('ctabg');
if (hbg || ctabg) {
  addEventListener('scroll', () => {
    const y = scrollY;
    if (hbg && y < innerHeight * 1.3) hbg.style.transform = `translateY(${y*.27}px)`;
    if (ctabg) {
      const r = ctabg.parentElement.getBoundingClientRect();
      if (r.top < innerHeight && r.bottom > 0)
        ctabg.style.transform = `translateY(${-r.top*.18}px)`;
    }
  }, {passive:true});
}

/* REVEAL */
const io = new IntersectionObserver(es => {
  es.forEach(e => { if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); } });
}, {threshold:.1, rootMargin:'0px 0px -30px 0px'});
document.querySelectorAll('.reveal,.reveal-l,.reveal-r').forEach(el => io.observe(el));

/* CURSEUR */
if (matchMedia('(pointer: fine)').matches) {
  const dot  = document.createElement('div');
  const ring = document.createElement('div');
  dot.className  = 'cursor-dot hide';
  ring.className = 'cursor-ring hide';
  document.body.append(dot, ring);
  document.body.classList.add('has-cursor');

  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  let x = 0, y = 0, rx = 0, ry = 0, started = false;

  addEventListener('mousemove', e => {
    x = e.clientX; y = e.clientY;
    if (!started) { started = true; rx = x; ry = y; dot.classList.remove('hide'); ring.classList.remove('hide'); }
    dot.style.transform = `translate(${x}px,${y}px)`;
    const field = e.target.closest('input,textarea,select');
    dot.classList.toggle('hide', !!field);
    ring.classList.toggle('hide', !!field);
    ring.classList.toggle('grow', !field && !!e.target.closest('a,button,label,summary'));
  });

  (function suivre() {
    const k = reduced ? 1 : .16;
    rx += (x - rx) * k;
    ry += (y - ry) * k;
    ring.style.transform = `translate(${rx}px,${ry}px)`;
    requestAnimationFrame(suivre);
  })();

  addEventListener('mousedown', () => ring.classList.add('press'));
  addEventListener('mouseup',   () => ring.classList.remove('press'));
  document.documentElement.addEventListener('mouseleave', () => { dot.classList.add('hide'); ring.classList.add('hide'); });
  document.documentElement.addEventListener('mouseenter', () => { if (started) { dot.classList.remove('hide'); ring.classList.remove('hide'); } });
}

/* TILT */
const ti = document.getElementById('tilt');
if(ti){
  ti.addEventListener('mousemove', function(e){
    const r=this.getBoundingClientRect();
    const xp=(e.clientX-r.left)/r.width-.5;
    const yp=(e.clientY-r.top)/r.height-.5;
    this.style.transition='transform .08s ease';
    this.style.transform=`perspective(900px) rotateY(${xp*9}deg) rotateX(${-yp*9}deg) scale(1.025)`;
  });
  ti.addEventListener('mouseleave', function(){
    this.style.transition='transform .6s ease';
    this.style.transform='perspective(900px) rotateY(0) rotateX(0) scale(1)';
  });
}
