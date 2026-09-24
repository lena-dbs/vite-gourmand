(function() {
    var form = document.getElementById('commande-form');
    var menuSelect = document.getElementById('menu_id');
    var adresseInput = document.getElementById('adresse');
    var cpInput = document.getElementById('code_postal');
    var villeInput = document.getElementById('ville');
    var nbInput = document.getElementById('nb_personnes');
    var recapMenu = document.getElementById('recap-menu');
    var recapLivraison = document.getElementById('recap-livraison');
    var recapReduction = document.getElementById('recap-reduction');
    var recapReductionLigne = document.getElementById('recap-reduction-ligne');
    var recapTotal = document.getElementById('recap-total');

    var menus = window.VG_MENUS || [];

    var BORDEAUX = { lat: 44.837789, lon: -0.579180 };
    var livraison = 0;         // frais de livraison courants (calculés depuis l'adresse)
    var geocodeTimer = null;

    function getMenu() {
        var id = parseInt(menuSelect.value);
        for (var i = 0; i < menus.length; i++) {
            if (menus[i].id === id) return menus[i];
        }
        return null;
    }

    function fmt(n) { return n.toFixed(2).replace('.', ',') + ' €'; }

    function updateRecap() {
        var m = getMenu();
        if (!m) return;
        var prix = m.prix;
        var nb = parseInt(nbInput.value) || m.min;
        var reduction = nb >= m.min + 5 ? prix * 0.10 : 0;
        var total = prix + livraison - reduction;

        recapMenu.textContent = fmt(prix);
        recapLivraison.textContent = livraison === 0 ? 'Gratuite' : fmt(livraison);
        if (reduction > 0) {
            recapReductionLigne.style.display = 'flex';
            recapReduction.textContent = '- ' + fmt(reduction);
        } else {
            recapReductionLigne.style.display = 'none';
        }
        recapTotal.textContent = fmt(total);
        nbInput.min = m.min;
    }

    // Distance routière approximative (km) entre Bordeaux et des coordonnées.
    function distanceKm(lat, lon) {
        var toRad = function (d) { return d * Math.PI / 180; };
        var dLat = toRad(lat - BORDEAUX.lat), dLon = toRad(lon - BORDEAUX.lon);
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2)
              + Math.cos(toRad(BORDEAUX.lat)) * Math.cos(toRad(lat)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
        return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)) * 1.3;
    }

    // Recalcule les frais de livraison automatiquement à partir de l'adresse saisie.
    function majLivraison() {
        var ville = villeInput.value.trim().toLowerCase();
        if (ville === 'bordeaux') { livraison = 0; updateRecap(); return; }

        var q = (adresseInput.value + ' ' + cpInput.value + ' ' + villeInput.value).trim();
        if (q.length < 6) { livraison = 5; updateRecap(); return; }

        recapLivraison.textContent = 'Calcul…';

        clearTimeout(geocodeTimer);
        geocodeTimer = setTimeout(function () {
            var fini = false;
            var done = function (val) { if (fini) return; fini = true; livraison = val; updateRecap(); };
            // Garde-fou : si l'API ne répond pas sous 6 s, on applique le forfait de base.
            var secours = setTimeout(function () { done(5); }, 6000);
            fetch('https://api-adresse.data.gouv.fr/search/?limit=1&q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    clearTimeout(secours);
                    var c = d && d.features && d.features[0] && d.features[0].geometry.coordinates;
                    if (c) {
                        var km = distanceKm(c[1], c[0]);
                        done(Math.round((5 + 0.59 * km) * 100) / 100);
                    } else {
                        done(5); // adresse introuvable : forfait de base
                    }
                })
                .catch(function () { clearTimeout(secours); done(5); });
        }, 500);
    }

    menuSelect.addEventListener('change', updateRecap);
    nbInput.addEventListener('input', updateRecap);
    adresseInput.addEventListener('input', majLivraison);
    cpInput.addEventListener('input', majLivraison);
    villeInput.addEventListener('input', majLivraison);
    majLivraison();
    updateRecap();

    form.addEventListener('submit', function(e) {
        var menuText = menuSelect.options[menuSelect.selectedIndex].text;
        var date = form.querySelector('#date_livraison').value;
        var heure = form.querySelector('#heure_livraison').value;
        var nb = nbInput.value;
        var ville = villeInput.value;
        var total = recapTotal.textContent.trim();

        var msg = 'Confirmez-vous cette commande ?\n\n'
            + 'Menu : ' + menuText + '\n'
            + 'Date : ' + date + ' à ' + heure + '\n'
            + 'Personnes : ' + nb + '\n'
            + 'Ville : ' + ville + '\n'
            + 'Total : ' + total + '\n'
            + 'Paiement : à la livraison';

        if (!confirm(msg)) {
            e.preventDefault();
        }
    });
})();
