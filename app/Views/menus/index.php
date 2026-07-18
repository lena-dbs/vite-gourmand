<main>

<section class="menus-page-s">

    <div class="user-header reveal">
        <div class="wrap">
            <p class="kicker">Notre carte</p>
            <h1 class="sec-h2">Tous nos <em>menus</em></h1>
        </div>
    </div>

    <div class="wrap">

        <!-- Filtres -->
        <div class="filtres reveal">
            <div class="filtre-group">
                <label>Thème</label>
                <select id="filtre-theme">
                    <option value="">Tous</option>
                    <option value="Noël">Noël</option>
                    <option value="Pâques">Pâques</option>
                    <option value="Classique">Classique</option>
                    <option value="Evénement">Evénement</option>
                    <option value="Anniversaire">Anniversaire</option>
                    <option value="Mariage">Mariage</option>
                    <option value="Apero Dinatoire">Apéro Dinatoire</option>
                </select>
            </div>
            <div class="filtre-group">
                <label>Régime</label>
                <select id="filtre-regime">
                    <option value="">Tous</option>
                    <option value="vegetarien">Végétarien</option>
                    <option value="vegan">Vegan</option>
                    <option value="sans gluten">Sans gluten</option>
                    <option value="halal">Halal</option>
                    <option value="classique">Classique</option>
                </select>
            </div>
            <div class="filtre-group">
                <label>Prix maximum</label>
                <input type="number" id="filtre-prix" placeholder="ex: 500">
            </div>
            <div class="filtre-group">
                <label>Personnes minimum</label>
                <input type="number" id="filtre-personnes" placeholder="ex: 10">
            </div>
        </div>

        <!-- Liste des menus -->
        <div class="menus-liste" id="menus-liste">
            <?php foreach ($menus as $menu): ?>
            <article class="menu-row" 
                data-theme="<?= htmlspecialchars($menu['theme']) ?>"
                data-regime="<?= htmlspecialchars($menu['regime']) ?>"
                data-prix="<?= $menu['prix_base'] ?>"
                data-personnes="<?= $menu['nb_personnes_min'] ?>">
                <div class="menu-row-body">
                    <div>
                        <div class="menu-row-head">
                            <div class="menu-tags">
                                <span class="mtag mtag-<?= strtr(mb_strtolower($menu['theme']), ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'ô' => 'o', 'ç' => 'c', ' ' => '-']) ?>">
                                    <?= htmlspecialchars($menu['theme']) ?>
                                </span>
                                <span class="mtag mtag-reg">
                                    <?= htmlspecialchars($menu['regime']) ?>
                                </span>
                            </div>
                            <div class="menu-price">
                                <?= number_format($menu['prix_base'], 0, ',', ' ') ?> €
                                <sub>/ <?= $menu['nb_personnes_min'] ?> pers.</sub>
                            </div>
                        </div>
                        <h2 class="menu-name"><?= htmlspecialchars($menu['titre']) ?></h2>
                        <p class="menu-desc"><?= htmlspecialchars($menu['description']) ?></p>
                    </div>
                    <div class="menu-row-foot">
                        <?php if ($menu['stock'] <= 3): ?>
                            <span class="menu-stock-alert">⚠ Plus que <?= $menu['stock'] ?> disponible(s)</span>
                        <?php endif; ?>
                        <a href="/menus/<?= $menu['menu_id'] ?>" class="menu-link">
                            Voir le détail →
                        </a>
                    </div>
                </div>
                <div class="menu-row-img">
                    <img src="<?= htmlspecialchars($menu['image']) ?>" 
                         alt="<?= htmlspecialchars($menu['titre']) ?>"
                         loading="lazy">
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- Message si aucun résultat -->
        <p class="no-results" id="no-results" style="display:none;">
            Aucun menu ne correspond à vos critères.
        </p>

    </div>
</section>

<script nonce="<?= CSP_NONCE ?>">
// Filtres dynamiques sans rechargement
const filtreTheme     = document.getElementById('filtre-theme');
const filtreRegime    = document.getElementById('filtre-regime');
const filtrePrix      = document.getElementById('filtre-prix');
const filtrePersonnes = document.getElementById('filtre-personnes');
const menus           = document.querySelectorAll('.menu-row');
const noResults       = document.getElementById('no-results');

function filtrer() {
    const theme     = filtreTheme.value.toLowerCase();
    const regime    = filtreRegime.value.toLowerCase();
    const prix      = filtrePrix.value ? parseFloat(filtrePrix.value) : null;
    const personnes = filtrePersonnes.value ? parseInt(filtrePersonnes.value) : null;

    let visible = 0;

    menus.forEach(menu => {
        const mTheme     = menu.dataset.theme.toLowerCase();
        const mRegime    = menu.dataset.regime.toLowerCase();
        const mPrix      = parseFloat(menu.dataset.prix);
        const mPersonnes = parseInt(menu.dataset.personnes);

        const ok =
            (!theme     || mTheme === theme) &&
            (!regime    || mRegime === regime) &&
            (!prix      || mPrix <= prix) &&
            (!personnes || mPersonnes <= personnes);

        menu.style.display = ok ? 'grid' : 'none';
        if (ok) visible++;
    });

    noResults.style.display = visible === 0 ? 'block' : 'none';
}

filtreTheme.addEventListener('change', filtrer);
filtreRegime.addEventListener('change', filtrer);
filtrePrix.addEventListener('input', filtrer);
filtrePersonnes.addEventListener('input', filtrer);
</script>

</main>