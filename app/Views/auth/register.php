<main>
<section class="auth-s" style="background-image: url('https://images.unsplash.com/photo-1559339352-11d035aa65de?w=1800&q=85&auto=format&fit=crop'); background-size: cover; background-position: center;">
    <div class="auth-overlay"></div>
    <div class="auth-card">

        <div class="auth-header">
            <p class="kicker">Espace membre</p>
            <h1 class="auth-titre">Créer un compte</h1>
        </div>

        <?php if ($error): ?>
            <div class="auth-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/inscription">
            <div class="form-row">
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required
                           placeholder="Julie"
                           value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required
                           placeholder="Santos"
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       placeholder="votre@email.fr"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" required
                       placeholder="0600000000"
                       value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="adresse" required
                       placeholder="1 rue de la Paix"
                       value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="ville">Ville</label>
                    <input type="text" id="ville" name="ville" required
                           placeholder="Bordeaux"
                           value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="code_postal">Code postal</label>
                    <input type="text" id="code_postal" name="code_postal" required
                           placeholder="33000"
                           value="<?= htmlspecialchars($_POST['code_postal'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••••">
                <span class="form-hint">10 caractères min., une majuscule, une minuscule, un chiffre et un caractère spécial.</span>
            </div>
            <button type="submit" class="hbtn" style="width:100%;justify-content:center;">
                <span>Créer mon compte</span>
            </button>
        </form>

        <p class="auth-switch">
            Déjà un compte ? 
            <a href="/connexion">Se connecter</a>
        </p>

    </div>
</section>
</main>