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
