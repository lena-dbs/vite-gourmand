<main>
<section class="employe-s">
    <div class="wrap">

        <a href="/admin" class="menu-detail-back">← Retour aux commandes</a>

        <div class="user-header reveal">
            <p class="kicker">Commande #<?= $commande['commande_id'] ?></p>
            <h1 class="sec-h2"><?= htmlspecialchars($commande['menu_titre']) ?></h1>
        </div>

        <div class="user-nav">
            <a href="/admin" class="user-nav-link active">Commandes</a>
            <a href="/admin/menus" class="user-nav-link">Menus</a>
            <a href="/admin/avis" class="user-nav-link">Avis</a>
            <a href="/admin/employes" class="user-nav-link">Employés</a>
            <a href="/admin/stats" class="user-nav-link">Statistiques</a>
            <a href="/deconnexion" class="user-nav-link user-nav-logout">Se déconnecter</a>
        </div>

        <div class="commande-detail-grid">

            <div class="commande-detail-infos reveal">
                <h2 class="menu-detail-section-title">Informations client</h2>
                <table class="commande-table">
                    <tr>
                        <td>Client</td>
                        <td><?= htmlspecialchars($commande['prenom']) ?> <?= htmlspecialchars($commande['nom']) ?></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td><?= htmlspecialchars($commande['email']) ?></td>
                    </tr>
                    <tr>
                        <td>Téléphone</td>
                        <td><?= htmlspecialchars($commande['telephone']) ?></td>
                    </tr>
                    <tr>
                        <td>Date livraison</td>
                        <td><?= date('d/m/Y', strtotime($commande['date_livraison'])) ?></td>
                    </tr>
                    <tr>
                        <td>Heure livraison</td>
                        <td><?= $commande['heure_livraison'] ?></td>
                    </tr>
                    <tr>
                        <td>Nb personnes</td>
                        <td><?= $commande['nb_personnes'] ?></td>
                    </tr>
                    <tr>
                        <td>Prix menu</td>
                        <td><?= number_format($commande['prix_menu'], 2, ',', ' ') ?> €</td>
                    </tr>
                    <tr>
                        <td>Livraison</td>
                        <td><?= number_format($commande['prix_livraison'], 2, ',', ' ') ?> €</td>
                    </tr>
                    <?php if ($commande['prix_reduction'] > 0): ?>
                    <tr>
                        <td>Réduction</td>
                        <td>- <?= number_format($commande['prix_reduction'], 2, ',', ' ') ?> €</td>
                    </tr>
                    <?php endif; ?>
                    <tr class="commande-table-total">
                        <td>Total</td>
                        <td><?= number_format($commande['prix_total'], 2, ',', ' ') ?> €</td>
                    </tr>
                </table>

                <h2 class="menu-detail-section-title" style="margin-top:32px;">Mettre à jour le statut</h2>
                <form method="POST" action="/admin/statut">
                    <input type="hidden" name="commande_id" value="<?= $commande['commande_id'] ?>">
                    <div class="form-group">
                        <label>Nouveau statut</label>
                        <select name="statut">
                            <option value="en_attente">En attente</option>
                            <option value="en_preparation">En préparation</option>
                            <option value="prete">Prête</option>
                            <option value="livree">Livrée</option>
                            <option value="retour_materiel">Retour matériel</option>
                            <option value="terminee">Terminée</option>
                            <option value="annulee">Annulée</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Commentaire</label>
                        <input type="text" name="commentaire" placeholder="Optionnel...">
                    </div>
                    <button type="submit" class="hbtn"><span>Mettre à jour</span></button>
                </form>
            </div>

            <div class="commande-detail-suivi reveal">
                <h2 class="menu-detail-section-title">Suivi de commande</h2>
                <div class="suivi-liste">
                    <?php foreach ($suivi as $etape): ?>
                    <div class="suivi-etape">
                        <div class="suivi-dot"></div>
                        <div class="suivi-content">
                            <p class="suivi-statut"><?= ucfirst(str_replace('_', ' ', $etape['statut'])) ?></p>
                            <?php if ($etape['commentaire']): ?>
                                <p class="suivi-commentaire"><?= htmlspecialchars($etape['commentaire']) ?></p>
                            <?php endif; ?>
                            <p class="suivi-date"><?= date('d/m/Y à H:i', strtotime($etape['created_at'])) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

    </div>
</section>
</main>

