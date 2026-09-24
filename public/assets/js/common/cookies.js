/* COOKIES + CONSENTEMENT CARTE */
(function () {
  var KEY = 'vg_cookie_consent'; // 'accepted' | 'refused'
  var banner = document.getElementById('cookie-banner');
  var frame = document.getElementById('map-frame');
  var placeholder = document.getElementById('map-placeholder');

  function loadMap() {
    if (frame && !frame.getAttribute('src') && frame.dataset.src) {
      frame.src = frame.dataset.src;
      frame.hidden = false;
      if (placeholder) placeholder.hidden = true;
    }
  }
  function unloadMap() {
    if (frame && frame.getAttribute('src')) {
      frame.removeAttribute('src');
      frame.hidden = true;
      if (placeholder) placeholder.hidden = false;
    }
  }
  function showBanner() { if (banner) banner.hidden = false; }
  function hideBanner() { if (banner) banner.hidden = true; }
  function accept() { localStorage.setItem(KEY, 'accepted'); hideBanner(); loadMap(); }
  function refuse() { localStorage.setItem(KEY, 'refused'); hideBanner(); unloadMap(); }

  var choice = localStorage.getItem(KEY);
  if (choice === 'accepted') loadMap();

  // Affichage initial : uniquement tant qu'aucun choix n'a été fait.
  if (banner && !choice) showBanner();

  // Handlers toujours attachés (bannière initiale ET re-gestion via le pied de page).
  var a = document.getElementById('cookie-accept');
  var r = document.getElementById('cookie-refuse');
  if (a) a.addEventListener('click', accept);
  if (r) r.addEventListener('click', refuse);

  // Bouton du cadre : charger la carte vaut acceptation.
  var mapLoad = document.getElementById('map-load');
  if (mapLoad) mapLoad.addEventListener('click', accept);

  // Lien « Gérer mes cookies » (pied de page) : rouvre la bannière pour changer d'avis.
  var manage = document.getElementById('cookie-manage');
  if (manage) manage.addEventListener('click', function (e) { e.preventDefault(); showBanner(); });
})();
