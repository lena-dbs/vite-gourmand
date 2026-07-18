<main>
<section class="user-s">

    <!-- Header vert pleine largeur -->
    <div class="user-header reveal">
        <div class="wrap">
            <p class="kicker">Espace personnel</p>
            <h1 class="sec-h2">Bonjour, <em><?= htmlspecialchars($_SESSION['user']['prenom']) ?></em></h1>
        </div>
    </div>

    <!-- Nav verte pleine largeur -->
    <div class="user-nav">
        <a href="/mon-compte" class="user-nav-link active">Mes commandes</a>
        <a href="/mon-compte/profil" class="user-nav-link">Mon profil</a>
        <a href="/deconnexion" class="user-nav-link user-nav-logout">Se déconnecter</a>
    </div>

    <!-- Contenu -->
    <div class="wrap">
        <?php if (empty($commandes)): ?>
            <div class="user-empty">
                <p>Vous n'avez pas encore passé de commande.</p>
                <a href="/menus" class="hbtn"><span>Découvrir nos menus</span></a>
            </div>
        <?php else: ?>
            <div class="commandes-liste">
                <?php foreach ($commandes as $commande): ?>
                <div class="commande-card reveal">
                    <div class="commande-card-img">
                        <img src="<?= htmlspecialchars($commande['menu_image']) ?>" 
                             alt="<?= htmlspecialchars($commande['menu_titre']) ?>">
                    </div>
                    <div class="commande-card-body">
                        <div class="commande-card-head">
                            <h2 class="commande-card-titre"><?= htmlspecialchars($commande['menu_titre']) ?></h2>
                            <span class="commande-statut commande-statut-<?= $commande['statut_actuel'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $commande['statut_actuel'])) ?>
                            </span>
                        </div>
                        <div class="commande-card-infos">
                            <span>📅 <?= date('d/m/Y', strtotime($commande['date_livraison'])) ?></span>
                            <span>👥 <?= $commande['nb_personnes'] ?> personnes</span>
                            <span>💰 <?= number_format($commande['prix_total'], 2, ',', ' ') ?> €</span>
                        </div>
                        <a href="/mon-compte/commandes/<?= $commande['commande_id'] ?>" class="menu-link">
                            Voir le détail →
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</section>
</main>