<main>
<section class="employe-s">

    <div class="user-header reveal">
        <div class="wrap">
            <p class="kicker">Espace administrateur</p>
            <h1 class="sec-h2">Gestion des <em>menus</em></h1>
        </div>
    </div>

    <div class="user-nav">
        <a href="/admin" class="user-nav-link">Commandes</a>
        <a href="/admin/menus" class="user-nav-link active">Menus</a>
        <a href="/admin/avis" class="user-nav-link">Avis</a>
        <a href="/admin/employes" class="user-nav-link">Employés</a>
        <a href="/admin/stats" class="user-nav-link">Statistiques</a>
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
                        <td>
                            <form method="POST" action="/admin/menus/stock" class="stock-form">
                                <?= $csrf ?>
                                <input type="hidden" name="menu_id" value="<?= $menu['menu_id'] ?>">
                                <input type="number" name="stock" value="<?= (int)$menu['stock'] ?>" min="0" max="999" class="stock-input">
                                <button type="submit" class="commande-annuler-btn">OK</button>
                            </form>
                        </td>
                        <td>
                            <span class="commande-statut <?= $menu['actif'] ? 'commande-statut-livree' : 'commande-statut-annulee' ?>">
                                <?= $menu['actif'] ? 'Actif' : 'Inactif' ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="/admin/menus/toggle" style="display:inline;">
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