<main>
<section class="commande-s">

    <div class="user-header reveal">
        <div class="wrap">
            <p class="kicker">Réservation</p>
            <h1 class="sec-h2">Passer <em>commande</em></h1>
        </div>
    </div>

    <div class="wrap">

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="auth-error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <form method="POST" action="/commande" class="commande-form" id="commande-form">
            <?= $csrf ?>

            <!-- Menu choisi -->
            <div class="commande-section">
                <h2 class="commande-section-titre">Le menu</h2>
                <div class="form-group">
                    <label for="menu_id">Menu sélectionné</label>
                    <select name="menu_id" id="menu_id" required>
                        <?php foreach ($menus as $m): ?>
                            <option value="<?= $m['menu_id'] ?>" 
                                <?= ($menu && $menu['menu_id'] == $m['menu_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['titre']) ?> — <?= number_format($m['prix_base'], 0, ',', ' ') ?> €
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Informations livraison -->
            <div class="commande-section">
                <h2 class="commande-section-titre">Informations de livraison</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_livraison">Date de livraison</label>
                        <input type="date" id="date_livraison" name="date_livraison" required
                               min="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="heure_livraison">Heure de livraison</label>
                        <input type="time" id="heure_livraison" name="heure_livraison" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="adresse">Adresse de livraison</label>
                    <input type="text" id="adresse" name="adresse" required
                           placeholder="12 rue des Remparts"
                           value="<?= htmlspecialchars($_SESSION['user']['adresse'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="code_postal">Code postal</label>
                        <input type="text" id="code_postal" name="code_postal" required
                               inputmode="numeric" pattern="[0-9]{5}" placeholder="33000"
                               value="<?= htmlspecialchars($_SESSION['user']['code_postal'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="ville">Ville de livraison</label>
                        <input type="text" id="ville" name="ville" required
                               placeholder="Bordeaux"
                               value="<?= htmlspecialchars($_SESSION['user']['ville'] ?? '') ?>">
                    </div>
                </div>
                <span class="form-hint">Livraison gratuite à Bordeaux. Ailleurs, les frais (5&nbsp;€ + 0,59&nbsp;€/km) sont calculés automatiquement à partir de votre adresse.</span>
            </div>

            <!-- Nombre de personnes -->
            <div class="commande-section">
                <h2 class="commande-section-titre">Nombre de personnes</h2>
                <div class="form-group">
                    <label for="nb_personnes">Nombre de personnes</label>
                    <input type="number" id="nb_personnes" name="nb_personnes" required
                           min="<?= $menu ? $menu['nb_personnes_min'] : 1 ?>"
                           value="<?= $menu ? $menu['nb_personnes_min'] : 1 ?>">
                    <?php if ($menu): ?>
                        <span class="form-hint">Minimum <?= $menu['nb_personnes_min'] ?> personnes. Réduction de 10% à partir de <?= $menu['nb_personnes_min'] + 5 ?> personnes.</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Récapitulatif prix -->
            <div class="commande-recap">
                <h2 class="commande-section-titre">Récapitulatif</h2>
                <div class="recap-ligne">
                    <span>Prix du menu</span>
                    <span id="recap-menu"><?= $menu ? number_format($menu['prix_base'], 2, ',', ' ') . ' €' : '—' ?></span>
                </div>
                <div class="recap-ligne">
                    <span>Livraison</span>
                    <span id="recap-livraison">—</span>
                </div>
                <div class="recap-ligne recap-reduction" id="recap-reduction-ligne" style="display:none;">
                    <span>Réduction 10%</span>
                    <span id="recap-reduction">—</span>
                </div>
                <div class="recap-ligne recap-total">
                    <span>Total</span>
                    <span id="recap-total">—</span>
                </div>
                <div class="recap-ligne" style="margin-top:12px;padding-top:12px;border-top:1px solid #e5ddd3;">
                    <span>Paiement</span>
                    <span>À la livraison</span>
                </div>
            </div>

            <p class="form-hint" style="margin-bottom:16px;">Le paiement s'effectue à la livraison, en espèces ou par carte bancaire.</p>

            <button type="submit" class="hbtn">
                <span>Confirmer la commande</span>
            </button>

        </form>
    </div>
</section>

<script nonce="<?= CSP_NONCE ?>">
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

    var menus = <?= json_encode(array_map(fn($m) => [
        'id' => $m['menu_id'],
        'prix' => (float)$m['prix_base'],
        'min' => (int)$m['nb_personnes_min']
    ], $menus)) ?>;

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
</script>
</main>