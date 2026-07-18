<main>
<section class="employe-s">

    <div class="user-header reveal">
        <div class="wrap">
            <p class="kicker">Espace employé</p>
            <h1 class="sec-h2">Gestion des <em>menus</em></h1>
        </div>
    </div>

    <div class="user-nav">
        <a href="/employe" class="user-nav-link">Commandes</a>
        <a href="/employe/menus" class="user-nav-link active">Menus</a>
        <a href="/employe/avis" class="user-nav-link">Avis</a>
        <form method="POST" action="/deconnexion" style="display:inline;"><?= $_csrf_field ?><button type="submit" class="user-nav-link user-nav-logout">Se déconnecter</button></form>
    </div>

    <div class="wrap">
        <div class="employe-table-wrap">
            <table class="employe-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Thème</th>
                        <th>Régime</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menus as $menu): ?>
                    <tr>
                        <td>#<?= $menu['menu_id'] ?></td>
                        <td><?= htmlspecialchars($menu['titre']) ?></td>
                        <td><?= htmlspecialchars($menu['theme']) ?></td>
                        <td><?= htmlspecialchars($menu['regime']) ?></td>
                        <td><?= number_format($menu['prix_base'], 2, ',', ' ') ?> €</td>
                        <td><?= $menu['stock'] ?></td>
                        <td>
                            <span class="commande-statut <?= $menu['actif'] ? 'commande-statut-livree' : 'commande-statut-annulee' ?>">
                                <?= $menu['actif'] ? 'Actif' : 'Inactif' ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="/employe/menus/toggle" style="display:inline;">
                                <?= $csrf ?>
                                <input type="hidden" name="menu_id" value="<?= $menu['menu_id'] ?>">
                                <button type="submit" class="commande-annuler-btn">
                                    <?= $menu['actif'] ? 'Désactiver' : 'Activer' ?>
                                </button>
                            </form>
                            <a href="/menus/<?= $menu['menu_id'] ?>" class="menu-link" style="margin-left:8px;">Voir →</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</section>
</main>
