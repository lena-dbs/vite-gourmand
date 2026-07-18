<main>
<section class="employe-s">

    <div class="user-header reveal">
        <div class="wrap">
            <a href="/admin/employes" class="menu-detail-back">← Retour aux employés</a>
            <p class="kicker">Espace administrateur</p>
            <h1 class="sec-h2">Créer un <em>employé</em></h1>
        </div>
    </div>

    <div class="user-nav">
        <a href="/admin" class="user-nav-link">Commandes</a>
        <a href="/admin/menus" class="user-nav-link">Menus</a>
        <a href="/admin/avis" class="user-nav-link">Avis</a>
        <a href="/admin/employes" class="user-nav-link active">Employés</a>
        <a href="/admin/stats" class="user-nav-link">Statistiques</a>
        <a href="/deconnexion" class="user-nav-link user-nav-logout">Se déconnecter</a>
    </div>

    <div class="wrap">
        <?php if ($error): ?>
            <div class="auth-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/employes/create" style="max-width:480px;">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       placeholder="employe@vitegourmand.fr"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe temporaire</label>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••••">
                <span class="form-hint">L'employé devra se rapprocher de vous pour obtenir ce mot de passe.</span>
            </div>
            <button type="submit" class="hbtn">
                <span>Créer le compte employé</span>
            </button>
        </form>
    </div>

</section>
</main>
