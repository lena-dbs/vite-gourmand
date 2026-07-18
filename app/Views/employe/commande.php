<main>
<section class="employe-s">

    <div class="user-header reveal">
        <div class="wrap">
            <p class="kicker">Commande #<?= $commande['commande_id'] ?></p>
            <h1 class="sec-h2"><?= htmlspecialchars($commande['menu_titre']) ?></h1>
        </div>
    </div>

    <div class="user-nav">
        <a href="/employe" class="user-nav-link active">Commandes</a>
        <a href="/employe/menus" class="user-nav-link">Menus</a>
        <a href="/employe/avis" class="user-nav-link">Avis</a>
        <form method="POST" action="/deconnexion" style="display:inline;"><?= $_csrf_field ?><button type="submit" class="user-nav-link user-nav-logout">Se déconnecter</button></form>
    </div>

    <div class="wrap">
        <a href="/employe" class="menu-detail-back">← Retour aux commandes</a>

        <div class="commande-detail-grid">

            <div class="commande-detail-infos reveal">

                <div class="detail-card">
                    <div class="detail-card-header">
                        <h2 class="detail-card-title">Informations client</h2>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-row">
                            <span class="detail-label">Client</span>
                            <span class="detail-value"><?= htmlspecialchars($commande['prenom']) ?> <?= htmlspecialchars($commande['nom']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email</span>
                            <span class="detail-value"><?= htmlspecialchars($commande['email']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Téléphone</span>
                            <span class="detail-value"><?= htmlspecialchars($commande['telephone']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-header">
                        <h2 class="detail-card-title">Livraison</h2>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-row">
                            <span class="detail-label">Date</span>
                            <span class="detail-value"><?= date('d/m/Y', strtotime($commande['date_livraison'])) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Heure</span>
                            <span class="detail-value"><?= $commande['heure_livraison'] ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Personnes</span>
                            <span class="detail-value"><?= $commande['nb_personnes'] ?></span>
                        </div>
                    </div>
                </div>

                <div class="detail-card detail-card-prix">
                    <div class="detail-card-header">
                        <h2 class="detail-card-title">Tarification</h2>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-row">
                            <span class="detail-label">Prix menu</span>
                            <span class="detail-value"><?= number_format($commande['prix_menu'], 2, ',', ' ') ?> €</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Livraison</span>
                            <span class="detail-value"><?= number_format($commande['prix_livraison'], 2, ',', ' ') ?> €</span>
                        </div>
                        <?php if ($commande['prix_reduction'] > 0): ?>
                        <div class="detail-row">
                            <span class="detail-label">Réduction</span>
                            <span class="detail-value" style="color:#3B6D11;">- <?= number_format($commande['prix_reduction'], 2, ',', ' ') ?> €</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="detail-card-total">
                        <span>Total</span>
                        <span><?= number_format($commande['prix_total'], 2, ',', ' ') ?> €</span>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-header detail-card-header-action">
                        <h2 class="detail-card-title">Mettre à jour le statut</h2>
                    </div>
                    <div class="detail-card-body">
                        <form method="POST" action="/employe/statut">
                            <?= $csrf ?>
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
                </div>

            </div>

            <div class="commande-detail-suivi reveal">
                <div class="detail-card">
                    <div class="detail-card-header detail-card-header-dark">
                        <h2 class="detail-card-title">Suivi de commande</h2>
                    </div>
                    <div class="detail-card-body">
                        <div class="suivi-liste">
                            <?php foreach ($suivi as $etape): ?>
                            <div class="suivi-etape">
                                <div class="suivi-dot"></div>
                                <div class="suivi-content">
                                    <p class="suivi-statut"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $etape['statut']))) ?></p>
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

        </div>
    </div>

</section>
</main>
