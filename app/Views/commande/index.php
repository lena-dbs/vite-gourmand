<main>
<section class="commande-s">

    <div class="user-header reveal">
        <div class="wrap">
            <p class="kicker">Réservation</p>
            <h1 class="sec-h2">Passer <em>commande</em></h1>
        </div>
    </div>

    <div class="wrap">

        <form method="POST" action="/commande" class="commande-form">

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
            </div>

            <button type="submit" class="hbtn">
                <span>Confirmer la commande</span>
            </button>

        </form>
    </div>
</section>
</main>