<main>
<section class="employe-s">

    <div class="user-header reveal">
        <div class="wrap">
            <p class="kicker">Espace administrateur</p>
            <h1 class="sec-h2">Validation des <em>avis</em></h1>
        </div>
    </div>

    <div class="user-nav">
        <a href="/admin" class="user-nav-link">Commandes</a>
        <a href="/admin/menus" class="user-nav-link">Menus</a>
        <a href="/admin/avis" class="user-nav-link active">Avis</a>
        <a href="/admin/employes" class="user-nav-link">Employés</a>
        <a href="/admin/stats" class="user-nav-link">Statistiques</a>
        <form method="POST" action="/deconnexion" style="display:inline;"><?= $_csrf_field ?><button type="submit" class="user-nav-link user-nav-logout">Se déconnecter</button></form>
    </div>

    <div class="wrap">
        <?php if (empty($avis)): ?>
            <div class="user-empty">
                <p>Aucun avis en attente de validation.</p>
            </div>
        <?php else: ?>
            <div class="avis-grid reveal">
                <?php foreach ($avis as $a): ?>
                <div class="avis-card">
                    <div class="avis-c-stars">
                        <?= str_repeat('★', $a['note']) ?><?= str_repeat('☆', 5 - $a['note']) ?>
                    </div>
                    <blockquote class="avis-c-q">"<?= htmlspecialchars($a['commentaire']) ?>"</blockquote>
                    <p class="avis-c-auth">
                        <strong><?= htmlspecialchars($a['prenom']) ?> <?= htmlspecialchars($a['nom']) ?></strong>
                        · <?= htmlspecialchars($a['menu_titre']) ?>
                    </p>
                    <div style="display:flex;gap:8px;margin-top:16px;">
                        <form method="POST" action="/admin/avis/update">
                            <?= $csrf ?>
                            <input type="hidden" name="avis_id" value="<?= $a['avis_id'] ?>">
                            <input type="hidden" name="statut" value="valide">
                            <button type="submit" class="hbtn" style="padding:8px 16px;">
                                <span>✓ Valider</span>
                            </button>
                        </form>
                        <form method="POST" action="/admin/avis/update">
                            <?= $csrf ?>
                            <input type="hidden" name="avis_id" value="<?= $a['avis_id'] ?>">
                            <input type="hidden" name="statut" value="refuse">
                            <button type="submit" class="commande-annuler-btn">✗ Refuser</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</section>
</main>
