<main>
<section class="auth-s" style="background-image: url('https://images.unsplash.com/photo-1559339352-11d035aa65de?w=1800&q=85&auto=format&fit=crop'); background-size: cover; background-position: center;">
    <div class="auth-overlay"></div>
    <div class="auth-card">

        <div class="auth-header">
            <p class="kicker">Sécurité</p>
            <h1 class="auth-titre">Mot de passe oublié</h1>
        </div>

        <?php if ($success): ?>
            <div class="auth-success">
                Si un compte existe avec cet email, vous recevrez un lien de réinitialisation dans quelques minutes.
            </div>
        <?php else: ?>

            <?php if ($error): ?>
                <div class="auth-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="/mot-de-passe-oublie">
                <?= $csrf ?>
                <div class="form-group">
                    <label for="email">Votre email</label>
                    <input type="email" id="email" name="email" required
                           placeholder="votre@email.fr">
                </div>
                <button type="submit" class="hbtn" style="width:100%;justify-content:center;">
                    <span>Envoyer le lien</span>
                </button>
            </form>

        <?php endif; ?>

        <p class="auth-switch">
            <a href="/connexion">← Retour à la connexion</a>
        </p>

    </div>
</section>
</main>
