<main>
<section class="user-s">

    <div class="user-header reveal">
        <div class="wrap">
            <p class="kicker">Espace personnel</p>
            <h1 class="sec-h2">Mon <em>profil</em></h1>
        </div>
    </div>

    <div class="user-nav">
        <a href="/mon-compte" class="user-nav-link">Mes commandes</a>
        <a href="/mon-compte/profil" class="user-nav-link active">Mon profil</a>
        <form method="POST" action="/deconnexion" style="display:inline;"><?= $_csrf_field ?><button type="submit" class="user-nav-link user-nav-logout">Se déconnecter</button></form>
    </div>

    <div class="wrap">
        <?php if ($success): ?>
            <div class="auth-success">Profil mis à jour avec succès !</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="auth-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/mon-compte/profil" class="profil-form">
            <?= $csrf ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required
                           value="<?= htmlspecialchars($user['prenom']) ?>">
                </div>
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required
                           value="<?= htmlspecialchars($user['nom']) ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                <span class="form-hint">L'email ne peut pas être modifié.</span>
            </div>
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" required
                       value="<?= htmlspecialchars($user['telephone']) ?>">
            </div>
            <div class="form-group">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="adresse" required
                       value="<?= htmlspecialchars($user['adresse']) ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="ville">Ville</label>
                    <input type="text" id="ville" name="ville" required
                           value="<?= htmlspecialchars($user['ville']) ?>">
                </div>
                <div class="form-group">
                    <label for="code_postal">Code postal</label>
                    <input type="text" id="code_postal" name="code_postal" required
                           value="<?= htmlspecialchars($user['code_postal']) ?>">
                </div>
            </div>
            <button type="submit" class="hbtn">
                <span>Sauvegarder</span>
            </button>
        </form>

    </div>
</section>
</main>