<main>
<section class="employe-s">

    <div class="user-header reveal">
        <div class="wrap">
            <p class="kicker">Espace administrateur</p>
            <h1 class="sec-h2">Gestion des <em>employés</em></h1>
        </div>
    </div>

    <div class="user-nav">
        <a href="/admin" class="user-nav-link">Commandes</a>
        <a href="/admin/menus" class="user-nav-link">Menus</a>
        <a href="/admin/avis" class="user-nav-link">Avis</a>
        <a href="/admin/employes" class="user-nav-link active">Employés</a>
        <a href="/admin/stats" class="user-nav-link">Statistiques</a>
        <form method="POST" action="/deconnexion" style="display:inline;"><?= $_csrf_field ?><button type="submit" class="user-nav-link user-nav-logout">Se déconnecter</button></form>
    </div>

    <div class="wrap">
        <div style="margin-bottom: 24px;">
            <a href="/admin/employes/create" class="hbtn"><span>+ Créer un employé</span></a>
        </div>

        <div class="employe-table-wrap">
            <table class="employe-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Email</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Créé le</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($employes)): ?>
                        <tr><td colspan="7" style="text-align:center;padding:24px;color:#776855;">Aucun employé</td></tr>
                    <?php else: ?>
                        <?php foreach ($employes as $employe): ?>
                        <tr>
                            <td>#<?= $employe['utilisateur_id'] ?></td>
                            <td><?= htmlspecialchars($employe['email']) ?></td>
                            <td><?= htmlspecialchars($employe['nom']) ?></td>
                            <td><?= htmlspecialchars($employe['prenom']) ?></td>
                            <td><?= date('d/m/Y', strtotime($employe['created_at'])) ?></td>
                            <td>
                                <span class="commande-statut <?= $employe['actif'] ? 'commande-statut-livree' : 'commande-statut-annulee' ?>">
                                    <?= $employe['actif'] ? 'Actif' : 'Inactif' ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="/admin/employes/toggle" style="display:inline;">
                                    <?= $csrf ?>
                                    <input type="hidden" name="employe_id" value="<?= $employe['utilisateur_id'] ?>">
                                    <button type="submit" class="commande-annuler-btn">
                                        <?= $employe['actif'] ? 'Désactiver' : 'Activer' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</section>
</main>
