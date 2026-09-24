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
