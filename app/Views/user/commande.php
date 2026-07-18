<main>
<section class="user-s">

    <div class="user-header reveal">
        <div class="wrap">
            <a href="/mon-compte" class="menu-detail-back">← Retour à mes commandes</a>
            <p class="kicker">Détail commande #<?= $commande['commande_id'] ?></p>
            <h1 class="sec-h2"><?= htmlspecialchars($commande['menu_titre']) ?></h1>
        </div>
    </div>

    <div class="wrap">
        <div class="commande-detail-grid">

            <!-- Infos commande -->
            <div class="commande-detail-infos reveal">
                <h2 class="menu-detail-section-title">Informations</h2>
                <table class="commande-table">
                    <tr>
                        <td>Date de livraison</td>
                        <td><?= date('d/m/Y', strtotime($commande['date_livraison'])) ?></td>
                    </tr>
                    <tr>
                        <td>Heure de livraison</td>
                        <td><?= $commande['heure_livraison'] ?></td>
                    </tr>
                    <tr>
                        <td>Nombre de personnes</td>
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

                <!-- Annulation -->
             <?php $dernierStatut = !empty($suivi) ? $suivi[count($suivi)-1]['statut'] : 'en_attente';
if ($dernierStatut === 'en_attente'): ?>
                <form method="POST" action="/mon-compte/annuler" style="margin-top: 24px;">
                    <input type="hidden" name="commande_id" value="<?= $commande['commande_id'] ?>">
                    <div class="form-group">
                        <label for="motif">Motif d'annulation</label>
                        <input type="text" id="motif" name="motif" required
                               placeholder="Raison de l'annulation...">
                    </div>
                    <button type="submit" class="commande-annuler-btn">Annuler la commande</button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Suivi commande -->
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

