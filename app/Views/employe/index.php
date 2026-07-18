<main>
<section class="employe-s">

    <div class="user-header reveal">
        <div class="wrap">
            <p class="kicker">Espace employé</p>
            <h1 class="sec-h2">Gestion des <em>commandes</em></h1>
        </div>
    </div>

    <div class="user-nav">
        <a href="/employe" class="user-nav-link active">Commandes</a>
        <a href="/employe/menus" class="user-nav-link">Menus</a>
        <a href="/employe/avis" class="user-nav-link">Avis</a>
        <form method="POST" action="/deconnexion" style="display:inline;"><?= $_csrf_field ?><button type="submit" class="user-nav-link user-nav-logout">Se déconnecter</button></form>
    </div>

    <div class="wrap">
        <!-- Filtres -->
        <form method="GET" action="/employe" class="employe-filtres">
            <div class="filtre-group">
                <label>Statut</label>
                <select name="statut">
                    <option value="">Tous</option>
                    <option value="en_attente" <?= $statut === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="en_preparation" <?= $statut === 'en_preparation' ? 'selected' : '' ?>>En préparation</option>
                    <option value="prete" <?= $statut === 'prete' ? 'selected' : '' ?>>Prête</option>
                    <option value="livree" <?= $statut === 'livree' ? 'selected' : '' ?>>Livrée</option>
                    <option value="annulee" <?= $statut === 'annulee' ? 'selected' : '' ?>>Annulée</option>
                    <option value="retour_materiel" <?= $statut === 'retour_materiel' ? 'selected' : '' ?>>Retour matériel</option>
                    <option value="terminee" <?= $statut === 'terminee' ? 'selected' : '' ?>>Terminée</option>
                </select>
            </div>
            <div class="filtre-group">
                <label>Recherche client</label>
                <input type="text" name="search" placeholder="Nom, prénom, email..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="hbtn"><span>Filtrer</span></button>
        </form>

        <!-- Liste commandes -->
        <div class="employe-table-wrap">
            <table class="employe-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Menu</th>
                        <th>Date livraison</th>
                        <th>Personnes</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($commandes)): ?>
                        <tr><td colspan="8" style="text-align:center;padding:24px;color:#776855;">Aucune commande trouvée</td></tr>
                    <?php else: ?>
                        <?php foreach ($commandes as $commande): ?>
                        <tr>
                            <td>#<?= $commande['commande_id'] ?></td>
                            <td>
                                <?= htmlspecialchars($commande['prenom']) ?> <?= htmlspecialchars($commande['nom']) ?><br>
                                <small><?= htmlspecialchars($commande['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($commande['menu_titre']) ?></td>
                            <td><?= date('d/m/Y', strtotime($commande['date_livraison'])) ?></td>
                            <td><?= $commande['nb_personnes'] ?></td>
                            <td><?= number_format($commande['prix_total'], 2, ',', ' ') ?> €</td>
                            <td>
                                <span class="commande-statut commande-statut-<?= htmlspecialchars($commande['statut_actuel']) ?>">
                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $commande['statut_actuel']))) ?>
                                </span>
                            </td>
                            <td>
                                <a href="/employe/commande?id=<?= $commande['commande_id'] ?>" class="menu-link">Voir →</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination['totalPages'] > 1): ?>
        <div class="pagination">
            <?php
            $params = [];
            if ($statut) $params['statut'] = $statut;
            if ($search) $params['search'] = $search;
            $baseUrl = '/employe';
            ?>
            <?php if ($pagination['page'] > 1): ?>
                <a href="<?= $baseUrl . '?' . http_build_query(array_merge($params, ['page' => $pagination['page'] - 1])) ?>" class="pagination-link">&laquo; Précédent</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                <a href="<?= $baseUrl . '?' . http_build_query(array_merge($params, ['page' => $i])) ?>"
                   class="pagination-link <?= $i === $pagination['page'] ? 'pagination-active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                <a href="<?= $baseUrl . '?' . http_build_query(array_merge($params, ['page' => $pagination['page'] + 1])) ?>" class="pagination-link">Suivant &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

</section>
</main>
