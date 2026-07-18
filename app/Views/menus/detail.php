<main>

<section class="menu-detail-s">

    <div class="user-header menu-hero reveal">
        <div class="wrap">
            <a href="/menus" class="menu-detail-back">← Retour aux menus</a>
            <div class="menu-tags">
                <span class="mtag mtag-<?= strtr(mb_strtolower($menu['theme']), ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'ô' => 'o', 'ç' => 'c', ' ' => '-']) ?>">
                    <?= htmlspecialchars($menu['theme']) ?>
                </span>
                <span class="mtag mtag-reg">
                    <?= htmlspecialchars($menu['regime']) ?>
                </span>
            </div>
            <h1 class="sec-h2"><?= htmlspecialchars($menu['titre']) ?></h1>
            <p class="menu-hero-desc"><?= htmlspecialchars($menu['description']) ?></p>
            <div class="menu-hero-prix">
                <?= number_format($menu['prix_base'], 0, ',', ' ') ?> €
                <span>/ <?= $menu['nb_personnes_min'] ?> personnes minimum</span>
            </div>
        </div>
    </div>

    <div class="wrap">

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="auth-error" style="margin:32px 0 0;"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <div class="menu-detail-grid">

          <!-- Colonne gauche : plats -->
<div class="menu-detail-plats reveal">
    <h2 class="menu-detail-section-title">Au menu</h2>

    <?php foreach ($plats as $plat): ?>
    <div class="plat-card">
        <?php if ($plat['photo']): ?>
        <div class="plat-card-img">
            <img src="<?= htmlspecialchars($plat['photo']) ?>" 
                 alt="<?= htmlspecialchars($plat['nom']) ?>"
                 loading="lazy">
        </div>
        <?php endif; ?>
        <div class="plat-card-body">
            <div class="menu-detail-plat-type"><?= htmlspecialchars($plat['type']) ?></div>
            <h3 class="menu-detail-plat-nom"><?= htmlspecialchars($plat['nom']) ?></h3>
            <?php if ($plat['description']): ?>
                <p class="menu-detail-plat-desc"><?= htmlspecialchars($plat['description']) ?></p>
            <?php endif; ?>
            <?php if (!empty($plat['allergenes'])): ?>
                <div class="menu-detail-allergenes">
                    <span class="allergene-label">Allergènes :</span>
                    <?php foreach ($plat['allergenes'] as $allergene): ?>
                        <span class="allergene-tag"><?= htmlspecialchars($allergene['libelle']) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
            <!-- Colonne droite : conditions + commande -->
            <div class="menu-detail-aside reveal">

                <!-- Conditions -->
                <?php if (!empty($conditions)): ?>
                <div class="menu-detail-conditions">
                    <h2 class="menu-detail-section-title">Conditions</h2>
                    <ul class="conditions-liste">
                        <?php foreach ($conditions as $condition): ?>
                            <li><?= htmlspecialchars($condition['description']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Stock + bouton commander -->
                <?php if ($menu['stock'] <= 0): ?>
                    <p class="menu-stock-alert">Ce menu est épuisé pour le moment.</p>
                    <span class="hbtn hbtn-off"><span>Épuisé</span></span>
                <?php else: ?>
                    <?php if ($menu['stock'] <= 3): ?>
                        <p class="menu-stock-alert">⚠ Plus que <?= $menu['stock'] ?> disponible(s)</p>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="/commande?menu_id=<?= $menu['menu_id'] ?>" class="hbtn">
                            <span>Commander ce menu</span>
                        </a>
                    <?php else: ?>
                        <a href="/connexion?redirect=<?= urlencode('/commande?menu_id=' . $menu['menu_id']) ?>" class="hbtn">
                            <span>Se connecter pour commander</span>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>

    </div>
</section>

</main>