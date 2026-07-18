<main>
<section class="auth-s"style="background-image: url('https://images.unsplash.com/photo-1559339352-11d035aa65de?w=1800&q=85&auto=format&fit=crop'); background-size: cover; background-position: center;">
    <div class="auth-overlay"></div>
<div class="auth-card">
        
        <div class="auth-header">
            <p class="kicker">Espace membre</p>
            <h1 class="auth-titre">Connexion</h1>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="auth-success">
                Compte créé avec succès ! Connectez-vous.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="auth-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/connexion">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       placeholder="votre@email.fr"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••••">
            </div>
            <div class="form-footer">
                <a href="/mot-de-passe-oublie" class="auth-forgot">Mot de passe oublié ?</a>
            </div>
            <button type="submit" class="hbtn" style="width:100%;justify-content:center;">
                <span>Se connecter</span>
            </button>
        </form>

        <p class="auth-switch">
            Pas encore de compte ? 
            <a href="/inscription">Créer un compte</a>
        </p>

    </div>
</section>
</main>