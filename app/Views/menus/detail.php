<main>

<section class="menu-detail-s">
    <div class="wrap">

        <!-- Retour -->
        <a href="/menus" class="menu-detail-back">← Retour aux menus</a>

        <!-- Header du menu -->
        <div class="menu-detail-header reveal">
            <div class="menu-tags">
                <span class="mtag mtag-<?= strtolower(str_replace(' ', '-', $menu['theme'])) ?>">
                    <?= htmlspecialchars($menu['theme']) ?>
                </span>
                <span class="mtag mtag-reg">
                    <?= htmlspecialchars($menu['regime']) ?>
                </span>
            </div>
            <h1 class="menu-detail-titre"><?= htmlspecialchars($menu['titre']) ?></h1>
            <p class="menu-detail-desc"><?= htmlspecialchars($menu['description']) ?></p>
            <div class="menu-detail-prix">
                <?= number_format($menu['prix_base'], 0, ',', ' ') ?> €
                <span>/ <?= $menu['nb_personnes_min'] ?> personnes minimum</span>
            </div>
        </div>

        <div class="menu-detail-grid">

            <!-- Colonne gauche : plats -->
            <div class="menu-detail-plats reveal">
                <h2 class="menu-detail-section-title">Au menu</h2>

                <?php foreach ($plats as $plat): ?>
                <div class="menu-detail-plat">
                    <div class="menu-detail-plat-type"><?= htmlspecialchars($plat['type']) ?></div>
                    <div class="menu-detail-plat-info">
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

                <!-- Stock -->
                <?php if ($menu['stock'] <= 3): ?>
                    <p class="menu-stock-alert">⚠ Plus que <?= $menu['stock'] ?> disponible(s)</p>
                <?php endif; ?>

                <!-- Bouton commander -->
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="/commande?menu_id=<?= $menu['menu_id'] ?>" class="hbtn">
                        <span>Commander ce menu</span>
                    </a>
                <?php else: ?>
                    <a href="/connexion?redirect=/menus/<?= $menu['menu_id'] ?>" class="hbtn">
                        <span>Se connecter pour commander</span>
                    </a>
                <?php endif; ?>

            </div>
        </div>

    </div>
</section>

</main>