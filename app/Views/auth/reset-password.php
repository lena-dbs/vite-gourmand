<main>
<section class="auth-s" style="background-image: url('/assets/images/auth-bg.jpg'); background-size: cover; background-position: center;">
    <div class="auth-overlay"></div>
    <div class="auth-card">

        <div class="auth-header">
            <p class="kicker">Sécurité</p>
            <h1 class="auth-titre">Nouveau mot de passe</h1>
        </div>

        <?php if ($error): ?>
            <div class="auth-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($token): ?>
            <form method="POST" action="/reinitialiser-mot-de-passe?token=<?= htmlspecialchars($token) ?>">
                <?= $csrf ?>
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" required
                           placeholder="••••••••••">
                    <span class="form-hint">10 caractères min., une majuscule, une minuscule, un chiffre et un caractère spécial.</span>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirm" name="password_confirm" required
                           placeholder="••••••••••">
                </div>
                <button type="submit" class="hbtn" style="width:100%;justify-content:center;">
                    <span>Réinitialiser le mot de passe</span>
                </button>
            </form>
        <?php else: ?>
            <p class="auth-switch">
                <a href="/mot-de-passe-oublie">Demander un nouveau lien</a>
            </p>
        <?php endif; ?>

        <p class="auth-switch">
            <a href="/connexion">← Retour à la connexion</a>
        </p>

    </div>
</section>
</main>

