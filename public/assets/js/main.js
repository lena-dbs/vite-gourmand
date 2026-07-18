

/* NAVBAR */
const nav = document.getElementById('nav');
addEventListener('scroll', () => nav.classList.toggle('solid', scrollY > 55), {passive:true});

/* PARALLAX */
const hbg  = document.getElementById('hbg');
const ctabg= document.getElementById('ctabg');
addEventListener('scroll', () => {
  const y = scrollY;
  if (y < innerHeight * 1.3) hbg.style.transform = `translateY(${y*.27}px)`;
  if (ctabg) {
    const r = ctabg.parentElement.getBoundingClientRect();
    if (r.top < innerHeight && r.bottom > 0)
      ctabg.style.transform = `translateY(${-r.top*.18}px)`;
  }
}, {passive:true});

/* REVEAL */
const io = new IntersectionObserver(es => {
  es.forEach(e => { if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); } });
}, {threshold:.1, rootMargin:'0px 0px -30px 0px'});
document.querySelectorAll('.reveal,.reveal-l,.reveal-r').forEach(el => io.observe(el));

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
