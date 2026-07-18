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
                    <label for="ville">Ville de livraison</label>
                    <input type="text" id="ville" name="ville" required
                           placeholder="Bordeaux"
                           value="<?= htmlspecialchars($_SESSION['user']['ville'] ?? '') ?>">
                    <span class="form-hint">Livraison gratuite à Bordeaux, 5€ + 0,59€/km au-delà.</span>
                </div>
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
        var ville = villeInput.value.trim().toLowerCase();
        var livraison = ville === 'bordeaux' ? 0 : 5;
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

    menuSelect.addEventListener('change', updateRecap);
    villeInput.addEventListener('input', updateRecap);
    nbInput.addEventListener('input', updateRecap);
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